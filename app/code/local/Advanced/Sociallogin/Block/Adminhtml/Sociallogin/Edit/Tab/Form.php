<?php

class Advanced_Sociallogin_Block_Adminhtml_Sociallogin_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('sociallogin_form', array('legend'=>Mage::helper('sociallogin')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('sociallogin')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('sociallogin')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('sociallogin')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('sociallogin')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('sociallogin')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('sociallogin')->__('Content'),
          'title'     => Mage::helper('sociallogin')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getSocialloginData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getSocialloginData());
          Mage::getSingleton('adminhtml/session')->setSocialloginData(null);
      } elseif ( Mage::registry('sociallogin_data') ) {
          $form->setValues(Mage::registry('sociallogin_data')->getData());
      }
      return parent::_prepareForm();
  }
}