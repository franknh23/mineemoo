<?php

/**
 * Stabeaddon
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Stabeaddon.com license that is
 * available through the world-wide-web at this URL:
 * http://www.stabeaddon.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Stabeaddon
 * @package     Stabeaddon_Bannerrules
 * @copyright   Copyright (c) 2012 Stabeaddon (http://www.stabeaddon.com/)
 * @license     http://www.stabeaddon.com/license-agreement.html
 */

/**
 * Bannerrules Adminhtml Controller
 * 
 * @category    Stabeaddon
 * @package     Stabeaddon_Bannerrules
 * @author      Stabeaddon Developer
 */
class Advanced_Bannerrules_Adminhtml_BannerrulesController extends Mage_Adminhtml_Controller_Action {

    /**
     * init layout and set active for current menu
     *
     * @return Advanced_Bannerrules_Adminhtml_BannerrulesController
     */
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('bannerrules/bannerrules')
                ->_addBreadcrumb(
                        Mage::helper('adminhtml')->__('Manage Banners'), Mage::helper('adminhtml')->__('Manage Banners')
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
        $bannerrulesId = $this->getRequest()->getParam('id');
        $model = Mage::getModel('bannerrules/bannerrules')->load($bannerrulesId);

        if ($model->getId() || $bannerrulesId == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            $model->getConditions()->setJsFormObject('rule_conditions_fieldset');

            Mage::register('bannerrules_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('bannerrules/bannerrules');


            $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Add Banner'), Mage::helper('adminhtml')->__('Add Banner')
            );

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)->setCanLoadRulesJs(true);
            $this->_addContent($this->getLayout()->createBlock('bannerrules/adminhtml_bannerrules_edit'))
                    ->_addLeft($this->getLayout()->createBlock('bannerrules/adminhtml_bannerrules_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('bannerrules')->__('Item does not exist')
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
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('bannerrules/bannerrules');
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
            }
            if (isset($data['website']) && $data['website']) {
                $data['website'] = implode(",", $data['website']);
            }
            if (isset($data['show_block']) && $data['show_block']) {
                $data['show_block'] = implode(",", $data['show_block']);
            }
            if (isset($data['customer_group']) && $data['customer_group']) {
                $data['customer_group'] = implode(",", $data['customer_group']);
            }

            if (isset($data['rule'])) {
                $rules = $data['rule'];
                if (isset($rules['conditions'])) {
                    $data['conditions'] = $rules['conditions'];
                }
                unset($data['rule']);
            }

            $model->loadPost($data);

            $model->setData($data)
                    ->setId($this->getRequest()->getParam('id'));

            try {

                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('bannerrules')->__('Item was successfully saved')
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
        Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('bannerrules')->__('Unable to find item to save')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete item action
     */
    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('bannerrules/bannerrules');
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
     * mass delete item(s) action
     */
    public function massDeleteAction() {
        $bannerrulesIds = $this->getRequest()->getParam('bannerrules');
        if (!is_array($bannerrulesIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($bannerrulesIds as $bannerrulesId) {
                    $bannerrules = Mage::getModel('bannerrules/bannerrules')->load($bannerrulesId);
                    $bannerrules->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($bannerrulesIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass change status for item(s) action
     */
    public function massStatusAction() {
        $bannerrulesIds = $this->getRequest()->getParam('bannerrules');
        if (!is_array($bannerrulesIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($bannerrulesIds as $bannerrulesId) {
                    Mage::getSingleton('bannerrules/bannerrules')
                            ->load($bannerrulesId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($bannerrulesIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * export grid item to CSV type
     */
    public function exportCsvAction() {
        $fileName = 'bannerrules.csv';
        $content = $this->getLayout()
                ->createBlock('bannerrules/adminhtml_bannerrules_grid')
                ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export grid item to XML type
     */
    public function exportXmlAction() {
        $fileName = 'bannerrules.xml';
        $content = $this->getLayout()
                ->createBlock('bannerrules/adminhtml_bannerrules_grid')
                ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('bannerrules');
    }

}
