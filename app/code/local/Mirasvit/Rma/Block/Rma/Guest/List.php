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



class Mirasvit_Rma_Block_Rma_Guest_List extends Mirasvit_Rma_Block_Rma_Guest_Abstract
{
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    protected $_collection;
    public function getRmaCollection()
    {
        if (!$this->_collection) {
            $orders = Mage::helper('rma')
                ->getAllowedOrderCollection(null, false)
                ->addFieldToFilter('customer_email', $this->getCustomerEmail());

            $ordersId = array(
                $this->getOrder()->getId() => $this->getOrder()->getId(),
            );

            // cause we have know one order after login
            if ($orders->count() > 1) {
                foreach ($orders as $order) {
                    $ordersId[$order->getId()] = $order->getId();
                }
            }

            $this->_collection = Mage::helper('rma')
                ->getRmaByOrder($ordersId)
                ->setOrder('created_at', 'desc');
        }

        return $this->_collection;
    }

    public function getCustomerEmail()
    {
        return Mage::getSingleton('customer/session')->getRmaGuestEmail();
    }
}
