<?php

class Advanced_Sociallogin_YahoologinController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function loginAction() {
        if (!$this->getAuthorizedToken()) {
            $token = $this->getAuthorization();
        } else {
            $token = $this->getAuthorizedToken();
        }
        return $token;
    }

    public function userAction() {
        $yahoo = Mage::getModel('sociallogin/yahoologin');
        $requestToken = Mage::getSingleton('core/session')->getRequestToken();

        $oauth_data = array(
            'oauth_token' => $this->getRequest()->getParam('oauth_token'),
            'oauth_verifier' => $this->getRequest()->getParam('oauth_verifier')
        );

        try {
            $token = $yahoo->getAccessToken($oauth_data, unserialize($requestToken));
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError('Login failed as you have not granted access.');
            die("<script type=\"text/javascript\">try{window.opener.location.reload(true);}catch(e){window.opener.location.href=\"" . Mage::getBaseUrl() . "\"} window.close();</script>");
        }
				
        $client = $token->getHttpClient($yahoo->getOptions());
		
        $client->setUri('https://social.yahooapis.com/v1/user/' . (string) $token->{'xoauth_yahoo_guid'} . '/profile');
        $client->setMethod(Zend_Http_Client::GET);
        
        $people = simplexml_load_string($client->request()->getBody());
        
        $first_name = (string) $people->{'givenName'};
        $last_name = (string) $people->{'familyName'};
        $email = (string) $people->emails->{'handle'};

        if ($email) {
            $store_id = Mage::app()->getStore()->getStoreId(); //add
            $website_id = Mage::app()->getStore()->getWebsiteId(); //add
            
            $profile = array('firstname' => $first_name, 'lastname' => $last_name, 'email' => $email);

            if ($profile['email']) {
                //get customer
                $customer = Mage::helper('sociallogin')->getCustomerByEmail($profile['email'], $website_id); //add edition

                if (!$customer->getId()) {
                    $customer = Mage::helper('sociallogin')->createCustomerSosial($profile, $website_id, $store_id);
                    $customer->sendPasswordReminderEmail();
                    $customer->setConfirmation(null);
                    $customer->save();
                }

                Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
                if (trim(Mage::getStoreConfig('sociallogin/general/redirect_url')))
                    die("<script type=\"text/javascript\">try{window.opener.location.href=\"" . Mage::helper('sociallogin/data')->getUrlLoginRedirect() . "\";}catch(e){window.opener.location.reload(true);} window.close();</script>");
                else
                    die("<script type=\"text/javascript\">window.opener.location.reload(true); window.close();</script>");
            } else {
                Mage::getSingleton('core/session')->addError('You provided a email invalid!');
                die("<script type=\"text/javascript\">try{window.opener.location.reload(true);}catch(e){window.opener.location.href=\"" . Mage::getBaseUrl() . "\"} window.close();</script>");
            }
        }
    }

    public function getAuthorizedToken() {
        $token = false;
        if (!is_null(Mage::getSingleton('core/session')->getAccessToken())) {
            $token = unserialize(Mage::getSingleton('core/session')->getAccessToken());
        }
        return $token;
    }

    public function getAuthorization() {
        $yahoo = Mage::getModel('sociallogin/yahoologin');
        $yahoo->setCallbackUrl(Mage::getUrl('sociallogin/yahoologin/user'));

        if (!is_null($this->getRequest()->getParam('oauth_token')) && !is_null($this->getRequest()->getParam('oauth_verifier'))) {
            $oauth_data = array(
                'oauth_token' => $this->_getRequest()->getParam('oauth_token'),
                'oauth_verifier' => $this->_getRequest()->getParam('oauth_verifier')
            );
            $token = $yahoo->getAccessToken($oauth_data, unserialize(Mage::getSingleton('core/session')->getRequestToken()));
            Mage::getSingleton('core/session')->setAccessToken(serialize($token));
            $yahoo->redirect();
        } else {
            $token = $yahoo->getRequestToken();
            Mage::getSingleton('core/session')->setRequestToken(serialize($token));
            $yahoo->redirect();
        }
        return $token;
    }

}
