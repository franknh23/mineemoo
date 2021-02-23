<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */

class Xonu_Sepaone_Block_Adminhtml_Export_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_helper;

	public function __construct(){
		parent::__construct();

		$this->_objectId = 'id';
		$this->_blockGroup = 'xonu_sepaone';
		$this->_controller = 'adminhtml_export';

        $this->_removeButton('save');
        $this->_removeButton('delete');
        $this->_removeButton('reset');
        $this->_removeButton('back');
	}
	
	public function getHeaderText()
    {
        $collection = Mage::getResourceModel('xonu_sepaone/history_collection')
            ->addFieldToFilter('count_transactions', array('gt' => 0))
            ->setOrder('started_at', 'DESC')
        ;
        $history = $collection->getFirstItem();
        if($history->getStartedAt() != '') {
            $datetime = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(strtotime($history->getStartedAt())));
        } else {
            $datetime = '';
        }

        return $title = sprintf($this->_helper()->__('Last Export: %s'), $datetime);
	}

    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_sepaone');
        return $this->_helper;
    }
}