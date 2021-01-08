<?php

class Advanced_Sociallogin_Model_Yahoologin extends Zend_Oauth_Consumer {

    protected $_options = null;

    public function __construct() {
        $this->_config = new Zend_Oauth_Config;
        $this->_options = array(
            'consumerKey' => $this->getConsumerKey(),
            'consumerSecret' => $this->getConsumerSecret(),
            'signatureMethod' => 'HMAC-SHA1',
            'version' => '1.0',
            'requestTokenUrl' => 'https://api.login.yahoo.com/oauth/v2/get_request_token',
            'accessTokenUrl' => 'https://api.login.yahoo.com/oauth/v2/get_token',
            'authorizeUrl' => 'https://api.login.yahoo.com/oauth/v2/request_auth'
        );

        $this->_config->setOptions($this->_options);
    }

    public function setCallbackUrl($url) {
        $this->_config->setCallbackUrl($url);
    }

    public function getOptions() {
        return $this->_options;
    }

    public function getConsumerKey() {
        return trim(Mage::getStoreConfig('sociallogin/yahoo_login/consumer_key'), Mage::app()->getStore()->getStoreId());
    }

    public function getConsumerSecret() {
        return trim(Mage::getStoreConfig('sociallogin/yahoo_login/consumer_secret'), Mage::app()->getStore()->getStoreId());
    }

}
