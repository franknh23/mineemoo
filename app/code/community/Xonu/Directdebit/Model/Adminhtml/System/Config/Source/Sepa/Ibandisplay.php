<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Model_Adminhtml_System_Config_Source_Sepa_Ibandisplay
{

    protected $_options;

    public function toOptionArray()
    {
        if (is_null($this->_options)) {
            $this->_options = array();

            $this->_options[] = array(
                'value' => 0,
                'label' => 'LLPPBBBBBBBBKKKKKKKKKK',
            );

            $this->_options[] = array(
                'value' => 1,
                'label' => 'LL PP BB BB BB BB KK KK KK KK KK',
            );

            $this->_options[] = array(
                'value' => 2,
                'label' => 'LLPP BBBB BBBB KKKK KKKK KK',
            );

        }
        return $this->_options;
    }

}