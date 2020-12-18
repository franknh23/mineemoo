<?php
/**
 * Stabeaddon
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Stabeaddon.com license that is
 * available through the world-wide-web at this URL:
 * http://www.stabeaddon.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Stabeaddon
 * @package     Stabeaddon_Bannerrules
 * @copyright   Copyright (c) 2012 Stabeaddon (http://www.stabeaddon.com/)
 * @license     http://www.stabeaddon.com/license-agreement.html
 */

/**
 * Bannerrules Edit Block
 * 
 * @category     Stabeaddon
 * @package     Stabeaddon_Bannerrules
 * @author      Stabeaddon Developer
 */
class Advanced_Bannerrules_Block_Adminhtml_Bannerrules_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        
        $this->_objectId = 'id';
        $this->_blockGroup = 'bannerrules';
        $this->_controller = 'adminhtml_bannerrules';
        
        $this->_updateButton('save', 'label', Mage::helper('bannerrules')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('bannerrules')->__('Delete'));
        
        $this->_addButton('saveandcontinue', array(
            'label'        => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'    => 'saveAndContinueEdit()',
            'class'        => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('bannerrules_content') == null)
                    tinyMCE.execCommand('mceAddControl', false, 'bannerrules_content');
                else
                    tinyMCE.execCommand('mceRemoveControl', false, 'bannerrules_content');
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }
    
    /**
     * get text to show in header when edit an item
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('bannerrules_data')
            && Mage::registry('bannerrules_data')->getId()
        ) {
            return Mage::helper('bannerrules')->__("Edit Banner '%s'",
                                                $this->htmlEscape(Mage::registry('bannerrules_data')->getTitle())
            );
        }
        return Mage::helper('bannerrules')->__('Add Banner');
    }
}