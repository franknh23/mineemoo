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
 * Public form for enter to RMA as guest.
 *
 * Class Mirasvit_Rma_Rma_GuestController
 */
class Mirasvit_Rma_GuestController extends Mage_Core_Controller_Front_Action
{
    /**
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     */
    public function guestAction()
    {
        $session = $this->_getSession();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($customer->getId()) {
            $this->_redirectUrl(Mage::helper('rma/url')->getNewRmaUrl());

            return;
        }
        try {
            $order = $this->_initOrder();
            if ($order) {
                // Check for return eligibility
                if (!Mage::helper('rma')->isReturnAllowed($order)) {
                    throw new Mage_Core_Exception(
                        Mage::helper('rma')->__(
                            'This order were placed more than %s days ago. Please, contact customer service.',
                            Mage::helper('rma')->getReturnPeriod()
                        )
                    );
                }
                $this->_getSession()->setRmaGuestOrderId($order->getId());
                $this->_getSession()->setRmaGuestEmail($order->getCustomerEmail());
                $this->_redirectUrl(Mage::helper('rma/url')->getGuestRmaListUrl());

                return;
            } elseif (Mage::app()->getRequest()->getParam('order_increment_id')) {
                throw new Mage_Core_Exception(Mage::helper('rma')->__('Wrong Order #, Email or Last Name'));
            }
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
        }

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     * List guest Rmas.
     */
    public function listAction()
    {
        $orderId = $this->_getSession()->getRmaGuestOrderId();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if (!$orderId && !$customer->getId()) {
            $this->_redirectUrl(Mage::helper('rma/url')->getGuestRmaUrl());

            return;
        }
        if ($customer->getId()) {
            $this->_redirectUrl(Mage::helper('rma/url')->getRmaListUrl());

            return;
        }
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        Mage::helper('rma/order')->registerGuestData($customer, $this->_getSession()->getData());

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     */
    public function viewAction()
    {
        if ($rma = $this->_initRma()) {
            $this->markAsRead($rma);
            $this->loadLayout();
            $this->_initLayoutMessages('customer/session');
            $this->renderLayout();
        } else {
            $this->_forward('no_rote');
        }
    }

    /**
     * @param Mirasvit_Rma_Model_Rma $rma
     */
    protected function markAsRead($rma)
    {
        if ($comment = $rma->getLastComment()) {
            $comment->setIsRead(true)->save();
        }
    }

    /**
     * Save comment.
     */
    public function savecommentAction()
    {
        $session = $this->_getSession();
        if (!($rma = $this->_initRma())) {
            $this->_redirectUrl($this->getRmaListUrl());

            return;
        }
        try {
            $isConfirmShipping = $this->getRequest()->getParam('shipping_confirmation');
            /// we need to confirm shipping BEFORE posting comment
            /// (comment can be from custom variables value in the shipping confirmation dialog)
            if ($isConfirmShipping) {
                $rma->confirmShipping();
                $session->addSuccess(Mage::helper('rma')->__('Shipping is confirmed. Thank you!'));
            }
            Mage::helper('rma/process')->createCommentFromPost($rma, $this->getRequest()->getParams());

            if (!$isConfirmShipping) {
                $session->addSuccess($this->__('Your comment was successfuly added'));
            }
            $this->_redirectUrl($this->getRmaViewUrl($rma));
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
            $this->_redirectUrl($this->getRmaViewUrl($rma));
        }
    }

    /**
     * @depricated
     */
    public function printAction()
    {
        if (!$rma = $this->_initRma()) {
            return;
        }
        $this->loadLayout('print');
        $this->renderLayout();
    }

    /**
     */
    public function printlabelAction()
    {
        if (!$rma = $this->_initRma()) {
            return;
        }

        if ($label = $rma->getReturnLabel()) {
            $this->_redirectUrl($label->getUrl());
        } else {
            $this->_forward('no_rote');
        }
    }

    /**
     * @return Mirasvit_Rma_Model_Rma|null
     */
    protected function _initRma()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $rma = Mage::getModel('rma/rma')->getRmaByGuestId($id);

            if ($rma->getId() > 0) {
                Mage::register('current_rma', $rma);

                return $rma;
            }
        }

        return;
    }

    /**
     * @param Mirasvit_Rma_Model_Rma $rma
     *
     * @return string
     */
    protected function getRmaViewUrl($rma)
    {
        return Mage::helper('rma/url')->getGuestRmaViewUrl(array('id' => $rma->getGuestId()));
    }

    /**
     * @return string
     */
    protected function getRmaListUrl()
    {
        return Mage::helper('rma/url')->getGuestRmaListUrl();
    }

    /**
     * @return false|Mage_Sales_Model_Order
     */
    protected function _initOrder()
    {
        if (($orderId = Mage::app()->getRequest()->getParam('order_increment_id')) &&
            ($email = Mage::app()->getRequest()->getParam('email'))) {
            $orderId = trim($orderId);
            $orderId = str_replace('#', '', $orderId);
            $collection = Mage::getModel('sales/order')->getCollection()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('increment_id', $orderId);
            if ($collection->count()) {
                $order = $collection->getFirstItem();
                $email = trim(strtolower($email));
                if ($email != strtolower($order->getCustomerEmail())
                    && $email != strtolower($order->getCustomerLastname())) {
                    return false;
                }

                return $order;
            }
        }
    }
}
