<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   RMA
 * @version   2.1.0-beta
 * @build     1359
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Rma_Model_Config_Source_Fedex_Method
{
    /*
     * Constructs shipment methods list for RMA FedEx Config
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            Mirasvit_Rma_Model_Config::FEDEX_METHOD_EUROPE_FIRST_INTERNATIONAL_PRIORITY => Mage::helper('rma')->__('Europe First International Priority'),
            Mirasvit_Rma_Model_Config::FEDEX_METHOD_FEDEX_1_DAY_FREIGHT => Mage::helper('rma')->__('FedEx 1-Day Freight'),
            Mirasvit_Rma_Model_Config::FEDEX_METHOD_FEDEX_2_DAY => Mage::helper('rma')->__('FedEx 2-Day'),
            Mirasvit_Rma_Model_Config::FEDEX_METHOD_FEDEX_2_DAY_FREIGHT => Mage::helper('rma')->__('FedEx 2-Day Freight'),
            Mirasvit_Rma_Model_Config::FEDEX_METHOD_FEDEX_3_DAY_FREIGHT => Mage::helper('rma')->__('FedEx 3-Day Freight'),
            Mirasvit_Rma_Model_Config::FEDEX_METHOD_FEDEX_EXPRESS_SAVER => Mage::helper('rma')->__('FedEx Express Saver'),
            Mirasvit_Rma_Model_Config::FEDEX_METHOD_FEDEX_GROUND => Mage::helper('rma')->__('FedEx Ground'),
            Mirasvit_Rma_Model_Config::FEDEX_METHOD_FIRST_OVERNIGHT => Mage::helper('rma')->__('First Overnight'),
            Mirasvit_Rma_Model_Config::FEDEX_METHOD_GROUND_HOME_DELIVERY => Mage::helper('rma')->__('Ground Home Delivery'),
            Mirasvit_Rma_Model_Config::FEDEX_METHOD_INTERNATIONAL_ECONOMY => Mage::helper('rma')->__('International Economy'),
            Mirasvit_Rma_Model_Config::FEDEX_METHOD_INTERNATIONAL_ECONOMY_FREIGHT => Mage::helper('rma')->__('International Economy Freight'),
            Mirasvit_Rma_Model_Config::FEDEX_METHOD_INTERNATIONAL_FIRST => Mage::helper('rma')->__('International First'),
            Mirasvit_Rma_Model_Config::FEDEX_METHOD_INTERNATIONAL_GROUND => Mage::helper('rma')->__('International Ground'),
            Mirasvit_Rma_Model_Config::FEDEX_METHOD_INTERNATIONAL_PRIORITY => Mage::helper('rma')->__('International Priority'),
            Mirasvit_Rma_Model_Config::FEDEX_METHOD_INTERNATIONAL_PRIORITY_FREIGHT => Mage::helper('rma')->__('International Priority Freight'),
            Mirasvit_Rma_Model_Config::FEDEX_METHOD_PRIORITY_OVERNIGHT => Mage::helper('rma')->__('Priority Overnight'),
            Mirasvit_Rma_Model_Config::FEDEX_METHOD_SMART_POST => Mage::helper('rma')->__('Smart Post'),
            Mirasvit_Rma_Model_Config::FEDEX_METHOD_STANDARD_OVERNIGHT => Mage::helper('rma')->__('Standard Overnight'),
        );
    }

    /*
     * Constructs option list for RMA FedEx Config
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = array();
        foreach ($this->toArray() as $k => $v) {
            $result[] = array('value' => $k, 'label' => $v);
        }

        return $result;
    }
}
