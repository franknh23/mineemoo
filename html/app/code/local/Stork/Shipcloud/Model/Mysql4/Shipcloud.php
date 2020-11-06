<?php

class Stork_Shipcloud_Model_Mysql4_Shipcloud extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the shipcloud_id refers to the key field in your database table.
        $this->_init('shipcloud/shipcloud', 'shipcloud_id');
    }
}