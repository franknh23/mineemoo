<?php

class Xonu_Sepaone_Block_Info extends Xonu_Directdebit_Block_Info {

    protected function _prepareSpecificInformation($transport = null) {
        $label = 'SEPAone EBICS Export';

        // do not show SEPAone info in frontend
        if(Mage::app()->getStore()->getStoreId()) return parent::_prepareSpecificInformation($transport);

        if(!Mage::getStoreConfigFlag('xonu_directdebit/sepaone/active')) {
            return parent::_prepareSpecificInformation($transport);
        }

        $transport = parent::_prepareSpecificInformation($transport);

        $coreHelper = Mage::helper('core');
        $helper = Mage::helper('xonu_sepaone');

        $orderId = $this->getRequest()->getParam('order_id');

        if(!isset($orderId)) {
            $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
            if(isset($creditmemoId)) {
                $creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoId);
                $orderId = $creditmemo->getOrderId();
            } else {
                return $transport;
            }
        }

        /* @var $export Xonu_Sepaone_Model_Resource_Export_Collection */
        $export = Mage::getResourceModel('xonu_sepaone/export')->getCollection()
            ->addFieldToFilter('order_id', array('eq' => $orderId))
            ->getFirstItem();
        $transactionId = $export->getLastTransactionId();

        if($transactionId != '') {
            if(Mage::getStoreConfigFlag('xonu_directdebit/sepaone/transaction_status_active')) {

                // get real-time transaction data
                $api = Mage::getModel('xonu_sepaone/api');
                $transaction = $api->transactionGetById($transactionId);

                $transport->addData(array(
                    $helper->__($label) => '', // create space
                    $helper->__('Export Date') =>
                        $coreHelper->formatDate($transaction['assoc']['created_at'], 'medium', false)
                . ' ' . $coreHelper->formatTime($transaction['assoc']['created_at'], 'medium', false),
                    $helper->__('Transaction Identifier') =>
                        $transactionId,
                    $helper->__('Transaction Status') =>
                        $transaction['assoc']['status'],
                    $helper->__('Request Date') =>
                        $coreHelper->formatDate($transaction['assoc']['requested_date'], 'medium', false),
                    $helper->__('Last Update') =>
                        $coreHelper->formatDate($transaction['assoc']['updated_at'], 'medium', false)
                . ' ' . $coreHelper->formatTime($transaction['assoc']['updated_at'], 'medium', false)
                ));

            } else {
                $transport->addData(array(
                    $helper->__($label) => '', // create space
                    $helper->__('Export Date') =>
                        $coreHelper->formatDate($export->getExportedAt(), 'medium', false)
                        . ' ' . $coreHelper->formatTime($export->getExportedAt(), 'medium', false),
                    $helper->__('Transaction Identifier') =>
                        $transactionId,
                    $helper->__('Transaction Status') =>
                        $helper->__('(real-time transaction status disabled)')
                ));
            }
        } else {
            $transport->addData(array(
                $helper->__($label) => '(no transaction found)'
            ));
        }

        return $transport;
    }

}