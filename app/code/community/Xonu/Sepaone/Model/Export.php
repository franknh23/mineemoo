<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Sepaone_Model_Export extends Mage_Core_Model_Abstract {

    protected $_helper;

    protected function _construct() {
        $this->_init('xonu_sepaone/export');
    }

    // mixed microtime ([ bool $get_as_float = false ] ), get_as_float introduced in PHP5
    public static function mtime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    public function export() {
        $tStart = self::mtime();

        $history = Mage::getModel('xonu_sepaone/history');
        $history->setStartedAt($this->_helper()->getCurrentDateTime());

        $validOrderStatus = explode(',', Mage::getStoreConfig('xonu_directdebit/sepaone/valid_status'));
        $orderLimitPerRequest = Mage::getStoreConfig('xonu_directdebit/sepaone/order_limit');


        /* @var $collection Mage_Sales_Model_Resource_Order_Grid_Collection */
        $collection = Mage::getResourceModel('sales/order_collection')
            ->join(array('p' => 'sales/order_payment'), 'parent_id=main_table.entity_id',
                array('sepa_mandate_id' => 'sepa_mandate_id',
                    'sepa_holder' => 'sepa_holder',
                    'sepa_iban' => 'sepa_iban',
                    'sepa_bic' => 'sepa_bic',
                    'base_amount_ordered' => 'base_amount_ordered'))
            ->join(array('e' => 'xonu_sepaone/export'), 'order_id=main_table.entity_id')
            ->join(array('m' => 'xonu_directdebit/mandate'), 'mandate_identifier=sepa_mandate_id',
                array('sepa_mandate_date' => 'created_at',
                    'recurrent' => 'recurrent'))
            // ->addFieldToFilter('p.method', array('eq' => $code)) // redundant because of the export table
            ->addFieldToFilter('e.exported', array('eq' => 0))
            ->addFieldToFilter('e.errors', array('eq' => 0))
            ->addFieldToFilter('main_table.status', array('in' => $validOrderStatus))
        ;

        $exportedIds = array(); $errorIds = array();

        // limit per request for busy shops
        if($orderLimitPerRequest > 0) {
            $collection->setPageSize($orderLimitPerRequest)->setCurPage(1);
            // $exportedIds = array();
            // foreach($collection as $row) $exportedIds[] = $row->getData('order_id');
        } else {
            // $exportedIds = $collection->getAllIds();
        }

        // empty result
        if(!$collection->count()) {
            $history->setEmpty(1);
            $history->save();
            return false;
        } else {
            $history->setCount($collection->count());
            $history->setEmpty(0);
        }

        if($customReference = Mage::getStoreConfigFlag('xonu_directdebit/sepaone/custom_reference_active')) {
            $customReferenceTemplate = trim(Mage::getStoreConfig('xonu_directdebit/sepaone/custom_reference_template'));
            if($customReferenceTemplate == '') $customReference = false;
        }

        $api = Mage::getModel('xonu_sepaone/api');
        foreach($collection as $row) {
            $orderId = $row->getData('order_id');

            $mandateReference = $row->getData('sepa_mandate_id');

            if($customReference) {
                $reasonForPayment = sprintf($customReferenceTemplate, $row->getData('increment_id'));
            } else {
                $reasonForPayment = $row->getData('increment_id');
            }

            // check remote mandate by reference
            $remoteMandateActive = false;
            $remoteDataMandate = $api->mandateGetByReference($mandateReference);

            if(sizeof($remoteDataMandate['assoc'])) {
                if($remoteDataMandate['assoc'][0]['status'] == 'active') $remoteMandateActive = true;
            }

            $logData = array(
                'order_id' => $row->getData('order_id'),
                'order_increment_id' => $row->getData('increment_id'),
                'mandate_id' => $mandateReference
            );

            // SEPAone data structure for mandate
            $mandateData = array(
                'reference' => $mandateReference,
                'signature_date' => substr($row->getData('sepa_mandate_date'), 0, 10),
                'ip' => $row->getData('remote_ip'),
                'recurring' => $row->getData('recurrent'),
                'bank_account' => array(
                    'iban' => $row->getData('sepa_iban'),
                    'bic' => $row->getData('sepa_bic'),
                    'name' => $row->getData('sepa_holder')
                )
            );

            // SEPAone data structure for single payment
            $transactionData = array(
                'mandate' => array(
                    'reference' => $mandateReference
                ),
                'amount_in_cents' => round($row->getData('base_amount_ordered') * 100),
                'remittance_information' => $reasonForPayment,
                'log' => $logData // will not be transferred to SEPAone
            );


            // SEPAone API calls
            if($remoteMandateActive) {
                // SEPAone API call: transfer transaction only
                $response = $api->transactionCreate($transactionData);
            } else {
                // SEPAone API call: transfer transaction with mandate
                $response = $api->transactionMandateCreate($mandateData, $transactionData);
            }

            if(isset($response['assoc']['id'])) {
                $transactionId = $response['assoc']['id'];

                $export = Mage::getResourceModel('xonu_sepaone/export')->getCollection()
                    ->addFieldToFilter('order_id', array('eq' => $orderId))
                    ->getFirstItem();

                $timestamp = $this->_helper()->getCurrentDateTime();
                $export->setLastTransactionId($transactionId)
                    ->setExported(true)
                    ->setErrors(false)
                    ->setExportedAt($timestamp)
                    ->save();

                // change order state
                $order = Mage::getModel('sales/order')->load($orderId);
                if($order->getState() == Mage_Sales_Model_Order::STATE_NEW) {
                   $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)
                         ->setStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)
                         ->save();
                }

                $exportedIds[] = $orderId;
            } else if(isset($response['assoc']['errors'])) {
                $errorIds[] = $orderId;
            }
        }


        $tFinish = self::mtime();
        $history->setProcessingTime($tFinish - $tStart);
        $history->setEndedAt($this->_helper()->getCurrentDateTime());
        $history->setCountTransactions(sizeof($exportedIds));
        $history->setCountErrors(sizeof($errorIds));
        $history->save();

        // set exported flags
        /*
        if(sizeof($exportedIds)) {
            $flags = Mage::getResourceModel('xonu_sepaone/export_collection')
                ->addFieldToFilter('order_id', array('in' => $exportedIds))
                ->load()
            ;
            $timestamp = $this->_helper()->getCurrentDateTime();
            foreach($flags as $flag) {
                $flag->setExported(true);
                $flag->setErrors(false);
                $flag->setExportedAt($timestamp);
            }
            $flags->save();
        }
        */

        // set error flags
        if(sizeof($errorIds)) {
            $flags = Mage::getResourceModel('xonu_sepaone/export_collection')
                ->addFieldToFilter('order_id', array('in' => $errorIds))
                ->load()
            ;
            $timestamp = $this->_helper()->getCurrentDateTime();
            foreach($flags as $flag) {
                $flag->setErrors(true);
                $flag->setExportedAt($timestamp);
            }
            $flags->save();
        }

    }

    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_sepaone');
        return $this->_helper;
    }
}