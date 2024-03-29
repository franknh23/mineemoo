<?php

class Advanced_Sociallogin_PopupController extends Mage_Core_Controller_Front_Action {

    protected function _getSession() {
        return Mage::getSingleton('customer/session');
    }

    public function loginAction() {
        $username = $this->getRequest()->getPost('username', false);
        $password = $this->getRequest()->getPost('password', false);

        $session = Mage::getSingleton('customer/session');

        $result = array('success' => false);

        if ($username && $password) {
            try {
                $session->login($username, $password);
            } catch (Exception $e) {
                $result['error'] = $e->getMessage();
            }
            if (!isset($result['error'])) {
                $result['success'] = true;
            }
        } else {
            $result['error'] = $this->__('Please enter a username and password.');
        }
        $router = Mage::app()->getRequest();
        
        if(isset($result['success']) && $result['success']){             
                $layout = Mage::getModel('core/layout');
                $layout->getUpdate()->load('onestepcheckout_index_index');
                $layout->generateXml()->generateBlocks();
                $html = $layout->getBlock('onestepcheckout')->toHtml();  
                $result['html'] = $html;
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function forgotPasswordAction() {
        $email = $this->getRequest()->getPost('email', false);
        $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email);

        if ($customer->getId()) {
            try {
                $newPassword = $customer->generatePassword();
                $customer->changePassword($newPassword, false);
                $customer->sendPasswordReminderEmail();
                $result = array('success' => true);
            } catch (Exception $e) {
                $result = array('success' => false, 'error' => $e->getMessage());
            }
        } else {
            $result = array('success' => false, 'error' => 'Not found!');
        }

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function createAccountAction() {
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $result = array('success' => false, 'Can Not Login!');
        } else {
            $firstName = $this->getRequest()->getPost('firstname', false);
            $lastName = $this->getRequest()->getPost('lastname', false);
            $pass = $this->getRequest()->getPost('password', false);
            $passConfirm = $this->getRequest()->getPost('confirmation', false);
            $email = $this->getRequest()->getPost('emailaddress', false);
            $customer = Mage::getModel('customer/customer')
                    ->setFirstname($firstName)
                    ->setLastname($lastName)
                    ->setEmail($email)
                    ->setPassword($pass)
                    ->setConfirmation($passConfirm);

            try {
                $customer->save();
                Mage::dispatchEvent('customer_register_success', array('customer' => $customer)
                );
                $result = array('success' => true);
                $session->setCustomerAsLoggedIn($customer);
            } catch (Exception $e) {
                $result = array('success' => false, 'error' => $e->getMessage());
            }
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    protected function _welcomeCustomer(Mage_Customer_Model_Customer $customer, $isJustConfirmed = false) {
        $this->_getSession()->addSuccess(
                $this->__('Thank you for registering with %s.', Mage::app()->getStore()->getFrontendName())
        );
        if ($this->_isVatValidationEnabled()) {
            $configAddressType = Mage::helper('customer/address')->getTaxCalculationAddressType();
            $userPrompt = '';
            switch ($configAddressType) {
                case Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING:
                    $userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you shipping address for proper VAT calculation', Mage::getUrl('customer/address/edit'));
                    break;
                default:
                    $userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you billing address for proper VAT calculation', Mage::getUrl('customer/address/edit'));
            }
            $this->_getSession()->addSuccess($userPrompt);
        }

        $customer->sendNewAccountEmail(
                $isJustConfirmed ? 'confirmed' : 'registered', '', Mage::app()->getStore()->getId()
        );

        $successUrl = Mage::getUrl('customer/account/login', array('_secure' => true));
        if ($this->_getSession()->getBeforeAuthUrl()) {
            $successUrl = $this->_getSession()->getBeforeAuthUrl(true);
        }
        return $successUrl;
    }

    protected function _isVatValidationEnabled($store = null) {
        return Mage::helper('customer/address')->isVatValidationEnabled($store);
    }

}
