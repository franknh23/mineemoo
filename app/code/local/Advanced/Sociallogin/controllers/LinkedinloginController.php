<?php

class Stableaddon_Sociallogin_LinkedinloginController extends Mage_Core_Controller_Front_Action {

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
        $linkedin = Mage::getModel('sociallogin/linkedinlogin');
        $requestToken = Mage::getSingleton('core/session')->getRequestToken();

        $oauth_data = array(
            'oauth_token' => $this->getRequest()->getParam('oauth_token'),
            'oauth_verifier' => $this->getRequest()->getParam('oauth_verifier')
        );

        try {
            $token = $linkedin->getAccessToken($oauth_data, unserialize($requestToken));
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError('Login failed as you have not granted access.');
            die("<script type=\"text/javascript\">try{window.opener.location.reload(true);}catch(e){window.opener.location.href=\"" . Mage::getBaseUrl() . "\"} window.close();</script>");
        }
        

        $client = $token->getHttpClient($linkedin->getOptions());
        
        $client->setUri('http://api.linkedin.com/v1/people/~');
        $client->setMethod(Zend_Http_Client::GET);
        $people = simplexml_load_string($client->request()->getBody());
        
        $first_name = (string) $people->{'first-name'};
        $last_name = (string) $people->{'last-name'};
        
        $client->setUri('http://api.linkedin.com/v1/people/~/email-address');
        $client->setMethod(Zend_Http_Client::GET);
		
        $email = simplexml_load_string($client->request()->getBody());
		
		
        if ($email) {
            $store_id = Mage::app()->getStore()->getStoreId(); //add
            $website_id = Mage::app()->getStore()->getWebsiteId(); //add
            
            $profile = array('firstname' => $first_name, 'lastname' => $last_name, 'email' => $email);
			
            if ($email) {
                //get customer
                $customer = Mage::helper('sociallogin')->getCustomerByEmail($email, $website_id); //add edition

                if (!$customer->getId()) {
                    $customer = Mage::helper('sociallogin')->createCustomerSosial($profile, $website_id, $store_id);
                    $customer->sendPasswordReminderEmail();
                    $customer->setConfirmation(null);
                    $customer->save();
                }

                Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
                if (trim(Mage::helper('sociallogin/data')->getUrlLoginRedirect()))
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
        $linkedin = Mage::getModel('sociallogin/linkedinlogin');
        $linkedin->setCallbackUrl(Mage::getUrl('sociallogin/linkedinlogin/user'));

        if (!is_null($this->getRequest()->getParam('oauth_token')) && !is_null($this->getRequest()->getParam('oauth_verifier'))) {
            $oauth_data = array(
                'oauth_token' => $this->_getRequest()->getParam('oauth_token'),
                'oauth_verifier' => $this->_getRequest()->getParam('oauth_verifier')
            );
            $token = $linkedin->getAccessToken($oauth_data, unserialize(Mage::getSingleton('core/session')->getRequestToken()));
            Mage::getSingleton('core/session')->setAccessToken(serialize($token));
            $linkedin->redirect();
        } else {
            $token = $linkedin->getRequestToken();
            Mage::getSingleton('core/session')->setRequestToken(serialize($token));
            $linkedin->redirect();
        }
        return $token;
    }

}
