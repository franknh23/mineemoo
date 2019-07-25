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



/**
 * @method Mirasvit_Rma_Model_Resource_Rma_Collection|Mirasvit_Rma_Model_Rma[] getCollection()
 * @method Mirasvit_Rma_Model_Rma load(int $id)
 * @method bool getIsMassDelete()
 * @method Mirasvit_Rma_Model_Rma setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method Mirasvit_Rma_Model_Rma setIsMassStatus(bool $flag)
 * @method Mirasvit_Rma_Model_Resource_Rma getResource()
 * @method int getRmaId()
 * @method Mirasvit_Rma_Model_Rma setRmaId(int $rmaId)
 * @method int getOrderId()
 * @method Mirasvit_Rma_Model_Rma setOrderId(int $entityId)
 * @method int getStoreId()
 * @method Mirasvit_Rma_Model_Rma setStoreId(int $storeId)
 * @method int getCustomerId()
 * @method Mirasvit_Rma_Model_Rma setCustomerId(int $entityId)
 * @method int getUserId()
 * @method Mirasvit_Rma_Model_Rma setUserId(int $entityId)
 * @method int getStatusId()
 * @method Mirasvit_Rma_Model_Rma setStatusId(int $statusId)
 * @method string getLastReplyName()
 * @method Mirasvit_Rma_Model_Rma setLastReplyName(string $field)
 * @method bool getIsAdminRead()
 * @method Mirasvit_Rma_Model_Rma setIsAdminRead(bool $field)
 * @method string getCreatedAt()
 * @method Mirasvit_Rma_Model_Rma setCreatedAt(string $field)
 * @method string getUpdatedAt()
 * @method Mirasvit_Rma_Model_Rma setUpdatedAt(string $field)
 * @method array getExchangeOrderIds()
 * @method Mirasvit_Rma_Model_Rma setExchangeOrderIds(array $ids)
 * @method array getCreditMemoIds()
 * @method Mirasvit_Rma_Model_Rma setCreditMemoIds(array $ids)
 */
