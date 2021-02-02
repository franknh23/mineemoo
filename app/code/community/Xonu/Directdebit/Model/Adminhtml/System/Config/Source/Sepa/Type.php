<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Model_Adminhtml_System_Config_Source_Sepa_Type
{

    protected $_options;

    public function toOptionArray()
    {
        $helper = Mage::helper('xonu_directdebit');

        if (is_null($this->_options)) {
            $this->_options = array();

            $this->_options[] = array(
                'value' => 'CORE',
                'label' => 'CORE',
            );

            $this->_options[] = array(
                'value' => 'COR1',
                'label' => $helper->__('COR1 (requires pain.008.003.02)'),
            );

            $this->_options[] = array(
                'value' => 'B2B',
                'label' => 'B2B',
            );
        }
        return $this->_options;
    }

}