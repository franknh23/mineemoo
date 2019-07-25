<?php
/**
 * Advanced
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the AdvancedCheckout.com license that is
 * available through the world-wide-web at this URL:
 * http://www.AdvancedCheckout.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Advanced
 * @package     Advanced_Delivery
 * @copyright   Copyright (c) 2012 Advanced (http://www.AdvancedCheckout.com/)
 * @license     http://www.AdvancedCheckout.com/license-agreement.html
 */

/**
 * Delivery Edit Block
 * 
 * @category     Advanced
 * @package     Advanced_Delivery
 * @author      Advanced Developer
 */
class Advanced_Delivery_Block_Adminhtml_Delivery_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        
        $this->_objectId = 'id';
        $this->_blockGroup = 'delivery';
        $this->_controller = 'adminhtml_delivery';
        
        $this->_updateButton('save', 'label', Mage::helper('delivery')->__('Save Holiday'));
        $this->_updateButton('delete', 'label', Mage::helper('delivery')->__('Delete Holiday'));
        
        $this->_addButton('saveandcontinue', array(
            'label'        => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'    => 'saveAndContinueEdit()',
            'class'        => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('delivery_content') == null)
                    tinyMCE.execCommand('mceAddControl', false, 'delivery_content');
                else
                    tinyMCE.execCommand('mceRemoveControl', false, 'delivery_content');
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
                 Event.observe('dateto','change',function(){
                            if(!$('datefrom').value){
                                    alert('" . $this->__('You need to insert From Date') . "');
                                    $('dateto').value='';
                                }
                            if($('datefrom').value && ($('datefrom').value>$('dateto').value)){
                                alert('" . $this->__('Invalid Date') . "');
                                    $('dateto').value='';
                            }
                            });
        ";
    }
    
    /**
     * get text to show in header when edit an item
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('delivery_data')
            && Mage::registry('delivery_data')->getId()
        ) {
            return Mage::helper('delivery')->__("Edit Holiday ",
                                                $this->htmlEscape(Mage::registry('delivery_data')->getTitle())
            );
        }
        return Mage::helper('delivery')->__('Add Holiday');
    }
}