<?php

/**
 * Advanced Checkout
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Onestepcheckout.com license that is
 * available through the world-wide-web at this URL:
 * http://www.www.advancedcheckout.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category 	Advanced Checkout
 * @package 	UC_Onestepcheckout
 * @copyright 	Copyright (c) 2015 Advanced Checkout (http://www.www.advancedcheckout.com/)
 * @license 	http://www.www.advancedcheckout.com/license-agreement.html
 */

/**
 * Onestepcheckout Block
 * 
 * @category 	Onestepcheckout
 * @package 	UC_Onestepcheckout
 * @author  	Onestepcheckout Developer
 */
class Advanced_Onestepcheckout_Helper_Euvat extends Mage_Checkout_Helper_Url {

    /**
     * check eu vat
     *
     * @return UC_Onestepcheckout_Helper_Euvat
     */
    public function verifyCustomerVat($quote,$vat_number = null,$country = null) {
   
        if ($vat_number) {

            $quote->getBillingAddress()->setTaxvat($vat_number);
            $quote->getShippingAddress()->setTaxvat($vat_number);

            $vat_number = preg_replace('/^\D{0,2}/', '', $vat_number);

            if(!$country)
                $country = $quote->getBillingAddress()->getCountry();
            if ($country == "GR") {
                $country = "EL";
            }

            if (in_array($country, array("AT", "BE", "BG", "CY", "CZ", "DE", "DK", "EE", "EL", "ES", "FI", "FR", "GB", "HU", "IE", "IT", "LT", "LU", "LV", "MT", "NL", "PL", "PT", "RO", "SE", "SI", "SK"))) {
                try {

                    if (Mage::getStoreConfig('onestepcheckout/vat/vat_verification',Mage::app()->getStore()->getStoreId()) == Advanced_Onestepcheckout_Model_Config_Source_Vatverification::ISVAT) {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, 'http://isvat.appspot.com/' . $country . '/' . $vat_number . '/');
                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $content = curl_exec($ch);
                        curl_close($ch);

                        if (strpos($content, "true") === false) {
                            $vat_exemption_flag = false;
                        } else {
                            $vat_exemption_flag = true;
                        }
                    } else {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, 'http://ec.europa.eu/taxation_customs/vies/viesquer.do');
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, 'vat=' . $vat_number . '&iso=' . $country . '&ms=' . $country);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $content = curl_exec($ch);
                        curl_close($ch);

                        if (strpos($content, Mage::helper('onestepcheckout')->__("Yes, valid VAT number")) === false) {
                            $vat_exemption_flag = false;
                        } else {
                            $vat_exemption_flag = true;
                        }
                    }
                    
                    $quote->getBillingAddress()->setIsValidVat($vat_exemption_flag);
                    $quote->getShippingAddress()->setIsValidVat($vat_exemption_flag);
                    $quote->save();
                    return $vat_exemption_flag;
                } catch (Exception $e) {
                    
                }
            }
        }

        $quote->getBillingAddress()->setIsValidVat(null);
        $quote->getShippingAddress()->setIsValidVat(null);

        return false;
    }
    
    public function getVatBaseCountryMode(){
    	return Mage::getStoreConfig('onestepcheckout/vat/base_country',Mage::app()->getStore()->getStoreId());
    }
    
    public function getVatWithinCountryMode(){
    	return Mage::getStoreConfig('onestepcheckout/vat/if_not_base_country',Mage::app()->getStore()->getStoreId());
    }

}
