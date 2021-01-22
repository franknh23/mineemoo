<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */

class Xonu_Directdebit_Block_Adminhtml_Export_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    protected $_helper;

    public function __construct() {
        parent::__construct();
        $this->setId('edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->_helper()->__('SEPA Direct Debit Payments'));
    }

    protected function _beforeToHtml() {
        $this->addTab('form_export', array(
            'label' => $label = $this->_helper()->__('Export Status'), 'title' => $label,
            'content' => $this->getLayout()->createBlock('xonu_directdebit/adminhtml_export_edit_tab_export')->toHtml(),
        ));

        $this->addTab('form_history', array(
            'label' => $label = $this->_helper()->__('Export History'), 'title' => $label,
            'content' => $this->getLayout()->createBlock('xonu_directdebit/adminhtml_export_edit_tab_history')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }

    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_directdebit');
        return $this->_helper;
    }
}