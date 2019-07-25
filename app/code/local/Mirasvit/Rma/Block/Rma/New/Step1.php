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



class Mirasvit_Rma_Block_Rma_New_Step1 extends Mirasvit_Rma_Block_Rma_New
{
    /**
     * @return string
     */
    public function getStep1PostUrl()
    {
        return Mage::getUrl('rma/rma_new/step2');
    }


    /**
     * @return array
     */
    public function getOrderCollection()
    {
        if ($this->getCustomer()->getId()) {
            $orders = Mage::helper('rma')->getAllowedOrderCollection($this->getCustomer());
        } else {
            $orders = Mage::registry('guest_orders');
            if (!$orders) {
                $orders = array();
            }
        }
        return $orders;
    }

    /**
     * @return int
     */
    public function getReturnPeriod()
    {
        return $this->getConfig()->getPolicyReturnPeriod();
    }

}
