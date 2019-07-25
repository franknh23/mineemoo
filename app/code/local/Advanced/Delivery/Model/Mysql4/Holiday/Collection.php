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
 * Delivery Resource Collection Model
 * 
 * @category    Advanced
 * @package     Advanced_Delivery
 * @author      Advanced Developer
 */
class Advanced_Delivery_Model_Mysql4_Holiday_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('delivery/holiday');
    }
    
    public function addStoreFilter($store, $withAdmin = true){

        if ($store instanceof Mage_Core_Model_Store) {
            $store = array($store->getId());
        }

        if (!is_array($store)) {
            $store = array($store);
        }

        $this->addFilter('store_id', array('in' => $store));

        return $this;
    }
}