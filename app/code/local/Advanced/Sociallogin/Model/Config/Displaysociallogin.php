<?php

class Advanced_Sociallogin_Model_Config_Displaysociallogin {

    public function toOptionArray() {
        return array(
            array('value' => 'in-login-popup', 'label' => Mage::helper('adminhtml')->__('In Login Popup')),
            array('value' => 'under-login-link', 'label' => Mage::helper('adminhtml')->__('Under Login Link'))
        );
    }

}
