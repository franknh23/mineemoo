<?php
/**
 * @package Xonu_Sepaone
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Sepaone_Model_Observer
{
    protected $_helper;

    /**
     * update existing mandate or use the newly created mandate,
     * create and send invoice automatically
     *
     * @param Varien_Event_Observer $observer
     */
    public function checkoutSubmitAllAfter(Varien_Event_Observer $observer)
    {
        $order   = $observer->getOrder();
        $code    = Mage::getModel('xonu_directdebit/payment_method_config')->getCode();
        $payment = $order->getPayment();
        $method  = $payment->getMethodInstance();
        $storeId = $order->getStoreId();

        // basic check
        if ($method->getCode() != $code) return;

        // save order id in the export table
        try{
            $export = Mage::getModel('xonu_sepaone/export');
            $export->setOrderId($order->getId());
            $export->save();
        } catch (Exception $e){
            Mage::log($e->getMessage());
        }
    }

    public function salesOrderCreditmemoSaveAfter($observer)
    {
        if(!Mage::getStoreConfigFlag('xonu_directdebit/sepaone/refund_export')) return;

        /* @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
        $creditMemo = $observer->getEvent()->getCreditmemo();
        $orderId = $creditMemo->getOrderId();
        // $creditMemo = Mage::getModel('sales/order_creditmemo')->load($creditMemo->getId());
        // $orderId = $creditMemo->getOrderId();

        $order = Mage::getModel('sales/order')->load($orderId);
        $code    = Mage::getModel('xonu_directdebit/payment_method_config')->getCode();
        $payment = $order->getPayment();
        $method  = $payment->getMethodInstance();

        // basic check
        if ($method->getCode() != $code) return;

        try{
            /* @var $export Xonu_Sepaone_Model_Resource_Export_Collection */
            $export = Mage::getResourceModel('xonu_sepaone/export')->getCollection()
                ->addFieldToFilter('order_id', array('eq' => $orderId))
                ->getFirstItem();
            $transactionId = $export->getLastTransactionId();

            $api = Mage::getModel('xonu_sepaone/api');

            $grandTotal = $creditMemo->getGrandTotal();
            $mandateReference = $payment->getData('sepa_mandate_id');

            $logData = array(
                'order_id' => $orderId,
                'order_increment_id' => $order->getData('increment_id'),
                'mandate_id' => $mandateReference
            );

            $transactionData = array(
                'transaction_id' => $transactionId,
                'remittance_information' => $creditMemo->getData('increment_id'),
                'amount_in_cents' => round($grandTotal * 100),
                'log' => $logData // will not be transferred to SEPAone
            );

            $api->refundCreate($transactionId, $transactionData);

        } catch (Exception $e){
            Mage::log($e->getMessage());
        }
    }


    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_sepaone');
        return $this->_helper;
    }

    public static function exportCron() {
        if(!Mage::getStoreConfigFlag('xonu_directdebit/sepaone/active')) return true; // SEPAone disabled

        $export = Mage::getModel('xonu_sepaone/export');

        $export->export();

        return true;
    }

    public function adminhtmlBlockSystemConfigInitTabSectionsBefore(Varien_Event_Observer $observer) {
        $section = $observer->getSection();

        $code = Mage::getModel('xonu_directdebit/payment_method_config')->getCode();
        if($section->getAttribute('module') == $code) {

            $configData = Mage::getSingleton('adminhtml/config_data');
            $website = $configData->getWebsite(); $store = $configData->getStore();
            if($store == '' && $website != '') $scope = 'websites/' . $website;
            elseif($store != '') $scope = 'stores/' . $store;
            else $scope = 'default';

            $config = Mage::getConfig();

            $webhookUrl = Mage::helper('xonu_sepaone')->getWebhookUrl();
            $config->setNode($scope . '/xonu_directdebit/sepaone/version', $v = (string)
            $config->getNode('modules/Xonu_Sepaone/version'));
            $config->setNode($scope . '/xonu_directdebit/sepaone/webhook_url', $webhookUrl);
            $config->setNode('/xonu_directdebit/sepaone/webhook_url/comment', $webhookUrl);
            $config->setNode($scope . '/xonu_directdebit/sepaone/name', 'Xonu_Sepaone (SEPAone Online Banking)');
        }
    }

    public function xonuDirectdebitMandateRevokeAfter(Varien_Event_Observer $observer) {
//        $mandate = $observer->getMandate();
//        $mandateReference = $observer->getMandate()->getMandateIdentifier();
//
//        $api = Mage::getModel('xonu_sepaone/api');
//        $remoteDataMandate = $api->mandateGetByReference($mandateReference);
//
//        if(sizeof($remoteDataMandate['assoc'])) {
//            $remoteId = $remoteDataMandate['assoc'][0]['id'];
//            $api->mandateUpdate($remoteId, array('status' => 'obsolete'));
//        }
    }
}
