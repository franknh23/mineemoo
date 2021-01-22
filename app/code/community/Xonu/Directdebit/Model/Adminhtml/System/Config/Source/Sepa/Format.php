<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Model_Adminhtml_System_Config_Source_Sepa_Format
{

    protected $_options;

    public function toOptionArray()
    {
        if (is_null($this->_options)) {
            $this->_options = array();

            $this->_options[] = array(
                'value' => 'pain.008.001.02',
                'label' => 'pain.008.001.02',
            );

            $this->_options[] = array(
                'value' => 'pain.008.002.02',
                'label' => 'pain.008.002.02',
            );

            $this->_options[] = array(
                'value' => 'pain.008.003.02',
                'label' => 'pain.008.003.02',
            );
        }
        return $this->_options;
    }

}