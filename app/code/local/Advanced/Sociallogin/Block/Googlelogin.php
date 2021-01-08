<?php

class Advanced_Sociallogin_Block_Googlelogin extends Mage_Core_Block_Template {

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getUser() {
        return Mage::getModel('sociallogin/googlelogin')->getUser();
    }

    public function getLoginUrl() {
        return Mage::getModel('sociallogin/googlelogin')->getLoginUrl();
    }

    public function getDirectLoginUrl() {
        return Mage::helper('googlelogin')->getDirectLoginUrl();
    }

}
