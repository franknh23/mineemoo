<?php

class Advanced_Sociallogin_Block_Adminhtml_Sociallogin_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'sociallogin';
        $this->_controller = 'adminhtml_sociallogin';
        
        $this->_updateButton('save', 'label', Mage::helper('sociallogin')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('sociallogin')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('sociallogin_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'sociallogin_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'sociallogin_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('sociallogin_data') && Mage::registry('sociallogin_data')->getId() ) {
            return Mage::helper('sociallogin')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('sociallogin_data')->getTitle()));
        } else {
            return Mage::helper('sociallogin')->__('Add Item');
        }
    }
}