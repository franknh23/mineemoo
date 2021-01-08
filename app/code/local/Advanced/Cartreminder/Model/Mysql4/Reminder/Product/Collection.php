<?php

class Advanced_Cartreminder_Model_Mysql4_Reminder_Product_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('cartreminder/reminder_product');
    }
}