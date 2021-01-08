<?php

class Advanced_Sociallogin_Block_Socialloginpopup extends Mage_Core_Block_Template {

    public function getListSocial() {
        $list_sociallogin = array(
            "facebook" => trim(Mage::getStoreConfig('sociallogin/facebook_login/sort_order')),
            "googleplus" => trim(Mage::getStoreConfig('sociallogin/googleplus_login/sort_order')),
            "yahoo" => trim(Mage::getStoreConfig('sociallogin/yahoo_login/sort_order')),
            "twitter" => trim(Mage::getStoreConfig('sociallogin/twitter_login/sort_order')),
            "linkedin" => trim(Mage::getStoreConfig('sociallogin/linkedin_login/sort_order')),
        );
        asort($list_sociallogin);
        
        return $list_sociallogin;
    }

    public function isEnable() {
        if (Mage::getStoreConfig('sociallogin/general/enable', Mage::app()->getStore()->getId())) {
            return true;
        }

        return false;
    }

    public function isEnablePopup() {
        if ($this->isEnable() && Mage::getStoreConfig('sociallogin/general/display_social_login', Mage::app()->getStore()->getId())) {
            return true;
        }

        return false;
    }

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

}
