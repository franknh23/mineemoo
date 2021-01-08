<?php

class Advanced_Sociallogin_GoogleloginController extends Mage_Core_Controller_Front_Action {

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
    
    public function oauth2callbackAction() {
        $user = Mage::getModel('sociallogin/googlelogin')->getUser($this->getRequest()->getParam('code'));
        if ($user['id']) {
            $store_id = Mage::app()->getStore()->getStoreId(); //add
            $website_id = Mage::app()->getStore()->getWebsiteId(); //add
            $username = explode(' ', $user['name'], 2);
            $profile = array('firstname' => $username[0], 'lastname' => $username[1], 'email' => $user['email']);
            
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
                if(trim(Mage::getStoreConfig('sociallogin/general/redirect_url')))
                    die("<script type=\"text/javascript\">try{window.opener.location.href=\"" . Mage::helper('sociallogin/data')->getUrlLoginRedirect() . "\";}catch(e){window.opener.location.reload(true);} window.close();</script>");
                else 
                    die("<script type=\"text/javascript\">window.opener.location.reload(true); window.close();</script>");
            } else {
                Mage::getSingleton('core/session')->addError('You provided a email invalid!');
                die("<script type=\"text/javascript\">try{window.opener.location.reload(true);}catch(e){window.opener.location.href=\"" . Mage::getBaseUrl() . "\"} window.close();</script>");
            }
        }
    }

}
