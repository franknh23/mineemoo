<?php
class Advanced_Delivery_Model_Mysql4_Deliverydate_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('delivery/deliverydate');
    }

}
