<?php

class Advanced_Cartreminder_Block_Adminhtml_Cartreminder_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'cartreminder';
        $this->_controller = 'adminhtml_cartreminder';
        
        $this->_updateButton('save', 'label', Mage::helper('cartreminder')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('cartreminder')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('cartreminder_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'cartreminder_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'cartreminder_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('cartreminder_data') && Mage::registry('cartreminder_data')->getId() ) {
            return Mage::helper('cartreminder')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('cartreminder_data')->getTitle()));
        } else {
            return Mage::helper('cartreminder')->__('Add Item');
        }
    }
}