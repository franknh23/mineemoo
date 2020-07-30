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
 * Bannerrules Adminhtml Block
 * 
 * @category    Stabeaddon
 * @package     Stabeaddon_Bannerrules
 * @author      Stabeaddon Developer
 */
class Advanced_Bannerrules_Block_Adminhtml_Bannerrules extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_bannerrules';
        $this->_blockGroup = 'bannerrules';
        $this->_headerText = Mage::helper('bannerrules')->__('Manage Banners');
        $this->_addButtonLabel = Mage::helper('bannerrules')->__('Add Banner');
        parent::__construct();
    }
}