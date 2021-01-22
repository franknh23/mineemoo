<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */

class Xonu_Directdebit_Block_Adminhtml_Mandate_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    protected $_helper;

    public function __construct() {
        parent::__construct();
        $this->setId('edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->_helper()->__('SEPA Direct Debit Mandate'));
    }

    protected function _beforeToHtml() {
        $this->addTab('form_data', array(
            'label' => $label = $this->_helper()->__('Data'), 'title' => $label,
            'content' => $this->getLayout()->createBlock('xonu_directdebit/adminhtml_mandate_edit_tab_data')->toHtml(),
        ));

        $this->addTab('form_document', array(
            'label' => $label = $this->_helper()->__('Document'), 'title' => $label,
            'content' => $this->getLayout()->createBlock('xonu_directdebit/adminhtml_mandate_edit_tab_document')->toHtml(),
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order')) {
            $this->addTab('form_orders', array(
                'label' => $label = $this->_helper()->__('Orders'), 'title' => $label,
                'content' => $this->getLayout()->createBlock('xonu_directdebit/adminhtml_mandate_edit_tab_orders')->toHtml(),
            ));
        }

        return parent::_beforeToHtml();
    }

    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_directdebit');
        return $this->_helper;
    }
}