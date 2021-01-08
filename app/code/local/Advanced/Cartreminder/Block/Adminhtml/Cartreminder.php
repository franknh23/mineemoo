<?php
class Advanced_Cartreminder_Block_Adminhtml_Cartreminder extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_cartreminder';
    $this->_blockGroup = 'cartreminder';
    $this->_headerText = Mage::helper('cartreminder')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('cartreminder')->__('Add Item');
    parent::__construct();
  }
}