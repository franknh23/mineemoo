<?php

class Advanced_Cartreminder_Model_Mysql4_Reminder extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('cartreminder/reminder', 'advanced_reminder_id');
    }
}