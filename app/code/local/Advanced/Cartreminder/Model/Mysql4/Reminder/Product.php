<?php

class Advanced_Cartreminder_Model_Mysql4_Reminder_Product extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('cartreminder/reminder_product', 'advanced_reminder_product_id');
    }
}