<?php

class Advanced_Delivery_Adminhtml_DeliverydateController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('delivery/deliverydate')
                ->_addBreadcrumb(
                        Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager')
        );
        return $this;
    }

    /**
     * index action
     */
    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    /**
     * delete action
     */
    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('delivery/deliverydate');
                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('Item was successfully deleted')
                );
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     *  edit action
     */
    public function editAction() {
        $deliveryId = $this->getRequest()->getParam('id');
        $model = Mage::getModel('delivery/deliverydate')->load($deliveryId);

        if ($model->getId() || $deliveryId == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('delivery_data', $model);
            $this->loadLayout();
            $this->_setActiveMenu('delivery/deliverydate');
            $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager')
            );
            $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News')
            );

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('delivery/adminhtml_deliverydate_edit'))
                    ->_addLeft($this->getLayout()->createBlock('delivery/adminhtml_deliverydate_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('delivery')->__('Item does not exist')
            );
            $this->_redirect('*/*/');
        }
    }

    /**
     *  new action
     */
    public function newAction() {
        $this->_forward('edit');
    }

    /**
     *  sent mail action
     */
    public function sendemailAction() {
        if ($data = $this->getRequest()->getPost()) {
            try {
                $deliveryId = $this->getRequest()->getParam('id');
                $model = Mage::getModel('delivery/deliverydate')->load($deliveryId);
                
                $model->setStatus(1)
                      ->save();
                $id = $model->getData('order_id');
                $customerEmail = Mage::getModel('sales/order')->load($id)->getCustomerEmail();
                $customerName = Mage::getModel('sales/order')->load($id)->getCustomerName();
                $this->sendReminderEmail($customerEmail, $customerName, $data['subject'], $data['contents'],$data['delivery_date'],$data['increment_id']);
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('delivery')->__('Email was sent'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('delivery')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    protected function sendReminderEmail($customerEmail, $customerName, $subject, $content ,$delivery_date,$increment_id) {
        $templateId = Mage::getStoreConfig('delivery/general/email');
        $mailTemplate = Mage::getModel('core/email_template');
        $translate = Mage::getSingleton('core/translate');
        $emailType = Mage::getStoreConfig('delivery/general/sender_email');
        $from_email = Mage::getStoreConfig('trans_email/ident_' . $emailType . '/email');
        $from_name = Mage::getStoreConfig('trans_email/ident_' . $emailType . '/name');
        $sender = array('email' => $from_email, 'name' => $from_name);
        $receipientEmail = $customerEmail;
        $receipientName = $customerName;
        $mailTemplate
                ->setTemplateSubject($subject)
                ->sendTransactional(
                        $templateId, $sender, $receipientEmail, $receipientName, array(
                            'customer_name' => $receipientName,
                            'content' => $content,
                            'delivery_date' => $delivery_date,
                            'increment_id' =>$increment_id,
                    
                        )
        );
        $translate->setTranslateInline(true);
    }
    
  
   public function massDeleteAction() {
        $deliveryIds = $this->getRequest()->getParam('deliverydate');
        if (!is_array($deliveryIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($deliveryIds as $deliveryId) {
                    $delivery = Mage::getModel('delivery/deliverydate')->load($deliveryId);
                    $delivery->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($deliveryIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    /**
     * mass delete item(s) action
     */
   public function massStatusAction() {
        $deliveryIds = $this->getRequest()->getParam('deliverydate');
        if (!is_array($deliveryIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($deliveryIds as $deliveryId) {
                    Mage::getSingleton('delivery/deliverydate')
                            ->load($deliveryId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($deliveryIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
        
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('advanced/delivery/deliverydate');
    }

}
