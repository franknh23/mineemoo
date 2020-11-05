<?php

/**
 * Class Stork_Shipcloud_Model_Pickup
 */
class Stork_Shipcloud_Model_Pickup extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('shipcloud/pickup');
    }
}
 
