<?php

class Advanced_Sociallogin_Model_Facebooklogin extends Mage_Core_Model_Abstract {

    public function loadFacebook() {

        try {
            require_once Mage::getBaseDir('base') . DS . 'lib' . DS . 'facebook-api' . DS . 'facebook-php-sdk-v3' . DS . 'src' . DS . 'facebook.php';
        } catch (Exception $e) {
            
        }
        $store_id = Mage::app()->getStore()->getStoreId();
        $facebook = new Facebook(array(
            'appId' => trim(Mage::getStoreConfig('sociallogin/facebook_login/appId', $store_id)),
            'secret' => trim(Mage::getStoreConfig('sociallogin/facebook_login/secret', $store_id)),
            'cookie' => true,
        ));

        return $facebook;
    }

    public function getUser() {
        $facebook = $this->loadFacebook();
        $userId = $facebook->getUser();
        $profile = NULL;

        if ($userId) {
            try {
                $profile = $facebook->api('/me?fields=id,name,first_name,last_name,email');
            } catch (FacebookApiException $e) {
                
            }
        }

        return $profile;
    }

    public function getLoginUrl() {
        $facebook = $this->loadFacebook();
        $loginUrl = $facebook->getLoginUrl(
                array(
                    'display' => 'popup',
                    'redirect_uri' => Mage::helper('sociallogin')->getFbLoginUrl(),
                    'scope' => 'public_profile, email',
                )
        );
        return $loginUrl;
    }

}
