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



require_once Mage::getModuleDir('controllers', 'Mirasvit_Rma').DS.'GuestController.php';

class Mirasvit_Rma_RmaController extends Mirasvit_Rma_GuestController
{
    /**
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    /**
     */
    public function indexAction()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if (!$customer->getId()) {
            $this->_redirect('rma/rma/new');

            return;
        }
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     */
    public function orderAction()
    {
        if ($orderId = $this->getRequest()->getParam('order_id')) {
            $order = Mage::getModel('sales/order')->load($orderId);
            $customer = $this->_getSession()->getCustomer();
            if ($order->getCustomerId() == $customer->getId()) {
                Mage::register('current_order', $order);
                $this->loadLayout();
                $this->_initLayoutMessages('customer/session');
                $this->renderLayout();

                return;
            }
        }
        $this->norouteAction();
    }

    /**
     * @return Mirasvit_Rma_Model_Rma|null
     */
    protected function _initRma()
    {
        $rma = parent::_initRma();
        if ((!$rma || !$rma->getId()) && ($id = $this->getRequest()->getParam('id'))) {
            $rma = Mage::getModel('rma/rma')->load($id);

            if ($rma->getId() > 0) {
                Mage::register('current_rma', $rma);

                return $rma;
            }
        }

        return $rma;
    }

    /**
     * @param Mirasvit_Rma_Model_Rma $rma
     *
     * @return string
     */
    protected function getRmaViewUrl($rma)
    {
        return Mage::helper('rma/url')->getRmaViewUrl($rma->getId());
    }

    /**
     * @return string
     */
    protected function getRmaListUrl()
    {
        return Mage::helper('rma/url')->getRmaListUrl();
    }
}
