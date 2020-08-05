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



class Mirasvit_Rma_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getConfig()
    {
        return Mage::getSingleton('rma/config');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return Mage_Core_Model_Store
     */
    public function getStoreByOrder($order)
    {
        return ($order) ? Mage::getModel('core/store')->load($order->getStoreId()) : Mage::app()->getStore();
    }

    public function toAdminUserOptionArray($emptyOption = false)
    {
        $arr = Mage::getModel('admin/user')->getCollection()->toArray();
        $result = array();
        foreach ($arr['items'] as $value) {
            $result[] = array('value' => $value['user_id'], 'label' => $value['firstname'].' '.$value['lastname']);
        }
        if ($emptyOption) {
            array_unshift($result, array('value' => 0, 'label' => Mage::helper('rma')->__('-- Please Select --')));
        }

        return $result;
    }
    public function getAdminUserOptionArray($emptyOption = false)
    {
        $arr = Mage::getModel('admin/user')->getCollection()->toArray();
        $result = array();
        foreach ($arr['items'] as $value) {
            $result[$value['user_id']] = $value['firstname'].' '.$value['lastname'];
        }
        if ($emptyOption) {
            $result[0] = Mage::helper('rma')->__('-- Please Select --');
        }

        return $result;
    }
    public function getCoreStoreOptionArray()
    {
        $arr = Mage::getModel('core/store')->getCollection()->toArray();
        foreach ($arr['items'] as $value) {
            $result[$value['store_id']] = $value['name'];
        }

        return $result;
    }

    /************************/

    /**
     * Returns all RMA's from specific order. If need, can exclude given RMA ID or array of IDs.
     *
     * @param Mage_Sales_Model_Order $order
     * @param bool|int|int[]         $excludeId
     *
     * @return Mirasvit_Rma_Model_Rma[]
     */
    public function getRmaByOrder($order, $excludeId = false)
    {
        if (is_object($order)) {
            $order = $order->getId();
        }

        $rmas = Mage::getModel('rma/rma')->getCollection()
            ->joinRmaItems()
            ->addFieldToFilter('items.order_id', $order);

        if ($excludeId) {
            // Exclude RMA's, if need
            $filter = is_array($excludeId) ? array('nin' => $excludeId) : array('neq' => $excludeId);
            $rmas->addFieldToFilter('main_table.rma_id', $filter);
        }

        return $rmas;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param string|null            $url
     *
     * @return string
     */
    public function getOrderLabel($order, $url = null)
    {
        if (!is_object($order)) {
            $order = Mage::getModel('sales/order')->load($order);
        }

        if ($order->getIsOffline()) {
            return $order->getName();
        }

        $res = "#{$order->getRealorderId()}";
        if ($url) {
            $res = "<a href='{$url}' target='_blank'>$res</a>";
        }
        $res .= Mage::helper('rma')->__(' at %s (%s)',
            Mage::helper('core')->formatDate($order->getCreatedAt(), 'medium'),
            strip_tags($order->formatPrice($order->getGrandTotal()))
        );

        return $res;
    }

    public function getOrderItemLabel($item)
    {
        $name = $item->getName();
        if (!$name && is_object($item->getProduct())) { //old versions support
            $name = $item->getProduct()->getName();
        }
        $options = $this->getItemOptions($item);
        if (count($options)) {
            $name .= ' (';
            foreach ($options as $option) {
                $name .= $option['label'].': '.$option['value'].', ';
            }
            $name = substr($name, 0, -2); //remove last ,
            $name .= ')';
        }

        return $name;
    }

    /**
     * @param Mirasvit_Rma_Model_Item $item
     * @param string                  $url
     *
     * @return string
     */
    public function getItemOrderLabel($item, $url = null)
    {
        $orderId = $item->getOrderId();
        if ($orderId) {
            return $this->getOrderLabel($orderId, $url);
        }

        return $item->getOfflineOrderName();
    }

    /**
     * Returns e.g. $295.00.
     *
     * @param Mirasvit_Rma_Model_Item $item
     *
     * @return string
     */
    public function getOrderItemPriceFormatted($item)
    {
        $orderItem = $item->getOrderItem();
        if ($orderItem->getId()) {
            return Mage::helper('core')->currencyByStore($orderItem->getPrice(), $orderItem->getStore(), true, false);
        } else {
            return '';
        }
    }

    /**
     * @param Mirasvit_Rma_Model_Item $orderItem
     *
     * @return array
     */
    public function getItemOptions($orderItem)
    {
        $result = array();
        if ($options = $orderItem->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }

        return $result;
    }

    public function generateIncrementId($rma)
    {
        $id = (string) $rma->getId();
        $storeId = (string) $rma->getStoreId();

        $config = $this->getConfig();
        $format = $config->getNumberFormat();
        $maxLen = $config->getNumberCounterLength();
        $counter = $config->getNumberCounterStart() + $id * $config->getNumberCounterStep() - 1;
        $counter = str_repeat('0', $maxLen - strlen($counter)).$counter;

        $result = str_replace('[counter]', $counter, $format);
        $result = str_replace('[store]', $storeId, $result);
//        $result = str_replace('[order]', $rma->getOrder()->getIncrementId(), $result);

        $collection = Mage::getModel('rma/rma')->getCollection()
            ->addFieldToFilter('main_table.increment_id', array('like' => $result.'%'));

        if ($collection->count()) {
            $result .= '-'.($collection->count() + 1);
        }

        return $result;
    }

    /**
     * @param Mirasvit_Rma_Model_Rma      $rma
     * @param Mage_Sales_Model_Order_Item $item
     *
     * @return Mirasvit_Rma_Model_Item
     */
    protected function _convertRmaItem($rma, $item)
    {
        if (!$rma || !$rma->getId()) {
            $item = Mage::getModel('rma/item')->initFromOrderItem($item);
        }

        return $item;
    }

    /**
     * @param null|Mirasvit_Rma_Model_Rma $rma
     * @param null|Mage_Sales_Model_Order $orders
     *
     * @return Mirasvit_Rma_Model_Item[][]
     */
    public function getRmaItemsByRmaGrouped($rma = null, $orders = null)
    {
        $return = array();
        $items = $this->getRmaItemsByRma($rma);
        foreach ($items as $item) {
            if ($item->getOrderId()) {
                $return[$item->getOrderId()][] = $item;
            } else {
                $return[$item->getOfflineOrderName()][] = $item;
            }
        }

        return $return;
    }

    /**
     * @param null|Mirasvit_Rma_Model_Resource_Item_Collection $collection
     * @param null|Mirasvit_Rma_Model_Rma                      $rma
     *
     * @return Mirasvit_Rma_Model_Item[]
     */
    private function prepareRmaItems($collection, $rma)
    {
        $items = array();

        /** @var Mage_Sales_Model_Order_Item $item */
        foreach ($collection as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            if ($item->getProductType() == 'bundle' && $this->getConfig()->getPolicyBundleOneByOne()) {
                $items[] = $this->_convertRmaItem($rma, $item);
                foreach ($item->getChildrenItems() as $bundleItem) {
                    $bundleItem = $this->_convertRmaItem($rma, $bundleItem);
                    $bundleItem->setIsBundleItem(true);
                    $items[] = $bundleItem;
                }
            } else {
                $item = $this->_convertRmaItem($rma, $item);
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @param null|Mirasvit_Rma_Model_Rma $rma
     *
     * @return Mirasvit_Rma_Model_Item[]
     */
    public function getRmaItemsByRma($rma)
    {
        if ($rma && $rma->getId()) {
            $collection = Mage::getModel('rma/item')->getCollection()
                ->addFieldToFilter('rma_id', $rma->getId());
        } else {
            $orders = $rma->getOrders();
            $collection = $this->getRmaItemsByOrders($orders);
        }

        return $this->prepareRmaItems($collection, $rma);
    }

    /**
     * @param Mage_Sales_Model_Order|Mage_Sales_Model_Resource_Order_Collection $orders
     *
     * @return Mirasvit_Rma_Model_Item[]|Mirasvit_Rma_Model_Resource_Item_Collection
     */
    public function getRmaItemsByOrders($orders)
    {
        $collection = Mage::getResourceModel('sales/order_item_collection');
        if ($orders) {
            if ($orders instanceof Mage_Core_Model_Abstract) {
                $orders = array($orders);
            }
        } else {
            $orders = array();
        }
        $ordersId = array(-1);
        foreach ($orders as $order) {
            $ordersId[] = $order->getId();
        }
        $collection->addFieldToFilter('order_id', $ordersId);

        return $this->prepareRmaItems($collection, null);
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public function convertToHtml($text)
    {
        $html = nl2br($text);

        return $html;
    }

    public function getStatusCollection()
    {
        $collection = Mage::getModel('rma/status')->getCollection()
            ->addFieldToFilter('is_active', true);

        return $collection;
    }

    public function copyEmailAttachments($email, $comment)
    {
        foreach ($email->getAttachments() as $emailAttachment) {
            Mage::getModel('mstcore/attachment')
                ->setEntityId($comment->getId())
                ->setEntityType('COMMENT')
                ->setName($emailAttachment->getName())
                ->setSize($emailAttachment->getSize())
                ->setBody($emailAttachment->getBody())
                ->setType($emailAttachment->getType())
                ->save();
        }
    }

    public function getReturnPeriod()
    {
        return  $this->getConfig()->getPolicyReturnPeriod();
    }

    /**
     * We calculate days from the next day of order has received the status 'complete'.
     */
    public function getLastReturnGmtDate()
    {
        $offset = gmdate('H') * 60 * 60 +  gmdate('i') * 60 + gmdate('s');
        $time = gmdate('U') - ($this->getReturnPeriod() + 1) * 24 * 60 * 60 - $offset;

        return Mage::getSingleton('core/date')->gmtDate(null, $time);
    }

    public function getAllowedOrderCollection($customer = false, $isLimitDate = true)
    {
        $allowedStatuses = $this->getConfig()->getPolicyAllowInStatuses();
        $limitDate = $this->getLastReturnGmtDate();
        $collection = Mage::getModel('sales/order')->getCollection();
        $collection->getSelect()->where("main_table.status IN ('".implode("','", $allowedStatuses)."')");
        if ($isLimitDate) {
            $collection->getSelect()->where('
                ((select MAX(created_at) from `'.Mage::getConfig()->getTablePrefix().'sales_flat_order_status_history'."`
                where status IN ('".implode("','", $allowedStatuses)."')
                      and parent_id = main_table.entity_id
                ) >= '$limitDate')
                ");
        }
        if ($customer && $customer->getId()) {
            $collection->addFieldToFilter('customer_id', (int) $customer->getId());
        }
        $collection->addAttributeToSelect('*')
                ->setOrder('updated_at', 'desc')
                ;

        return $collection;
    }

    public function isReturnAllowed($order)
    {
        if (is_object($order)) {
            $order = $order->getId();
        }
        $collection = $this->getAllowedOrderCollection();
        $collection->addFieldToFilter('entity_id', (array) $order);

        return $collection->count() > 0;
    }

    public function getStatusByCode($code)
    {
        $collection = Mage::getModel('rma/status')->getCollection();
        $collection->addFieldToFilter('code', $code);
        if ($collection->count()) {
            return $collection->getFirstItem();
        }
    }

    public function getResolutionByCode($code)
    {
        $collection = Mage::getModel('rma/resolution')->getCollection();
        $collection->addFieldToFilter('code', $code);
        if ($collection->count()) {
            return $collection->getFirstItem();
        }
    }

    public function getCssFile()
    {
        if (file_exists(Mage::getBaseDir('skin').'/frontend/base/default/css/mirasvit/rma/custom.css')) {
            return 'css/mirasvit/rma/custom.css';
        }
        if (Mage::getVersion() >= '1.9.0.0') {
            return 'css/mirasvit/rma/rwd.css';
        }

        return 'css/mirasvit/rma/fixed.css';
    }
}
