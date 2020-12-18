<?php
class Advanced_Delivery_Block_Adminhtml_Deliverydate_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        
        $this->_objectId = 'id';
        $this->_blockGroup = 'delivery';
        $this->_controller = 'adminhtml_deliverydate';
        
        $this->_updateButton('save', 'label', Mage::helper('delivery')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('delivery')->__('Delete'));
   
                $id = $this->getRequest()->getParam('id');
  
        

            $this->_addButton('sendemail', array(
                'label'     => Mage::helper('adminhtml')->__('Send Email'),
                'onclick'   => 'sendemail()',
                'class'     => 'save',
            ), -100);	
        
        

        
        $this->_removeButton('reset');
          $this->_removeButton('save');
     
         $this->_removeButton('delete');
        
                $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('delivery_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'delivery_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'delivery_content');
                }
            }

            function sendemail(){
                editForm.submit($('edit_form').action);
            }
        ";
    }
    public function getHeaderText()
    {
        if (Mage::registry('delivery_data')
            && Mage::registry('delivery_data')->getId()
        ) {
            return Mage::helper('delivery')->__("Edit  Delivery date ",
                                                $this->htmlEscape(Mage::registry('delivery_data')->getTitle())
            );
        }
        return Mage::helper('delivery')->__('Add Delivery');
    }
}