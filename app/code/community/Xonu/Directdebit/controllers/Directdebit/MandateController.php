<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Directdebit_MandateController extends Mage_Adminhtml_Controller_Action {

    protected $_helper;

    /**
     * Acl check for admin
     * @return bool
     */
    protected function _isAllowed()
    {
        $aclPath = 'sales/xonu_directdebit';
        $action = strtolower($this->getRequest()->getActionName());
        switch($action) {
            case 'grid':
            case 'ordergrid':
                $action = 'index';
                break;
            case 'edit':
                $action = 'view';
                break;
            case 'revoke':
                $action = 'view/revoke';
                break;
        }
        if($action != 'index') $aclPath .= '/'.$action;
        return Mage::getSingleton('admin/session')->isAllowed($aclPath);
    }

    protected function _initAction()
    {
        $this->_title($this->__('Sales'))
             ->loadLayout()
             ->_setActiveMenu('sales/xonu_directdebit');

        return $this;
    }

    public function indexAction()
    {
        $title = $this->_helper()->__('SEPA Direct Debit Mandates');

        $this->_initAction()
            ->_addBreadcrumb($title, $title)
            ->_title($title)
        ;

        $this->renderLayout();
    }

    public function editAction() {

        $mandate_id = $this->getRequest()->getParam('mandate_id');
        if(isset($mandate_id))
            $mandate = Mage::getModel('xonu_directdebit/mandate')->load($mandate_id, 'mandate_identifier');
        else {
            $id = $this->getRequest()->getParam('id');
            $mandate = Mage::getModel('xonu_directdebit/mandate')->load($id);
        }


        if($mandate->getMandateIdentifier() != '') {
            Mage::register('xonu_directdebit_mandate', $mandate);

            $subTitle = $this->_helper()->__('SEPA Direct Debit Mandate');
            $title = '#'.$mandate->getMandateIdentifier();

            $this->_initAction()
                ->_addBreadcrumb($subTitle, $subTitle)
                ->_title($subTitle)->_title($title)
            ;

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('xonu_directdebit/adminhtml_mandate_edit'))
                    ->_addLeft($this->getLayout()->createBlock('xonu_directdebit/adminhtml_mandate_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError($this->_helper()->__('Mandate not found'));
            $this->_redirect('*/*/');
        }
    }

    public function revokeAction() {
        $id = $this->getRequest()->getParam('id');
        if (Mage::getSingleton('admin/session')->isAllowed('sales/xonu_directdebit/view/revoke')) {
            $mandate = Mage::getModel('xonu_directdebit/mandate')->load($id);

            Mage::dispatchEvent('xonu_directdebit_mandate_revoke_before', array('mandate' => $mandate));
            $mandate->setRevoked(true);
            $mandate->save();
            Mage::dispatchEvent('xonu_directdebit_mandate_revoke_after', array('mandate' => $mandate));
        }
        $this->_redirect('*/*/edit', array('id' => $id));
    }

    public function gridAction() {
        $this->loadLayout()->renderLayout();
    }

    public function ordergridAction() {
        $mandate_id = $this->getRequest()->getParam('mandate_id');
        if(isset($mandate_id))
            $mandate = Mage::getModel('xonu_directdebit/mandate')->load($mandate_id, 'mandate_identifier');
        else {
            $id = $this->getRequest()->getParam('id');
            $mandate = Mage::getModel('xonu_directdebit/mandate')->load($id);
        }

        if($mandate->getMandateIdentifier() != '') {
            Mage::register('xonu_directdebit_mandate', $mandate);
        }

        $this->loadLayout()->renderLayout();
    }

    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_directdebit');
        return $this->_helper;
    }
}
