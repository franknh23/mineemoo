<?php

class Meetanshi_InvisibleCaptcha_Model_Observer extends Varien_Event_Observer
{
    public function serverVerify($observer)
    {
        $param = Mage::app()->getRequest()->getParam('invisible_token');
        $fullActionName = $e_type = str_replace('_', '/',
            $observer->getEvent()->getControllerAction()->getFullActionName());
        $allUrl = Mage::helper('recaptcha')->getUrls();
        $postData = Mage::app()->getRequest()->getParams();

        if (count($postData) > 0) {
            foreach ($allUrl as $url) {
                $url = str_replace(Mage::getBaseUrl(),'',$url);
                if (strpos($url, $fullActionName) !== false && $url == $fullActionName) {
                    if ($param != '') {
                        $result = Mage::helper('recaptcha')->verify($param);
                        if (!$result['success']) {
                            Mage::getSingleton('core/session')->addError($result['error']);
                            $url = Mage::helper('core/http')->getHttpReferer() ? Mage::helper('core/http')->getHttpReferer() : Mage::getUrl();
                            Mage::app()->getFrontController()->getResponse()->setRedirect($url);
                            Mage::app()->getResponse()->sendResponse();
                            exit;
                        }
                    } else {
                        Mage::getSingleton('core/session')->addError('The request is invalid or malformed');
                        $url = Mage::helper('core/http')->getHttpReferer() ? Mage::helper('core/http')->getHttpReferer() : Mage::getUrl();
                        Mage::app()->getFrontController()->getResponse()->setRedirect($url);
                        Mage::app()->getResponse()->sendResponse();
                        exit;
                    }
                }
            }
        }
    }
}