class Mirasvit_Rma_Model_Rma extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('rma/rma');
    }

    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    protected $_itemCollection;

    /**
     * @return Mirasvit_Rma_Model_Item[]|Mirasvit_Rma_Model_Resource_Item_Collection
     */
    public function getItemCollection()
    {
        if (!$this->_itemCollection) {
            $this->_itemCollection = Mage::getModel('rma/item')->getCollection()
                ->addFieldToFilter('rma_id', $this->getRmaId())
                ->addFieldToFilter('qty_requested', array('gt' => 0))
                ->addOrder('main_table.item_id', 'ASC')
            ;
        }

        return $this->_itemCollection;
    }

    protected $itemsCollection;
    /**
     * @return Mirasvit_Rma_Model_Item[]|Mirasvit_Rma_Model_Resource_Item_Collection
     */
    public function getItemsCollection()
    {
        if (!$this->itemsCollection) {
            $this->itemsCollection = Mage::getModel('rma/item')->getCollection()
                ->addFieldToFilter('rma_id', $this->getRmaId());
        }

        return $this->itemsCollection;
    }

    protected $_commentCollection;
    /**
     * @return Mirasvit_Rma_Model_Comment[]|Mirasvit_Rma_Model_Resource_Comment_Collection
     */
    public function getCommentCollection()
    {
        if (!$this->_commentCollection) {
            $this->_commentCollection = Mage::getModel('rma/comment')->getCollection()
                ->addFieldToFilter('rma_id', $this->getRmaId());
        }

        return $this->_commentCollection;
    }

    protected $_order = null;
    protected $orders = null;
    protected $orderArray = null;

    /**
     * @return bool|Mage_Sales_Model_Order
     */
    public function getOrders()
    {
        if ($this->orders == null) {
            if ($this->getRmaId()) {
                $items = $this->getItemsCollection()
                    ->getOrdersId();

                if (!$items->count()) {
                    return false;
                }

                $orders = $this->prepareOfflineOrders($items);
                $ordersId = $orders['orders'];
                $offlineOrders = $orders['offline_orders'];

                if ($ordersId) {
                    $this->orders = Mage::getModel('sales/order')->getCollection()
                        ->addFieldToFilter('entity_id', $ordersId);
                }
                if ($offlineOrders) {
                    if (!$this->orders) {
                        $this->orders = Mage::getModel('sales/order')->getCollection()
                            ->addFieldToFilter('entity_id', 0);
                    }
                    foreach ($offlineOrders as $offlineOrder) {
                        $order = Mage::helper('rma/order')->createOfflineOrder($offlineOrder);
                        $this->orders->addItem($order);
                    }
                }
            } elseif ($this->getOrdersId()) {
                $this->orders = Mage::getModel('sales/order')->getCollection()
                    ->addFieldToFilter('entity_id', $this->getOrdersId());
            }
        }

        return $this->orders;
    }

    /**
     * @return null|array array of orders
     */
    public function loadOrders()
    {
        if ($this->orderArray == null) {
            $orders = $this->getOrders();
            if ($orders) {
                foreach ($orders as $order) {
                    $this->orderArray[$order->getId()] = $order;
                }
            }
        }

        return $this->orderArray;
    }

    /**
     * @param int $id
     */
    public function loadOrder($id)
    {
        if ($this->orderArray == null) {
            $this->loadOrders();
        }

        return isset($this->orderArray[$id]) ? $this->orderArray[$id] : null;
    }

    /**
     * @return array
     */
    public function getOrdersId()
    {
        if (is_string($this->orders_id)) {
            return explode(',', $this->orders_id);
        }

        return (array) $this->orders_id;
    }

    /**
     * @param array|Varien_Data_Collection $items
     *
     * @return array
     */
    private function prepareOfflineOrders($items)
    {
        $ordersId = array();
        $offlineOrders = array();
        foreach ($items as $item) {
            $ordersId[$item->getOrderId()] = $item->getOrderId();
            if (!$item->getOrderId()) {
                $offlineOrders[$item->getOfflineOrderName()]['id'] = $item->getOfflineOrderName();
                if ($this->getCustomerId()) {
                    $offlineOrders[$item->getOfflineOrderName()]['customer_id'] = $this->getCustomerId();
                }
                if ($this->getOfflineAddress()) {
                    $offlineOrders[$item->getOfflineOrderName()]['address'] = $this->getOfflineAddress();
                }
                $offlineOrders[$item->getOfflineOrderName()]['items'][$item->getOfflineOrderName()] = $item->getData();
            }
        }

        return array('orders' => $ordersId, 'offline_orders' => $offlineOrders);
    }

    /**
     * @param int        $id
     * @param int|string $customerEmail
     *
     * @return bool
     */
    public function getIsRmaAllowed($id, $customerEmail)
    {
        $customerId = (int) $customerEmail;

        // guest customer
        if ($customerId != $customerEmail) {
            $customerId = null;
        }

        $orders = Mage::helper('rma')
            ->getAllowedOrderCollection($customerId, false);

        if (!$customerId) {
            $orders->addFieldToFilter('customer_email', $customerEmail);
        }

        $ordersId = array();
        if (!$orders->count()) {
            return false;
        }
        foreach ($orders as $order) {
            $ordersId[$order->getId()] = $order->getId();
        }

        $collection = Mage::helper('rma')
            ->getRmaByOrder($ordersId)
            ->addFieldToFilter('main_table.rma_id', $id)
            ->setOrder('created_at', 'desc');

        return (bool) $collection->count();
    }

    protected $_store = null;

    /**
     * @return bool|Mage_Core_Model_Store
     */
    public function getStore()
    {
        if (!$this->getStoreId()) {
            return Mage::app()->getDefaultStoreView();
        }
        if ($this->_store === null) {
            $this->_store = Mage::getModel('core/store')->load($this->getStoreId());
        }

        return $this->_store;
    }

    protected $_customer = null;

    /**
     * @return bool|Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if ($this->_customer === null) {
            if ($this->getCustomerId()) {
                $this->_customer = Mage::getModel('customer/customer')->load($this->getCustomerId());
            } elseif ($this->getFirstname()) {
                $this->_customer = new Varien_Object(array(
                    'firstname' => $this->getFirstname(),
                    'lastname' => $this->getLastname(),
                    'name' => $this->getFirstname().' '.$this->getLastname(),
                    'email' => $this->getEmail(),
                ));
            } else {
                $this->_customer = false;
            }
        }

        return $this->_customer;
    }

    protected $_status = null;

    /**
     * @return bool|Mirasvit_Rma_Model_Status
     */
    public function getStatus()
    {
        if (!$this->getStatusId()) {
            return false;
        }
        if ($this->_status === null) {
            $this->_status = Mage::getModel('rma/status')->load($this->getStatusId());
            $this->_status->setStoreId($this->getStoreId());
        }

        return $this->_status;
    }

    /************************/

    protected $_ticket = null;
    public function getTicket()
    {
        if (!$this->getTicketId()) {
            return false;
        }
        if ($this->_ticket === null) {
            $this->_ticket = Mage::getModel('helpdesk/ticket')->load($this->getTicketId());
        }

        return $this->_ticket;
    }

    protected $_creditmemo_order = null;

    /**
     * @return bool|Mage_Core_Model_Abstract|null
     */
    public function getCreditMemo()
    {
        if (!$this->getCreditMemoId()) {
            return false;
        }
        if ($this->_creditmemo_order === null) {
            $this->_creditmemo_order = Mage::getModel('sales/order_creditmemo')->load($this->getCreditMemoId());
        }

        return $this->_creditmemo_order;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Mage::getUrl('rma/rma/view', array('id' => $this->getId()));
    }

    /**
     * @return string
     */
    public function getGuestUrl()
    {
        $url = Mage::helper('rma/url')
            ->getGuestRmaViewUrl(array('id' => $this->getGuestId(), '_store' => $this->getStoreId()));

        return $url;
    }

    /**
     * @return string
     */
    public function getGuestSuccessUrl()
    {
        if (Mage::getSingleton('rma/config')->getGeneralIsAdditionalStepAllowed()) {
            $url = Mage::getUrl('rma/rma/shipment', array('id' => $this->getGuestId(), '_store' => $this->getStoreId()));
        } else {
            $url = Mage::getUrl('rma/rma/success', array('id' => $this->getGuestId(), '_store' => $this->getStoreId()));
        }

        return $url;
    }

    /**
     * @return string
     */
    public function getPrintUrl()
    {
        $url = Mage::getUrl('rma/rma/print', array('id' => $this->getGuestId(), '_store' => $this->getStoreId()));

        return $url;
    }

    /**
     * @return string
     */
    public function getGuestPrintUrl()
    {
        $url = Mage::getUrl('rma/guest/print', array('id' => $this->getGuestId(), '_store' => $this->getStoreId()));

        return $url;
    }

    /**
     * @return bool|string
     */
    public function getGuestPrintLabelUrl()
    {
        if (!$this->getReturnLabel()) {
            return false;
        }

        return Mage::getUrl('rma/guest/printlabel', array('id' => $this->getGuestId(), '_store' => $this->getStoreId()));
    }

    /**
     * @return string
     */
    public function getBackendUrl()
    {
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/rma_rma/edit', array('id' => $this->getId()));

        return $url;
    }

    /**
     * @retrun void
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        if (!$this->getGuestId()) {
            $this->setGuestId(md5($this->getId().Mage::helper('rma/string')->generateRandString(10)));
        }

        $config = Mage::getSingleton('rma/config');
        if (!$this->getId()) {
            $this->setIsAdminRead(true);
        }
        if (!$this->getStatusId()) {
            $this->setStatusId($config->getGeneralDefaultStatus());
        }
        if (!$this->getUserId()) {
            $this->setUserId($config->getGeneralDefaultUser());
        }
        if (!$this->getIsResolved()) {
            $status = $this->getStatus();
            if ($status->getIsRmaResolved()) {
                $this->setIsResolved(true);
            }
        }

        if ($this->getId()) {
            if (!Mage::registry('rma_created')) {
                Mage::helper('rma/ruleevent')->newEvent(Mirasvit_Rma_Model_Config::RULE_EVENT_RMA_UPDATED, $this);
            }
        } else {
            Mage::helper('rma/ruleevent')->newEvent(Mirasvit_Rma_Model_Config::RULE_EVENT_RMA_CREATED, $this);
        }
    }

    /**
     * @return bool
     */
    public function getIsShowShippingBlock()
    {
        if (!$this->getStatus()) {
            return false;
        }

        return $this->getStatus()->getIsRmaResolved();
    }

    /**
     * @throws Exception
     */
    protected function _afterSaveCommit()
    {
        parent::_afterSaveCommit();
        if (!$this->getIncrementId()) {
            $this->setIncrementId(Mage::helper('rma')->generateIncrementId($this));
            $this->save();
        }
    }

    /**
     * @return string
     */
    public function getShippingAddressHtml()
    {
        $items = array();
        $items[] = $this->getFirstname().' '.$this->getLastname();
        if ($this->getEmail()) {
            $items[] = $this->getEmail();
        }
        if ($this->getTelephone()) {
            $items[] = $this->getTelephone();
        }
        if ($this->getCompany()) {
            $items[] = $this->getCompany();
        }
        if ($this->getStreet()) {
            $items[] = $this->getStreet();
        }
        if ($this->getCity()) {
            $items[] = $this->getCity();
        }
        if ($this->getRegion()) {
            $items[] = $this->getRegion();
        }
        if ($this->getPostcode()) {
            $items[] = $this->getPostcode();
        }
        if ($this->getCountryId()) {
            $country = Mage::getModel('directory/country')->loadByCode($this->getCountryId());
            $items[] = $country->getName();
        }

        if ($this->getOfflineAddress()) {
            $items[] = Mage::helper('rma')->convertToHtml('<br>'.$this->getOfflineAddress());
        }

        return trim(implode('<br>', $items));
    }

    /**
     * @return string
     */
    public function getReturnAddress()
    {
        return Mage::getSingleton('rma/config')->getGeneralReturnAddress($this->getStoreId());
    }

    /**
     * @return string
     */
    public function getReturnAddressHtml()
    {
        return Mage::helper('rma')->convertToHtml($this->getReturnAddress());
    }

    /**
     * @param string                              $text
     * @param bool                                $isHtml
     * @param Mage_Customer_Model_Customer        $customer
     * @param Mage_Admin_Model_User               $user
     * @param bool                                $isNotify
     * @param bool                                $isVisible
     * @param bool|true                           $isNotifyAdmin
     * @param Mirasvit_Helpdesk_Model_Email|false $email
     *
     * @return Mirasvit_Rma_Model_Comment
     *
     * @throws Exception
     */
    public function addComment($text, $isHtml, $customer, $user, $isNotify, $isVisible, $isNotifyAdmin = true, $email = false)
    {
        $comment = Mage::getModel('rma/comment')
            ->setRmaId($this->getId())
            ->setText($text, $isHtml)
            ->setIsVisibleInFrontend($isVisible)
            ->setIsCustomerNotified($isNotify)
            ->save();

        if ($email) {
            $comment->setEmailId($email->getId());
            $email->setIsProcessed(true)
                  ->save();
            Mage::helper('rma')->copyEmailAttachments($email, $comment);
        } else {
            $allowedExtensions = Mage::helper('rma/attachment')->getAllowedExtensions();
            $allowedSize = Mage::helper('rma/attachment')->getAllowedSize() * 1024 * 1024;

            Mage::helper('mstcore/attachment')->saveAttachments('COMMENT', $comment->getId(), 'attachment', $allowedExtensions, $allowedSize);
        }

        if ($customer) {
            $comment->setCustomerId($customer->getId())
                    ->setCustomerName($customer->getName());
            $this->setLastReplyName($customer->getName());
            $this->setIsAdminRead(false);
            if ($isNotifyAdmin) {
                Mage::helper('rma/mail')->sendNotificationAdminEmail($this, $comment);
            }
            Mage::helper('rma/ruleevent')->newEvent(Mirasvit_Rma_Model_Config::RULE_EVENT_NEW_CUSTOMER_REPLY, $this);
        } elseif ($user) {
            $this->setLastReplyName($user->getName());
            $this->setIsAdminRead(true);

            $comment->setUserId($user->getId());
            if ($isNotify) {
                Mage::helper('rma/mail')->sendNotificationCustomerEmail($this, $comment);
            }
            //send notification about internal message
            if ($this->getUserId() != $user->getId() && !$isVisible) {
                Mage::helper('rma/mail')->sendNotificationAdminEmail($this, $comment);
            }
            Mage::helper('rma/ruleevent')->newEvent(Mirasvit_Rma_Model_Config::RULE_EVENT_NEW_STAFF_REPLY, $this);
        }

        $comment->save();
        $this->save();

        return $comment;
    }

    /**
     * @param array $ordersId
     *
     * @return $this
     *
     * @throws Exception
     */
    public function initFromOrder($ordersId)
    {
        $this->setOrdersId($ordersId);
        //        $order = $this->getOrders()->getFirstItem();
        $collection = Mage::helper('rma')->getAllowedOrderCollection($this->getCustomer(), false);
        $collection->addFieldToFilter('entity_id', $ordersId);

        if ($orders = Mage::app()->getRequest()->getParam('offline_orders')) {
            foreach ($orders as $data) {
                $order = Mage::helper('rma/order')->createOfflineOrder($data);
                $collection->addItem($order);
            }
        }

        $order = $collection->getLastItem();

        $this->setCustomerId($order->getCustomerId());
        if ($customer = $this->getCustomer()) {
            $data = $customer->getData();
            unset($data['increment_id']);
            $this->addData($data);
        } else {
            $this->setEmail($order->getCustomerEmail());
        }

        $address = $order->getShippingAddress();
        if (!$address) {
            $address = $order->getBillingAddress();
        }
        $data = $address->getData();
        if (!$address->getEmail() || trim($address->getEmail()) == '') {
            unset($data['email']);
        }
        unset($data['increment_id']);
        $this->addData($data);

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getFirstname().' '.$this->getLastname();
    }

    /**
     * @return mixed
     */
    public function getReturnLabel()
    {
        return Mage::helper('mstcore/attachment')->getAttachment('rma_return_label', $this->getId());
    }

    protected $_user = null;

    /**
     * @return bool|Mage_Admin_Model_User|null
     */
    public function getUser()
    {
        if (!$this->getUserId()) {
            return false;
        }
        if ($this->_user === null) {
            $this->_user = Mage::getModel('admin/user')->load($this->getUserId());
        }

        return $this->_user;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return 'RMA-'.$this->getGuestId();
    }

    /**
     * @return Mirasvit_Rma_Model_Comment
     */
    public function getLastComment()
    {
        $collection = Mage::getModel('rma/comment')->getCollection()
            ->addFieldToFilter('rma_id', $this->getId())
            ->setOrder('comment_id', 'asc');
        if ($collection->count()) {
            return $collection->getFirstItem();
        }
    }

    public function getUserName()
    {
        if ($this->getUser()) {
            return $this->getUser()->getName();
        } else {
            return Mage::helper('rma')->__('Unassigned');
        }
    }

    public function getCreatedAtFormated($format)
    {
        return Mage::helper('core')->formatDate($this->getCreatedAt(), $format).' '.Mage::helper('core')->formatTime($this->getCreatedAt(), $format);
    }

    public function getUpdatedAtFormated($format)
    {
        return Mage::helper('core')->formatDate($this->getUpdatedAt(), $format).' '.Mage::helper('core')->formatTime($this->getUpdatedAt(), $format);
    }

    public function confirmShipping()
    {
        if ($status = Mage::helper('rma')->getStatusByCode(Mirasvit_Rma_Model_Status::PACKAGE_SENT)) {
            $this->setStatusId($status->getId());
            $this->save();
            Mage::helper('rma/process')->notifyRmaChange($this);
        }
    }

    public function getStatusName()
    {
        return Mage::helper('rma/locale')->getLocaleValue($this, 'status_name');
    }

    public function getHasItemsWithResolution($resolutionId)
    {
        $items = Mage::getModel('rma/item')->getCollection()
            ->addFieldToFilter('rma_id', $this->getRmaId())
            ->addFieldToFilter('qty_requested', array('gt' => 0))
            ->addFieldToFilter('main_table.resolution_id', $resolutionId);

        return $items->count() > 0;
    }

    /**
     * Returns block of all FedEx Labels in current RMA.
     *
     * @return array() | false;
     */
    public function getFedExLabels()
    {
        $fedexLabels = array();
        $labels = Mage::getModel('rma/fedex_label')->getCollection()
            ->addFieldToFilter('rma_id', $this->getId());
        foreach ($labels as $label) {
            $trackNumber = $label->getTrackNumber();
            $fedexLabels[] = '<a target="_blank" href="'.Mage::helper('adminhtml')->getUrl('rma/guest/getFedExLabel', array('label_id' => $label->getId())).'">'.
                Mage::helper('rma')->__('Download label (TRK #').
                    substr($trackNumber, 0, 3).' '.substr($trackNumber, 3, 4).' '.substr($trackNumber, 7).')</a>';
        }

        return (count($fedexLabels)) ? '<br>'.implode('<br>', $fedexLabels) : false;
    }

    public function getRmaByGuestId($id)
    {
        return $this->getCollection()
            ->addFieldToFilter('main_table.guest_id', $id)
            ->getFirstItem();
    }
}
