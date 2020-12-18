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
 * Bannerrules Status Model
 * 
 * @category    Stabeaddon
 * @package     Stabeaddon_Bannerrules
 * @author      Stabeaddon Developer
 */
class Advanced_Bannerrules_Model_Edit_Staticblocks extends Varien_Object
{
    
   
    
    /**
     * get model option hash as array
     *
     * @return array
     */
    static public function getOptionHash()
    {
        $options = array();
        $collection = Mage::getModel('cms/block')->getCollection()
                                    ->addFieldToFilter('is_active',1);
        
        foreach ($collection as $block) {
            $options[] = array(
                'value'    => $block->getId(),
                'label'    => $block->getTitle()
            );
        }
        
        $options[] = array(
                'value'    => '',
                'label'    => '--- '.Mage::helper('bannerrules')->__('None of Above').' ---'
            );
        return $options;
    }
}