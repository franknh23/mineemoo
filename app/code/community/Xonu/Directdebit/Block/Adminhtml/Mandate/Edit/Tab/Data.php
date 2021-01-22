<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */

class Xonu_Directdebit_Block_Adminhtml_Mandate_Edit_Tab_Data extends Mage_Adminhtml_Block_Widget_Form {

    protected $_helper;

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        /* @var $mandate Xonu_Directdebit_Model_Mandate */
        $mandate = Mage::registry('xonu_directdebit_mandate');
        $data = $mandate->getData();

        $order = Mage::getModel('sales/order')->load($data['last_order_id']);





        // expiration date and remaining time
        $remainingSeconds = $mandate->getExpirationDate('U') - time();
        $interval = $this->_helper()->convertSecondsToInterval($remainingSeconds);
        $intervalFormat = $this->_helper()->__('%y Year(s), %m Month(s), %d Day(s)');
        $data['remaining_time'] = $interval->format($intervalFormat);







        // check validity
        $errors = array();

        $data['document_html_checksum'] = $mandate->getDocumentHtmlChecksum() . ' - ' .
            ($mandate->checkDocumentHtml() ? $this->_helper()->__('OK') : $this->_helper()->__('ERROR'));

        $data['document_data_checksum'] = $mandate->getDocumentDataChecksum() . ' - ' .
            ($mandate->checkDocumentData() ? $this->_helper()->__('OK') : $this->_helper()->__('ERROR'));


        if($data['revoked'])    $errors['revoked'] = true;
        if($remainingSeconds <= 0) $errors['expired'] = true;
        if(!$data['recurrent']) $errors['recurrent'] = true;
        if(!($mandate->checkDocumentHtml() && $mandate->checkDocumentData())) $errors['checksum'] = true;


        if($errors) {
            $valid = false; $data['valid'] = $this->_helper()->__('No');
        } else {
            $valid = true;  $data['valid'] = $this->_helper()->__('Yes');
        }






        $fieldset = $form->addFieldset('general', array('legend' => $this->_helper()->__('General')));

        // $fieldset->addField('mandate_identifier', 'label', array('label' => $this->_helper()->__('Mandate Identifier')));

        $fieldset->addField('local_created_at', 'label', array('label' => $this->_helper()->__('Date of Granting')));
        $data['local_created_at'] = $this->formatDateTime($data['created_at']);

        $fieldset->addField('type', 'label', array('label' => $this->_helper()->__('Type of Payment')));
        $data['type'] = $data['recurrent'] ?
            $this->_helper()->__('Recurrent Payment') : $this->_helper()->__('One-Off Payment');

        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addType('store', 'Xonu_Directdebit_Block_Adminhtml_Mandate_Edit_Form_Element_Store');
            $fieldset->addField('store_id', 'store', array('label' => $this->_helper()->__('Created In')));
        }

        $fieldset = $form->addFieldset('customer', array('legend' => $this->_helper()->__('Account Information')));

        $data['customer_name'] = $data['customer_firstname'].' '.$data['customer_lastname'];
        if(Mage::getSingleton('admin/session')->isAllowed('customer/manage')) {
            $fieldset->addType('customer_edit', 'Xonu_Directdebit_Block_Adminhtml_Mandate_Edit_Form_Element_Customer');
            $fieldset->addField('customer_name', 'customer_edit', array(
                'label' => $this->_helper()->__('Customer Name'),
                'customer_id' => $data['customer_id']
            ));
        } else {
            $fieldset->addField('customer_name', 'label', array('label' => $this->_helper()->__('Customer Name')));
        }

        $fieldset->addField('customer_email', 'label', array('label' => $this->_helper()->__('Email')));

        $fieldset = $form->addFieldset('validity', array('legend' => $this->_helper()->__('Validity')));

        $fieldset->addField('local_last_update', 'label', array('label' => $this->_helper()->__('Last Update')));
        $data['local_last_update'] = $this->formatDateTime($data['updated_at']);

        $data['last_order_id_increment'] = $order->getIncrementId();
        if(Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $fieldset->addType('order_view', 'Xonu_Directdebit_Block_Adminhtml_Mandate_Edit_Form_Element_Order');
            $fieldset->addField('last_order_id_increment', 'order_view', array(
                'label' => $this->_helper()->__('Last Order Id'),
                'order_id' => $data['last_order_id']
            ));
        } else {
            $fieldset->addField('last_order_id_increment', 'label', array('label' => $this->_helper()->__('Last Order Id')));
        }

        $fieldset->addField('local_order_created_at', 'label', array('label' => $this->_helper()->__('Last Order Date')));
        $data['local_order_created_at'] = $this->formatDateTime($data['last_order_created_at']);

        if($data['recurrent']) {
            $fieldset->addField('expiration_date', 'label', array('label' => $this->_helper()->__('Expiration Date')));
            $data['expiration_date'] = $this->formatDateTime($mandate->getExpirationDate());

            if($valid) $fieldset->addField('remaining_time', 'label', array('label' => $this->_helper()->__('Remaining Time')));

            $fieldset->addField('valid', 'label', array('label' => $this->_helper()->__('Valid')));
        }


        if(Mage::helper('xonu_directdebit/sepaone')->isAvailable()) {
            $data = Mage::helper('xonu_sepaone/mandate')->getRemoteData($form, $data);
        }

        $documentData = $mandate->getDocumentData();

        $fieldset = $form->addFieldset('creditor_data', array('legend' => $this->_helper()->__('Creditor')));

        foreach($documentData['creditor'] as $code => $value) {
            $label = ucwords(str_replace('_', ' ', $code));
            $fieldset->addField("creditor_$code", 'label', array('label' => $this->_helper()->__($label)));
            $data["creditor_$code"] = $value;
        }

        $fieldset = $form->addFieldset('debitor_data', array('legend' => $this->_helper()->__('Debitor')));

        foreach($documentData['debitor'] as $code => $value) {
            if($code == 'iban' || $code == 'bic') $label = strtoupper($code);
            else                                  $label = ucwords(str_replace('_', ' ', $code));
            $fieldset->addField("debitor_$code", 'label', array('label' => $this->_helper()->__($label)));
            $data["debitor_$code"] = $value;
        }



        $fieldset = $form->addFieldset('integrity_check', array('legend' => $this->_helper()->__('Integrity Check')));

        $fieldset->addField('document_data_checksum', 'label', array('label' => $this->_helper()->__('Data Checksum')));

        $fieldset->addField('document_html_checksum', 'label', array('label' => $this->_helper()->__('Document Checksum')));

        $form->setValues($data);
        return parent::_prepareForm();
    }

    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_directdebit');
        return $this->_helper;
    }

    protected function formatDateTime($timestamp) {
        return $this->_helper()->formatDateTime($timestamp);
    }

}