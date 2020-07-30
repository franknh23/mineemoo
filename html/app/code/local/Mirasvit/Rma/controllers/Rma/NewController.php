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



class Mirasvit_Rma_Rma_NewController extends Mage_Core_Controller_Front_Action
{
    /**
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Step 1.
     */
    public function step1Action()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        Mage::helper('rma/order')->registerGuestData($customer, $this->_getSession()->getData());

        $this->render();
    }

    /**
     * Step 2.
     */
    public function step2Action()
    {
        if (!$this->getRequest()->getPost()) {
            $this->_redirect('*/*/step1');
            return;
        }
        $this->render();
    }

    /**
     * Submit.
     */
    public function submitAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_redirectReferer();

            return;
        }
        $session = $this->_getSession();

        $formUid = $this->getRequest()->getParam('form_uid');
        if ($formUid == $session->getLastFormUid()) { //simple protection from double posting. #RMA-90
            $this->_redirectReferer();

            return;
        }
        $session->setLastFormUid($formUid);

        $customer = $session->getCustomer();
        $data = $this->getRequest()->getParams();

        $items = $data['items'];
        unset($data['items']);

        try {
            $rma = Mage::helper('rma/process')->createRmaFromPost($data, $items, $customer);
            if (Mage::getSingleton('rma/config')->getGeneralIsAdditionalStepAllowed()) {
                $this->_redirect('*/*/step3', array('id' => $rma->getId()));
            } else {
                $this->_redirect('*/*/success', array('id' => $rma->getId()));
            }
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
            $session->setFormData($data);
            if ($this->getRequest()->getParam('id')) {
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } else {
                $this->_redirect('*/*/add', array('order_id' => $this->getRequest()->getParam('order_id')));
            }
        }
    }

    /**
     * Step 3.
     */
    public function step3Action()
    {
        if (!$this->_initRma()) {
            $this->_redirect('*/*/step1');

            return;
        }
        $this->render();
    }

    /**
     * Success.
     */
    public function successAction()
    {
        if (!$this->_initRma()) {
            $this->_redirect('*/*/step1');

            return;
        }
        $this->render();
    }

    /**
     * @return Mirasvit_Rma_Model_Rma
     */
    protected function _initRma()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $customer = $this->_getSession()->getCustomer();
            $rma = Mage::getModel('rma/rma')->getRmaByGuestId($id);
            if (!$rma->getId() && ($customer->getId() || $this->_getSession()->getRmaGuestEmail())) {
                $rma = Mage::getModel('rma/rma')->load($id);
            }

            if ($rma->getId() > 0) {
                Mage::register('current_rma', $rma);

                return $rma;
            }
        }
    }

    /**
     * Mage_Cms_Helper_Page->_renderPage().
     *
     * @return bool
     */
    private function render()
    {
        if ($this->_getSession()->getRmaGuestEmail()) {
            $this->setTwoColumnsRightLayout();
        } else {
            $this->setCustomerAccountLayout();
        }

        $this->addActionLayoutHandles();

        $this->loadLayoutUpdates();
        $layoutUpdate = 'page_two_columns_right';
        $this->getLayout()->getUpdate()->addUpdate($layoutUpdate);
        $this->generateLayoutXml()->generateLayoutBlocks();

        /* @TODO: Move catalog and checkout storage types to appropriate modules */
        $messageBlock = $this->getLayout()->getMessagesBlock();

        $storageType = 'customer/session';
        $storage = Mage::getSingleton($storageType);
        if ($storage) {
            $messageBlock->addStorageType($storageType);
            $messageBlock->addMessages($storage->getMessages(true));
        }

        $this->renderLayout();

        return true;
    }

    /**
     * Set page_two_columns_right layout.
     */
    private function setTwoColumnsRightLayout()
    {
        $this->getLayout()->getUpdate()
            ->addHandle('default')
            ->addHandle('page_two_columns_right');
    }

    /**
     * Set customer_account layout.
     */
    private function setCustomerAccountLayout()
    {
        $this->getLayout()->getUpdate()
            ->addHandle('default')
            ->addHandle('customer_account');
    }
}
