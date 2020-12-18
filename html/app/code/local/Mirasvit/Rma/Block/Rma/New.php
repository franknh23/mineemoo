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



class Mirasvit_Rma_Block_Rma_New extends Mage_Core_Block_Template
{
    /**
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle(Mage::helper('rma')->__('Request new return'));
        }
    }

    /**
     * @return Mirasvit_Rma_Model_Config
     */
    protected function getConfig()
    {
        return Mage::getSingleton('rma/config');
    }

    /**
     * @return Mage_Customer_Model_Customer
     */
    protected function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

//    protected $orders;
//    public function getOrders()
//    {
//        if (!$this->orders) {
//            if ($orders = Mage::app()->getRequest()->getParam('orders')) {
//                $collection = Mage::helper('rma')->getAllowedOrderCollection($this->getCustomer(), false);
//                $collection->addFieldToFilter('entity_id', $orders);
//                if ($collection->count()) {
//                    $this->orders = $collection;
//                }
//
//                if ($orders = Mage::app()->getRequest()->getParam('offline_orders')) {
//                    foreach ($orders as $data) {
//                        $order = Mage::helper('rma/order')->createOfflineOrder($data);
//
//                        if (!$this->orders) {
//                            $this->orders = $collection->addItem($order);
//                        } else {
//                            $this->orders->addItem($order);
//                        }
//                    }
//                }
//            }
//        }
//
//        return $this->orders;
//    }
//
//
//
//
//    public function getOrderItemCollection()
//    {
//        $order = $this->getOrder();
//        $collection = $order->getItemsCollection();
//
//        return $collection;
//    }
//
    /**
     * @return int
     */
    public function getStoreId()
    {
        return Mage::app()->getStore()->getId();
    }
//
//
//    public function getPolicyIsActive()
//    {
//        return $this->getConfig()->getPolicyIsActive();
//    }
//
//    protected $_pblock;
//    public function getPolicyBlock()
//    {
//        if (!$this->_pblock) {
//            $this->_pblock = Mage::getModel('cms/block')->load($this->getConfig()->getPolicyPolicyBlock());
//        }
//
//        return $this->_pblock;
//    }
//
//    public function getPolicyTitle()
//    {
//        return $this->getPolicyBlock()->getTitle();
//    }
//
//    public function getPolicyContent()
//    {
//        $helper = Mage::helper('cms');
//        $processor = $helper->getPageTemplateProcessor();
//
//        return $processor->filter($this->getPolicyBlock()->getContent());
//    }
//

//
//    public function getIsGift()
//    {
//        return Mage::app()->getRequest()->getParam('is_gift') == 1;
//    }

    /**
     * @param int $orderItem
     *
     * @return Mirasvit_Rma_Model_Item[]|Mirasvit_Rma_Model_Resource_Item_Collection
     */
    public function getRmaItemsByOrderItem($orderItem)
    {
        $collection = Mage::getModel('rma/item')->getCollection();
        $collection->addFieldToFilter('order_item_id', $orderItem->getId());
        $collection->addFieldToFilter('qty_requested', array('gt' => 0));

        return $collection;
    }

    /**
     * @param Mirasvit_Rma_Model_Rma $rma
     *
     * @return string
     */
    public function getRMAUrl($rma)
    {
        return $rma->getUrl();
    }

    /**
     * @param int $orderItem
     *
     * @return string
     */
    public function getRmasByOrderItem($orderItem)
    {
        $result = array();
        foreach ($this->getRmaItemsByOrderItem($orderItem) as $item) {
            $rma = Mage::getModel('rma/rma')->load($item->getRmaId());
            $result[] = "<a href='{$this->getRMAUrl($rma)}' target='_blank'>#{$rma->getIncrementId()}</a>";
        }

        return implode(', ', $result);
    }
}
