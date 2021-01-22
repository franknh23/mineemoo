<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Model_Adminhtml_System_Config_Source_Order_Status
{
    protected $_options;

    public function toOptionArray()
    {
        if (is_null($this->_options)) {
            $this->_options = array();
            /* @var $config Mage_Sales_Model_Order_Config */
            $config = Mage::getModel('sales/order_config');


            // $statusCollection = $config->getStatuses(); // we do not support choosing arbitrary status
            $statusCollection = $config->getStateStatuses('new'); // get statuses from the state new

            foreach ($statusCollection as $code => $label)
            {
                $this->_options[] = array(
                    'value' => $code,
                    'label' => $label,
                );
            }
        }
        return $this->_options;
    }
}
