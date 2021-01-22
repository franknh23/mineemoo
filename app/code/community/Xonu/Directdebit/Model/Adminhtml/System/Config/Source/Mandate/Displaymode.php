<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Model_Adminhtml_System_Config_Source_Mandate_Displaymode
{
    protected $_options;

    public function toOptionArray()
    {
        if (is_null($this->_options)) {
            $this->_options = array();
            $helper = Mage::helper('xonu_directdebit');

            $this->_options[] = array(
                'value' => Xonu_Directdebit_Model_Mandate::ACCOUNT_DISPLAY_MODE_FULL,
                'label' => $helper->__('Display Bank Account Details'),
            );

            $this->_options[] = array(
                'value' => Xonu_Directdebit_Model_Mandate::ACCOUNT_DISPLAY_MODE_PARTIAL,
                'label' => $helper->__('Display BIC and partially IBAN'),
            );

            $this->_options[] = array(
                'value' => Xonu_Directdebit_Model_Mandate::ACCOUNT_DISPLAY_MODE_HIDE,
                'label' => $helper->__('Display Mandate Identifier Only'),
            );
        }
        return $this->_options;
    }

    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_directdebit');
        return $this->_helper;
    }

}