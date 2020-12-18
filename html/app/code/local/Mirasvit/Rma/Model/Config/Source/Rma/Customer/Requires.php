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



class Mirasvit_Rma_Model_Config_Source_Rma_Customer_Requires
{
    public function toArray()
    {
        $options = array(
            Mirasvit_Rma_Model_Config::RMA_CUSTOMER_REQUIRES_REASON => Mage::helper('rma')->__('Reason'),
            Mirasvit_Rma_Model_Config::RMA_CUSTOMER_REQUIRES_CONDITION => Mage::helper('rma')->__('Condition'),
            Mirasvit_Rma_Model_Config::RMA_CUSTOMER_REQUIRES_RESOLUTION => Mage::helper('rma')->__('Resolution'),
        );

        return $options;
    }
    public function toOptionArray()
    {
        $result = array(array('value' => '', 'label' => ''));
        foreach ($this->toArray() as $k => $v) {
            $result[] = array('value' => $k, 'label' => $v);
        }

        return $result;
    }

    /************************/
}
