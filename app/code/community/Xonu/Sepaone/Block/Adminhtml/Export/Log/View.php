<?php

class Xonu_Sepaone_Block_Adminhtml_Export_Log_View extends Mage_Adminhtml_Block_Widget_Form_Container {

    protected $_helper;

    public function __construct(){
        parent::__construct();

        $this->_removeButton('save');
        $this->_removeButton('delete');
        $this->_removeButton('reset');
        $this->_removeButton('back');

        $this->_addButton(
            'close',
            array(
                'label'     => Mage::helper('catalog')->__('Close Window'),
                'class'     => 'cancel',
                'onclick'   => 'window.close()',
                'level'     => -1
            )
        );
    }

    public function getHeaderText()
    {
        $id = $this->getRequest()->getParam('id');
        return $this->_helper->__('Log Data (ID: %s)', $id);
    }

    public function getLog() {
        $id = $this->getRequest()->getParam('id');
        $log = Mage::getModel('xonu_sepaone/log')->load($id);
        return $log;
    }

    public function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_sepaone');
        return $this->_helper;
    }

}