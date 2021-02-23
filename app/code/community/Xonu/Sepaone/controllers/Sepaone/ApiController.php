<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Sepaone_Sepaone_ApiController extends Mage_Adminhtml_Controller_Action {

    protected $_helper;

    /**
     * Acl check for admin
     * @return bool
     */
    protected function _isAllowed()
    {
        $aclPath = 'admin/system/convert/xonu_sepaone';
        $action = strtolower($this->getRequest()->getActionName());
        switch($action) {
            case 'gridhistory':
            case 'gridexport':
            case 'gridlog':
                $action = 'index';
                break;
            case 'massflag':
                $action = 'export';
                break;
        }
        if($action != 'index') $aclPath .= '/'.$action;
        return Mage::getSingleton('admin/session')->isAllowed($aclPath);
    }

    protected function _initAction()
    {
        $this->_title($this->__('Web Services'))
            ->loadLayout()
            ->_setActiveMenu('system/api/xonu_sepaone');

        return $this;
    }

    public function indexAction()
    {
        $title = $this->_helper()->__('SEPAone');

        $this->_initAction()
            ->_setActiveMenu('system/api/xonu_sepaone')
            ->_addBreadcrumb($title, $title)
            ->_title($title)
        ;

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('xonu_sepaone/adminhtml_export_edit'))
             ->_addLeft($this->getLayout()->createBlock('xonu_sepaone/adminhtml_export_edit_tabs'));

        $this->renderLayout();
    }

    public function viewlogAction()
    {
        $title = $this->_helper()->__('SEPAone');

        $this->_initAction()
            ->_setActiveMenu('system/api/xonu_sepaone')
            ->_addBreadcrumb($title, $title)
            ->_title($title)
        ;

        $this->renderLayout();
    }

    public function gridhistoryAction() {
        $this->loadLayout()->renderLayout();
    }

    public function gridexportAction() {
        $this->loadLayout()->renderLayout();
    }

    public function gridlogAction() {
        $this->loadLayout()->renderLayout();
    }

    public function massFlagAction() {
        $orderIds = (array)$this->getRequest()->getParam($this->getRequest()->getParam('massaction_prepare_key'));
        $exported = (int)$this->getRequest()->getParam('exported');

        $collection = Mage::getResourceModel('xonu_sepaone/export_collection')
            ->addFieldToFilter('order_id', array('in' => $orderIds));

        foreach($collection as $orderExport) {
            $orderExport->setExported($exported);
        }
        $collection->save();

        $this->_redirect('*/*/');
    }

    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_sepaone');
        return $this->_helper;
    }

}