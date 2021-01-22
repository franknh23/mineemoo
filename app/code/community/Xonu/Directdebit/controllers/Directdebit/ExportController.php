<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Directdebit_ExportController extends Mage_Adminhtml_Controller_Action {

    protected $_helper;

    /**
     * Acl check for admin
     * @return bool
     */
    protected function _isAllowed()
    {
        $aclPath = 'admin/system/convert/xonu_directdebit';
        $action = strtolower($this->getRequest()->getActionName());
        switch($action) {
            case 'historygrid':
            case 'exportgrid':
                $action = 'index';
                break;
            case 'xml':
            case 'csv':
            case 'dta':
            case 'massflag':
                $action = 'export';
                break;
        }
        if($action != 'index') $aclPath .= '/'.$action;
        return Mage::getSingleton('admin/session')->isAllowed($aclPath);
    }

    protected function _initAction()
    {
        $this->_title($this->__('Import/Export'))
             ->loadLayout()
             ->_setActiveMenu('system/convert/xonu_directdebit');

        return $this;
    }

    public function indexAction()
    {
        $title = $this->_helper()->__('SEPA Direct Debit Payments');

        $this->_initAction()
             ->_setActiveMenu('system/convert/xonu_directdebit')
             ->_addBreadcrumb($title, $title)
             ->_title($title)
        ;

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('xonu_directdebit/adminhtml_export_edit'))
                ->_addLeft($this->getLayout()->createBlock('xonu_directdebit/adminhtml_export_edit_tabs'));

        $this->renderLayout();
    }

    public function historygridAction() {
        $this->loadLayout()->renderLayout();
    }

    public function exportgridAction() {
        $this->loadLayout()->renderLayout();
    }

    public function massFlagAction() {
        $orderIds = (array)$this->getRequest()->getParam($this->getRequest()->getParam('massaction_prepare_key'));
        $exported = (int)$this->getRequest()->getParam('exported');

        $collection = Mage::getResourceModel('xonu_directdebit/export_collection')
            ->addFieldToFilter('order_id', array('in' => $orderIds));

        foreach($collection as $orderExport) {
            $orderExport->setExported($exported);
        }
        $collection->save();

        $this->_redirect('*/*/');
    }

    protected function emptyExportInfo() {
        Mage::getSingleton('adminhtml/session')->addNotice(
            $this->_helper()->__('All payments have been already exported.')
        );
    }

    public function csvAction() {
        $response = Mage::getModel('xonu_directdebit/export')->export('csv');
        if(!$response) {
            $this->emptyExportInfo();
            $this->_redirect('*/*/');
        } elseif($response['error']) {
            $this->_redirect('*/*/');
        }
        else $this->_prepareDownloadResponse($response['filename'], $response['data']);
    }

    public function dtaAction() {
        $response = Mage::getModel('xonu_directdebit/export')->export('dta');
        if(!$response) {
            $this->emptyExportInfo();
            $this->_redirect('*/*/');
        } elseif($response['error']) {
            $this->_redirect('*/*/');
        }
        else $this->_prepareDownloadResponse($response['filename'], $response['data']);
    }

    public function xmlAction() {
        $response = Mage::getModel('xonu_directdebit/export')->export('xml');
        if(!$response) {
            $this->emptyExportInfo();
            $this->_redirect('*/*/');
        } elseif($response['error']) {
            $this->_redirect('*/*/');
        }
        else $this->_prepareDownloadResponse($response['filename'], $response['data']);
    }

    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_directdebit');
        return $this->_helper;
    }

}