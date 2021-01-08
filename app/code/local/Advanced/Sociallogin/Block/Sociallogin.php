<?php

class Advanced_Sociallogin_Block_Sociallogin extends Mage_Core_Block_Template {

    public $is_enable = false;
    public $is_enable_popup = false;

    public function getListSocial() {
        $store_id = Mage::app()->getStore()->getStoreId();

        $list_sociallogin = array(
            "facebook" => trim(Mage::getStoreConfig('sociallogin/facebook_login/sort_order', $store_id)),
            "googleplus" => trim(Mage::getStoreConfig('sociallogin/googleplus_login/sort_order', $store_id)),
            "yahoo" => trim(Mage::getStoreConfig('sociallogin/yahoo_login/sort_order', $store_id)),
            "twitter" => trim(Mage::getStoreConfig('sociallogin/twitter_login/sort_order', $store_id)),
            "linkedin" => trim(Mage::getStoreConfig('sociallogin/linkedin_login/sort_order', $store_id)),
        );
        asort($list_sociallogin);

        return $list_sociallogin;
    }
    
    public function isEnableOSC() {
        return $this->is_enable;
    }
    
    public function isEnablePopupOSC() {
        return $this->is_enable_popup;
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
        foreach (explode(',', Mage::getStoreConfig('sociallogin/general/display_social_login', Mage::app()->getStore()->getId())) as $value) {
            if ($value == 'under-login-link') {
                $this->is_enable = true;
            }
            
            if ($value == 'in-login-popup') {
                $this->is_enable_popup = true;
            }
        }
        return parent::_prepareLayout();
    }

}
