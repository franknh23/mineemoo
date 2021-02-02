<?php

class Xonu_Sepaone_Helper_Mandate extends Mage_Core_Helper_Abstract {

    protected $_helper;

    public function getRemoteData($form, $data) {

        if(Mage::getStoreConfigFlag('xonu_directdebit/sepaone/mandate_status_active')) {
            $fieldset = $form->addFieldset('sepaone', array('legend' => $this->_helper()->__('SEPAone EBICS Export')));

            $api = Mage::getModel('xonu_sepaone/api');
            $mandateReference = $data['mandate_identifier'];
            $remoteDataMandate = $api->mandateGetByReference($mandateReference);

            if(sizeof($remoteDataMandate['assoc'])) {

                foreach($remoteDataMandate['assoc'][0] as $field => $value) {

                    if($field == 'bank_account') {
                        $flatData = '';
                        foreach($value as $n => $v) $flatData .= "$n: $v; ";
                        $value = $flatData;
                    } elseif($field == 'links') {
                        $field = 'link_count';
                        $value = sizeof($value);
                    }

                    $fieldCode = "sepaone_$field";
                    $fieldName = ucwords(str_replace('_', ' ', $field));

                    $data[$fieldCode] = $value;
                    $fieldset->addField($fieldCode, 'label', array('label' => $this->_helper()->__($fieldName)));
                }

            } else {
                $fieldCode = 'sepaone_note';
                $fieldName = 'Info';
                $data[$fieldCode] = sprintf("Mandate not found.", $mandateReference);
                $fieldset->addField($fieldCode, 'label', array('label' => $this->_helper()->__($fieldName)));
            }
        }

        return $data;
    }

    public function getExportButton() {
        return array(
            'label' => Mage::helper('xonu_sepaone')->__('EBICS Export'),
            'class'     => 'scalable go',
            'onclick'   => 'setLocation(\'' . $this->getExportUrl() .'\')',
        );
    }

    protected function getExportUrl() {
        return Mage::helper("adminhtml")->getUrl('adminhtml/sepaone_api/index');
    }

    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_sepaone');
        return $this->_helper;
    }
}
