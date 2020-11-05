<?php

class Stork_Shipcloud_Block_Adminhtml_Shipcloud_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'shipcloud';
        $this->_controller = 'adminhtml_shipcloud';

        $this->_updateButton('save', 'label', Mage::helper('shipcloud')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('shipcloud')->__('Delete Item'));

        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('shipcloud_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'shipcloud_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'shipcloud_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('shipcloud_data') && Mage::registry('shipcloud_data')->getId() ) {
            return Mage::helper('shipcloud')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('shipcloud_data')->getTitle()));
        } else {
            return Mage::helper('shipcloud')->__('Add Item');
        }
    }
}
