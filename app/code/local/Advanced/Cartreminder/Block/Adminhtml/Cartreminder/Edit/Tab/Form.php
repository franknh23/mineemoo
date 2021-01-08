<?php

class Advanced_Cartreminder_Block_Adminhtml_Cartreminder_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('cartreminder_form', array('legend'=>Mage::helper('cartreminder')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('cartreminder')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('cartreminder')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('cartreminder')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('cartreminder')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('cartreminder')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('cartreminder')->__('Content'),
          'title'     => Mage::helper('cartreminder')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getCartreminderData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getCartreminderData());
          Mage::getSingleton('adminhtml/session')->setCartreminderData(null);
      } elseif ( Mage::registry('cartreminder_data') ) {
          $form->setValues(Mage::registry('cartreminder_data')->getData());
      }
      return parent::_prepareForm();
  }
}