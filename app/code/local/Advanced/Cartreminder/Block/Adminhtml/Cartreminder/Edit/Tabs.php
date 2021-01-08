<?php

class Advanced_Cartreminder_Block_Adminhtml_Cartreminder_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('cartreminder_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('cartreminder')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('cartreminder')->__('Item Information'),
          'title'     => Mage::helper('cartreminder')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('cartreminder/adminhtml_cartreminder_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}