<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Model_Adminhtml_System_Config_Source_Customer_Group
{
    /**
     * @var array Customer Groups
     */
    protected $_options;

    /**
     * array of customer groups for backend select
     * @return array Customer Groups
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $collection = Mage::getResourceModel('customer/group_collection')
                ->loadData()
                ->toOptionArray();
            $this->_options = $collection;

            /*
            array_unshift(
                $this->_options,
                array(
                    'value' => '',
                    'label' => Mage::helper('xonu_directdebit')->__('-- Please Select --')
                )
            );
            */
        }

        return $this->_options;
    }
}