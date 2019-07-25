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
 * Delivery Resource Model
 * 
 * @category    Advanced
 * @package     Advanced_Delivery
 * @author      Advanced Developer
 */
class Advanced_Delivery_Model_Mysql4_Intervals extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('delivery/intervals', 'intervals_id');
    }
}