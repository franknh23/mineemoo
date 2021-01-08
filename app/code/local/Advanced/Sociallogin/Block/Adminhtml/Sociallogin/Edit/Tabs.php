<?php

class Advanced_Sociallogin_Block_Adminhtml_Sociallogin_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('sociallogin_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('sociallogin')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('sociallogin')->__('Item Information'),
          'title'     => Mage::helper('sociallogin')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('sociallogin/adminhtml_sociallogin_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}