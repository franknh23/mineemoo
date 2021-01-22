<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */

class Xonu_Directdebit_Block_Adminhtml_Mandate_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_helper;

	public function __construct(){
		parent::__construct();

		$this->_objectId = 'id';
		$this->_blockGroup = 'xonu_directdebit';
		$this->_controller = 'adminhtml_mandate';

        $this->_removeButton('save');
        $this->_removeButton('delete');
        $this->_removeButton('reset');

        $mandate = Mage::registry('xonu_directdebit_mandate');
        if(!$mandate->getRevoked() && $mandate->getRecurrent()) {
            if (Mage::getSingleton('admin/session')->isAllowed('sales/xonu_directdebit/view/revoke')) {
                $this->_addButton('revoke', array(
                    'label' => $this->_helper()->__('Revoke'),
                    'class'     => 'delete',
                    'onclick'   => 'deleteConfirm(\''. $this->_helper()->__('Are you sure you want to do this?')
                        .'\', \'' . $this->getRevokeUrl() . '\')',
                ));
            }
        }
	}
	
	public function getHeaderText()
    {
        return $this->_helper()
                    ->__("Mandate Identifier: %s", Mage::registry('xonu_directdebit_mandate')->getMandateIdentifier());
	}

    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_directdebit');
        return $this->_helper;
    }

    public function getRevokeUrl()
    {
        return $this->getUrl('*/*/revoke', array($this->_objectId => $this->getRequest()->getParam($this->_objectId)));
    }

}