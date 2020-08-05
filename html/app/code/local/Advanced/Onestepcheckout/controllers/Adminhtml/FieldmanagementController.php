<?php

/**
 * Advanced Checkout
 * 
 * NOTICE OF LICENSE 
 * 
 * This source file is subject to the Onestepcheckout.com license that is
 * available through the world-wide-web at this URL:
 * http://www.advancedcheckout.com/license-agreement
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category 	Advanced Checkout
 * @package 	Advanced_Onestepcheckout
 * @copyright 	Copyright (c) 2015 Advanced Checkout (http://www.advancedcheckout.com/)
 * @license 	http://www.advancedcheckout.com/license-agreement
 */

/**
 * Onestepcheckout Controller
 * 
 * @category 	Onestepcheckout
 * @package 	Advanced_Onestepcheckout
 * @author  	Onestepcheckout Developer
 */
class Advanced_Onestepcheckout_Adminhtml_FieldmanagementController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {
        $this->loadLayout()
                ->_title($this->__('Field Management'));
        $this->renderLayout();
    }

    public function saveAction() {
        $storecode = Mage::app()->getRequest()->getParam('store');
        $website = Mage::app()->getRequest()->getParam('website');
        $helper = Mage::helper('onestepcheckout');

       

        $_fields = array();
        $scopeId = 0;
        $scope = 'default';
        
        $post = Mage::app()->getRequest()->getParams();

        $useDefault = 1;
        $inherit = 0;
        
        if (isset($post['groups']['position']['fields']["manage"]['inherit']))
            $inherit = $post['groups']['position']['fields']["manage"]['inherit'];
        if ($inherit == 0) {
            $useDefault = 0;
        }
        
       
        
        if ($storecode) {
            $scope = 'stores';
            $scopeId = (int) Mage::getConfig()->getNode('stores/' . $storecode . '/system/store/id');

            $checkFieldExit = Mage::getModel('onestepcheckout/fieldsposition')->getCollection()
                            ->addFieldToFilter('scope', $scope)
                            ->addFieldToFilter('scope_id', $scopeId)->getFirstItem();

            if ($checkFieldExit->getId()) {
                $fields = $helper->getFieldData($scope, $scopeId);
            } else {
                $checkFieldExit = Mage::getModel('onestepcheckout/fieldsposition')->getCollection()
                                ->addFieldToFilter('scope', 'websites')
                                ->addFieldToFilter('scope_id', (int) Mage::getConfig()->getNode('websites/' . $website . '/system/website/id'))->getFirstItem();
                if ($checkFieldExit->getId()) {
                    $fields = $helper->getFieldData('websites', (int) Mage::getConfig()->getNode('websites/' . $website . '/system/website/id'));
                } else {
                    $fields = $helper->getFieldData('default', 0);
                }
            }
        } elseif ($website) {
            $scope = 'websites';
            $scopeId = (int) Mage::getConfig()->getNode('websites/' . $website . '/system/website/id');
            $checkFieldExit = Mage::getModel('onestepcheckout/fieldsposition')->getCollection()
                            ->addFieldToFilter('scope', $scope)
                            ->addFieldToFilter('scope_id', $scopeId)->getFirstItem();
            if ($checkFieldExit->getId()) {
                $fields = $helper->getFieldData($scope, $scopeId);
            } else {
                $fields = $helper->getFieldData('default', 0);
            }
        } else {
            $scope = 'default';
            $scopeId = 0;
            $fields = $helper->getFieldData($scope, $scopeId);
        }
        

        $post = Mage::app()->getRequest()->getPost();

        $fields = $post['field'];
        
        $oldFields = Mage::getModel('onestepcheckout/fieldsposition')->getCollection()
                ->addFieldToFilter('scope', $scope)
                ->addFieldToFilter('scope_id', $scopeId);
        foreach ($oldFields as $oldField) {
            $oldField->delete();
        }
       
        foreach ($fields as $id => $value) {
            if ($inherit == 0) {
                $model = Mage::getModel('onestepcheckout/fieldsposition')
                        ->setData('scope', $scope)
                        ->setData('scope_id', $scopeId)
                        ->setData('width', $value['width'])
                        ->setData('path', $id)
                        ->setData('remove', $value['remove'])
                        ->setData('position', $value['position'])
                        ->setData('required', $value['required'])
                        ->setData('use_default', $useDefault);
                $model->save();
            }else{
                $oldPositions = Mage::getModel('onestepcheckout/fieldsposition')->getCollection()
                        ->addFieldToFilter('scope', $scope)
                        ->addFieldToFilter('scope_id', $scopeId)
                        ->addFieldToFilter('path', $id);
                foreach ($oldPositions as $oldPosition)
                    $oldPosition->delete();
            }
        }
    }

    public function testdataAction() {
        $_websites = Mage::getModel('core/website')->getCollection();
        $_stores = Mage::getModel('core/store')->getCollection();

        $oldConfigs = Mage::getModel('onestepcheckout/fieldsposition')->getCollection();
        foreach ($oldConfigs as $oldConfig) {
            $oldConfig->delete();
        }

        $fields = array(
            'firstname' => array(0, '', 1),
            'lastname' => array(0, '', 1),
            'email' => array(0, '', 1),
            'company' => array(0, '', 0),
            'street' => array(1, '', 1),
            'country' => array(0, '', 1),
            'city' => array(0, '', 0),
            'region' => array(1, '', 1),
            'postcode' => array(1, '', 0),
            'telephone' => array(0, '', 0),
            'fax' => array(0, '', 0),
            'birthday' => array(1, 'remove', 0),
            'gender' => array(1, 'remove', 0),
            'taxvat' => array(1, 'remove', 0),
            'prefix_name' => array(1, 'remove', 0),
            'middlename' => array(1, 'remove', 0),
            'suffix' => array(1, 'remove', 0),
        );
        $postion = 0;
        $right = 1;
        foreach ($fields as $id => $value) {
            $fieldsModel = Mage::getModel('onestepcheckout/fieldsposition');
            $fieldsModel->setData('scope', 'default')
                    ->setData('scope_id', 0)
                    ->setData('path', $id)
                    ->setData('remove', $value[1])
                    ->setData('use_default', 1)
                    ->setData('required', $value[2]);

            $fieldsModel->setData('width', 0);
            $fieldsModel->setData('position', $postion);
            $postion++;

            try {
                $fieldsModel->save();
            } catch (Exception $e) {
                
            }
        }
    }
    
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('advanced/fieldmanagement');
    }

}
