<?php

class Advanced_Sociallogin_Model_Mysql4_Sociallogin_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('sociallogin/sociallogin');
    }

}
