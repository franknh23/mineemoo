<?php

class Advanced_Sociallogin_TwitterloginController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    // url to login

    public function loginAction() {
        if (!$this->getAuthorizedToken()) {
            $token = $this->getAuthorization();
        } else {
            $token = $this->getAuthorizedToken();
        }

        return $token;
    }

    //url after authorize
    public function userAction() {
        $otwitter = Mage::getModel('sociallogin/twitterlogin');
        $requestToken = Mage::getSingleton('core/session')->getRequestToken();

        $oauth_data = array(
            'oauth_token' => $this->getRequest()->getParam('oauth_token'),
            'oauth_verifier' => $this->getRequest()->getParam('oauth_verifier')
        );

        try {
            $token = $otwitter->getAccessToken($oauth_data, unserialize($requestToken));
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError('Login failed as you have not granted access.');
            die("<script type=\"text/javascript\">try{window.opener.location.reload(true);}catch(e){window.opener.location.href=\"" . Mage::getBaseUrl() . "\"} window.close();</script>");
        }

        if ($token->user_id) {
            $store_id = Mage::app()->getStore()->getStoreId(); //add
            $website_id = Mage::app()->getStore()->getWebsiteId(); //add
            $name = (string) $token->screen_name;
            
            $profile = array('firstname' => $name, 'lastname' => $name, 'email' => $name . '@twitter.com');

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
        $otwitter = Mage::getModel('sociallogin/twitterlogin');
        $otwitter->setCallbackUrl(Mage::getUrl('sociallogin/twitterlogin/user'));

        if (!is_null($this->getRequest()->getParam('oauth_token')) && !is_null($this->getRequest()->getParam('oauth_verifier'))) {
            $oauth_data = array(
                'oauth_token' => $this->_getRequest()->getParam('oauth_token'),
                'oauth_verifier' => $this->_getRequest()->getParam('oauth_verifier')
            );
            $token = $otwitter->getAccessToken($oauth_data, unserialize(Mage::getSingleton('core/session')->getRequestToken()));
            Mage::getSingleton('core/session')->setAccessToken(serialize($token));
            $otwitter->redirect();
        } else {
            $token = $otwitter->getRequestToken();
            Mage::getSingleton('core/session')->setRequestToken(serialize($token));
            $otwitter->redirect();
        }
        return $token;
    }

}
