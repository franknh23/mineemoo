<?php

class Advanced_Sociallogin_Model_Googlelogin extends Mage_Core_Model_Abstract {

    public function loadGoogle() {
        require_once Mage::getBaseDir('base') . DS . 'lib' . DS . 'google-api' . DS . 'Google_Client.php';
        require_once Mage::getBaseDir('base') . DS . 'lib' . DS . 'google-api' . DS . 'contrib' . DS . 'Google_Oauth2Service.php';

        $client = new Google_Client();
        $client->setClientId($this->getClientId());
        $client->setClientSecret($this->getClientSecret());
        $client->setRedirectUri($this->getRedirectUri());
        $client->setAccessType('online');
        $client->setApprovalPrompt('auto');
        return $client;
    }

    public function getClientId() {
        return trim(Mage::getStoreConfig('sociallogin/googleplus_login/client_id', Mage::app()->getStore()->getStoreId()));
    }

    public function getClientSecret() {
        return trim(Mage::getStoreConfig('sociallogin/googleplus_login/client_secret', Mage::app()->getStore()->getStoreId()));
    }

    public function getRedirectUri() {
        return trim(Mage::getStoreConfig('sociallogin/googleplus_login/redirect_uri', Mage::app()->getStore()->getStoreId()));
    }

    public function getUser($code) {
        $client = $this->loadGoogle();
        $oauth20 = new Google_Oauth2Service($client);
        $client->authenticate($code);
        return $oauth20->userinfo->get();
    }

    public function getLoginUrl() {
        $google = $this->loadGoogle();
        $scope = array(
            'https://www.googleapis.com/auth/userinfo.profile',
            'https://www.googleapis.com/auth/userinfo.email'
        );
        $google->setScopes($scope);
        return $google->createAuthUrl();
    }

}
