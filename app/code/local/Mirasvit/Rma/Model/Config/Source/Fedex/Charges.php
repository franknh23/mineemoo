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



class Mirasvit_Rma_Model_Config_Source_Fedex_Charges
{
    /*
     * Constructs shipment methods list for RMA FedEx Config
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            Mirasvit_Rma_Model_Config::FEDEX_SHIPPING_CHARGE_PAYS_RECIPIENT => Mage::helper('rma')->__('Recipient'),
            Mirasvit_Rma_Model_Config::FEDEX_SHIPPING_CHARGE_PAYS_SENDER => Mage::helper('rma')->__('Sender'),
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
