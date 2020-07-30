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
class Advanced_Onestepcheckout_Adminhtml_SurveyreportController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {
        $this->loadLayout()
            ->_setActiveMenu('surveyreport')
            ->_title($this->__('Survey Reports'));
        // my stuff
        $this->renderLayout();
    }
    
    public function massDeleteAction() {
        $deliveryIds = $this->getRequest()->getParam('survey');
        if (!is_array($deliveryIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($deliveryIds as $deliveryId) {
                    $delivery = Mage::getModel('onestepcheckout/survey')->load($deliveryId);
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
     * export grid item to CSV type
     */
    public function exportCsvAction() {
        $fileName = 'surveyreport.csv';
        $content = $this->getLayout()
                ->createBlock('onestepcheckout/adminhtml_surveyreport_grid')
                ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export grid item to XML type
     */
    public function exportXmlAction() {
        $fileName = 'surveyreport.xml';
        $content = $this->getLayout()
                ->createBlock('onestepcheckout/adminhtml_surveyreport_grid')
                ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }
    
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('advanced/survey');
    }

}
