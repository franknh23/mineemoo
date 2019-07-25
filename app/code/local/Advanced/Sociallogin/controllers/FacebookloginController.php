<?php

class Advanced_Sociallogin_FacebookloginController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function loginAction() {
        $user = Mage::getModel('sociallogin/facebooklogin')->getUser();

        if ($user['id']) {
            $store_id = Mage::app()->getStore()->getStoreId(); //add
            $website_id = Mage::app()->getStore()->getWebsiteId(); //add
            
            $profile = array('firstname' => $user['first_name'], 'lastname' => $user['last_name'], 'email' => $user['email']);
            
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
                    die("<script type=\"text/javascript\">try{window.opener.location.href=\"" .  Mage::helper('sociallogin/data')->getUrlLoginRedirect()  . "\";}catch(e){window.opener.location.reload(true);} window.close();</script>");
                else 
                    die("<script type=\"text/javascript\">window.opener.location.reload(true); window.close();</script>");
            } else {
                Mage::getSingleton('core/session')->addError('You provided a email invalid!');
                die("<script type=\"text/javascript\">try{window.opener.location.reload(true);}catch(e){window.opener.location.href=\"" . Mage::getBaseUrl() . "\"} window.close();</script>");
            }
        }
    }
}
    