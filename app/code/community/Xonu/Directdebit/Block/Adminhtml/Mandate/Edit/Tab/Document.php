<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */

class Xonu_Directdebit_Block_Adminhtml_Mandate_Edit_Tab_Document extends Mage_Adminhtml_Block_Widget_Form {

    protected $_helper;

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        /* @var $mandate Xonu_Directdebit_Model_Mandate */
        $mandate = Mage::registry('xonu_directdebit_mandate');

        $data = array();

        $fieldset = $form->addFieldset('mandate_document', array('legend' => $this->_helper()->__('Document')));


        $fieldset->addType('html', 'Xonu_Directdebit_Block_Adminhtml_Mandate_Edit_Form_Element_Document');

        $fieldset->addField('document_html', 'html', array(
            'label' => '',
            'document_html' => $mandate->getDocumentHtml(),
        ));

        $form->setValues($data);
        return parent::_prepareForm();
    }

    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_directdebit');
        return $this->_helper;
    }

}