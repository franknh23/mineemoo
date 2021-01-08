<?php

class Advanced_Sociallogin_Helper_Data extends Mage_Core_Helper_Abstract {

    function getStoreId() {
        return Mage::app()->getStore()->getStoreId();
    }

    function getBaseUrl() {
        return Mage::app()->getStore($this->getStoreId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
    }

    function getUrlLoginRedirect() {
        return $this->getBaseUrl() . trim(Mage::getStoreConfig('sociallogin/general/redirect_url', $this->getStoreId()));
    }

    function getFbLoginUrl() {
        $isSecure = Mage::getStoreConfig('web/secure/use_in_frontend');
        return $this->_getUrl('sociallogin/facebooklogin/login', array('_secure' => $isSecure, 'auth' => 1));
    }

    public function getCustomerByEmail($email, $website_id) {
        $collection = Mage::getModel('customer/customer')->getCollection()
                ->addFieldToFilter('email', $email);

        if (Mage::getStoreConfig('customer/account_share/scope', Mage::app()->getStore()->getStoreId())) {
            $collection->addFieldToFilter('website_id', $website_id);
        }
        return $collection->getFirstItem();
    }

    public function createCustomerSosial($profile, $website_id, $store_id) {
        $customer = Mage::getModel('customer/customer')->setId(null);
        $customer->setFirstname($profile['firstname'])
                ->setLastname($profile['lastname'])
                ->setEmail($profile['email'])
                ->setWebsiteId($website_id)
                ->setStoreId($store_id)
                ->save();

        $generatePassword = $customer->generatePassword();
        $customer->setPassword($generatePassword);
        try {
            $customer->save();
        } catch (Exception $e) {
            
        }
        return $customer;
    }

}
