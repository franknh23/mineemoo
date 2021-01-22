<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Model_Validation
{
    /**
     * @var float
     */
    protected $_baseGrandTotal;

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->hasSpecificCustomerGroup()
                        && $this->hasMinimumOrderTotal()
                        && $this->hasMaximumOrderTotal();
    }

    /**
     * check customer group
     * @return bool
     */
    public function hasSpecificCustomerGroup()
    {
        if (!Mage::getStoreConfigFlag('payment/xonu_directdebit/allowallgroups')) {
            $allowedGroupIds = explode(',', Mage::getStoreConfig('payment/xonu_directdebit/specificgroup'));
            if (!in_array($this->_getCustomerGroupId(), $allowedGroupIds)) {
                return false;
            }
        }

        return true;
    }

    /**
     * check the required minimum order amount
     * @return bool
     */
    public function hasMinimumOrderTotal()
    {
        $minOrderTotal = (float)Mage::getStoreConfig('payment/xonu_directdebit/min_order_total');
        return (
            $minOrderTotal == 0 || ($minOrderTotal > 0 && $minOrderTotal <= $this->_getOrderTotal())
        );

    }

    /**
     * check the required maximum order amount
     * @return bool
     */
    public function hasMaximumOrderTotal()
    {
        $maxOrderTotal = (float)Mage::getStoreConfig('payment/xonu_directdebit/max_order_total');
        return (
            $maxOrderTotal == 0 || $maxOrderTotal > 0 && $maxOrderTotal >= $this->_getOrderTotal()
        );
    }

    /**
     * returns the rounded base grand total value of the quote
     * @return float
     */
    protected function _getOrderTotal() {
        if(isset($this->_baseGrandTotal)) return $this->_baseGrandTotal;

        return ($this->_baseGrandTotal =
        Mage::getModel('core/store')->roundPrice(
            Mage::getSingleton('checkout/session')->getQuote()->getBaseGrandTotal()
        ));
    }


    /**
     * get current session
     * @return Mage_Adminhtml_Model_Session_Quote | Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            /* @var $session Mage_Adminhtml_Model_Session_Quote */
            $session = Mage::getSingleton('adminhtml/session_quote');
        } else {
            /* @var $session Mage_Customer_Model_Session */
            $session = Mage::getSingleton('customer/session');
        }

        return $session;
    }

    /**
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer()
    {
        return $this->_getSession()->getCustomer();
    }

    /**
     * @return int
     */
    protected function _getCustomerGroupId()
    {
        $customerGroupId = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
        if (Mage::app()->getStore()->isAdmin()) {
            $customerGroupId = $this->_getSession()->getQuote()->getCustomerGroupId();
        } else {
            if ($this->_getSession()->isLoggedIn()) {
                $customerGroupId = $this->_getSession()->getCustomerGroupId();
            }
        }

        return $customerGroupId;
    }
}
