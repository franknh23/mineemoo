<?php
class Advanced_Delivery_Adminhtml_IntervalsController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('delivery/intervals')
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
     * view and edit item action
     */
    public function editAction() {
        $deliveryId = $this->getRequest()->getParam('id');
        $model = Mage::getModel('delivery/intervals')->load($deliveryId);

        if ($model->getId() || $deliveryId == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('delivery_data', $model);

      
            $this->loadLayout();
            $this->_setActiveMenu('delivery/intervals');
      
            $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager')
            );
            $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News')
            );

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('delivery/adminhtml_intervals_edit'))
                    ->_addLeft($this->getLayout()->createBlock('delivery/adminhtml_intervals_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('delivery')->__('Item does not exist')
            );
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    /**
     * save item action
     */
    public function saveAction() {
        

        $allStores = Mage::app()->getStores(); 
        for($i = 0; $i<=count($allStores) ; $i++ )
        {
         $a[] = $i;
        }
          $as= implode(',', $a);
        if ($data = $this->getRequest()->getPost()) {
            if (isset($data['stores'])) {
                if (in_array('0', $data['stores'])) {
                    $data['store_id'] = $as;
                } else {
                    $data['store_id'] = implode(",", $data['stores']);
                }
                unset($data['stores']);
            }
            
              if ($data['hourstart']) {
                $data['hourstart'] = implode(':', $data['hourstart']);
            }
            if ($data['hourto']) {
                $data['hourto'] = implode(':', $data['hourto']);
            }
            
            $model = Mage::getModel('delivery/intervals');
            $model->setData($data)
                    ->setId($this->getRequest()->getParam('id'));
            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('delivery')->__('Staff was successfully saved')
                );
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
    }

    /**
     * delete item action
     */
   public function massDeleteAction() {
        $deliveryIds = $this->getRequest()->getParam('intervals');
        if (!is_array($deliveryIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($deliveryIds as $deliveryId) {
                    $delivery = Mage::getModel('delivery/intervals')->load($deliveryId);
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
        $deliveryIds = $this->getRequest()->getParam('intervals');
        if (!is_array($deliveryIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($deliveryIds as $deliveryId) {
                    Mage::getSingleton('delivery/intervals')
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
        return Mage::getSingleton('admin/session')->isAllowed('advanced/delivery/intervals');
    }

}
