<?php

class Advanced_Sociallogin_Block_Facebooklogin extends Mage_Core_Block_Template {

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getUser() {
        return Mage::getModel('sociallogin/facebooklogin')->getUser();
    }

    public function getLoginUrl() {
        return Mage::getModel('sociallogin/facebooklogin')->getLoginUrl();
    }

    public function getDirectLoginUrl() {
        return Mage::helper('sociallogin')->getDirectLoginUrl();
    }

}
