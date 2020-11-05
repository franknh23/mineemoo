<?php

class Stork_Shipcloud_Block_Adminhtml_Shipcloud_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('shipcloud_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('shipcloud')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('shipcloud')->__('Item Information'),
          'title'     => Mage::helper('shipcloud')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('shipcloud/adminhtml_shipcloud_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}