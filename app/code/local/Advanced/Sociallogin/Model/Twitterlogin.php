<?php

class Advanced_Sociallogin_Model_Twitterlogin extends Zend_Oauth_Consumer {

    protected $_options = null;

    public function __construct() {
        $this->_config = new Zend_Oauth_Config;
        $this->_options = array(
            'consumerKey' => $this->getConsumerKey(),
            'consumerSecret' => $this->getConsumerSecret(),
            'signatureMethod' => 'HMAC-SHA1',
            'version' => '1.0',
            'requestTokenUrl' => 'https://api.twitter.com/oauth/request_token',
            'accessTokenUrl' => 'https://api.twitter.com/oauth/access_token',
            'authorizeUrl' => 'https://api.twitter.com/oauth/authorize'
        );

        $this->_config->setOptions($this->_options);
    }

    public function setCallbackUrl($url) {
        $this->_config->setCallbackUrl($url);
    }

    public function getConsumerKey() {
        return trim(Mage::getStoreConfig('sociallogin/twitter_login/consumer_key', Mage::app()->getStore()->getStoreId()));
    }

    public function getConsumerSecret() {
        return trim(Mage::getStoreConfig('sociallogin/twitter_login/consumer_secret', Mage::app()->getStore()->getStoreId()));
    }

}
