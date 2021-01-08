<?php

class Advanced_Cartreminder_Model_Reminder_Product extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('cartreminder/reminder_product');
    }
}