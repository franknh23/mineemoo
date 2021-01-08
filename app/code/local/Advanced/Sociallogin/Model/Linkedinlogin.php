<?php

class Advanced_Sociallogin_Model_Linkedinlogin extends Zend_Oauth_Consumer {

    protected $_options = null;

    public function __construct() {
        $this->_config = new Zend_Oauth_Config;
        $this->_options = array(
            'consumerKey' => $this->getConsumerKey(),
            'consumerSecret' => $this->getConsumerSecret(),
            'signatureMethod' => 'HMAC-SHA1',
            'version' => '1.0',
            'requestTokenUrl' => 'https://api.linkedin.com/uas/oauth/requestToken?scope=r_emailaddress',
            'accessTokenUrl' => 'https://api.linkedin.com/uas/oauth/accessToken',
            'authorizeUrl' => 'https://www.linkedin.com/uas/oauth/authenticate'
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
        return trim(Mage::getStoreConfig('sociallogin/linkedin_login/consumer_key', Mage::app()->getStore()->getStoreId()));
    }

    public function getConsumerSecret() {
        return trim(Mage::getStoreConfig('sociallogin/linkedin_login/consumer_secret', Mage::app()->getStore()->getStoreId()));
    }

}
