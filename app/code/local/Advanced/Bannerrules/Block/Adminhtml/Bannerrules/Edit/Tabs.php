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
 * Bannerrules Edit Tabs Block
 * 
 * @category    Stabeaddon
 * @package     Stabeaddon_Bannerrules
 * @author      Stabeaddon Developer
 */
class Advanced_Bannerrules_Block_Adminhtml_Bannerrules_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('bannerrules_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('bannerrules')->__('Item Information'));
    }
    
    /**
     * prepare before render block to html
     *
     * @return Advanced_Bannerrules_Block_Adminhtml_Bannerrules_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('bannerrules')->__('Information'),
            'title'     => Mage::helper('bannerrules')->__('Information'),
            'content'   => $this->getLayout()
                                ->createBlock('bannerrules/adminhtml_bannerrules_edit_tab_form')
                                ->toHtml(),
        ));

        $this->addTab('conditions', array(
            'label' => Mage::helper('bannerrules')->__('Conditions'),
            'title' => Mage::helper('bannerrules')->__('Conditions'),
            'content' => $this->getLayout()
                    ->createBlock('bannerrules/adminhtml_bannerrules_edit_tab_conditions')
                    ->toHtml(),
        ));
        
        $this->addTab('actions', array(
            'label' => Mage::helper('bannerrules')->__('Actions '),
            'title' => Mage::helper('bannerrules')->__('Actions '),
            'content' => $this->getLayout()
                    ->createBlock('bannerrules/adminhtml_bannerrules_edit_tab_actions')
                    ->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}