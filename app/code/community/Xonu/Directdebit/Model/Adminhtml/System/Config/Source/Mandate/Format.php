<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Model_Adminhtml_System_Config_Source_Mandate_Format
{
    protected $_options;

    public function toOptionArray()
    {
        if (is_null($this->_options)) {
            $this->_options = array();
            $helper = Mage::helper('xonu_directdebit');
            $mandate = Mage::getModel('xonu_directdebit/mandate');
            $mandate->setStoreId(1); // necessary for the numbering format preview

            /*
            $this->_options[] = array(
                'value' => $format = Xonu_Directdebit_Model_Mandate::MANDATE_ID_FORMAT_INCREMENT,
                'label' => sprintf(
                    $helper->__('%s - Sequential Numbering', $mandate->createMandateIdentifier($format))),
            );
            */

            $this->_options[] = array(
                'value' => $format = Xonu_Directdebit_Model_Mandate::MANDATE_ID_FORMAT_DATETIME17,
                'label' => sprintf(
                    $helper->__('%s - Date and Time Concatenated', $mandate->createMandateIdentifier($format))),
            );

            $this->_options[] = array(
                'value' => $format = Xonu_Directdebit_Model_Mandate::MANDATE_ID_FORMAT_DATETIME19,
                'label' => sprintf(
                    $helper->__('%s - Date and Time Separated', $mandate->createMandateIdentifier($format))),
            );

            $this->_options[] = array(
                'value' => $format = Xonu_Directdebit_Model_Mandate::MANDATE_ID_FORMAT_DATETIME23,
                'label' => sprintf(
                    $helper->__('%s - Date and Time Fully Separated', $mandate->createMandateIdentifier($format))),
            );

            $this->_options[] = array(
                'value' => $format = Xonu_Directdebit_Model_Mandate::MANDATE_ID_FORMAT_UNIXTIME,
                'label' => sprintf(
                    $helper->__('%s - Unix Timestamp with Microseconds', $mandate->createMandateIdentifier($format))),
            );
        }
        return $this->_options;
    }

    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_directdebit');
        return $this->_helper;
    }

}