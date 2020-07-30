<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Tax
 * @copyright  Copyright (c) 2006-2014 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tax Calculation Model
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Advanced_Onestepcheckout_Model_Tax_Calculation extends Mage_Tax_Model_Calculation {

    /**
     * Get request object with information necessary for getting tax rate
     * Request object contain:
     *  country_id (->getCountryId())
     *  region_id (->getRegionId())
     *  postcode (->getPostcode())
     *  customer_class_id (->getCustomerClassId())
     *  store (->getStore())
     *
     * @param   null|false|Varien_Object $shippingAddress
     * @param   null|false|Varien_Object $billingAddress
     * @param   null|int $customerTaxClass
     * @param   null|int $store
     * @return  Varien_Object
     */
    public function getRateRequest(
    $shippingAddress = null, $billingAddress = null, $customerTaxClass = null, $store = null) {
        if ($shippingAddress === false && $billingAddress === false && $customerTaxClass === false) {
            return $this->getRateOriginRequest($store);
        }
        $address = new Varien_Object();
        $customer = $this->getCustomer();
        $basedOn = Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_BASED_ON, $store);

        if (($shippingAddress === false && $basedOn == 'shipping') || ($billingAddress === false && $basedOn == 'billing')
        ) {
            $basedOn = 'default';
        } else {
            if ((($billingAddress === false || is_null($billingAddress) || !$billingAddress->getCountryId()) && $basedOn == 'billing') || (($shippingAddress === false || is_null($shippingAddress) || !$shippingAddress->getCountryId()) && $basedOn == 'shipping')
            ) {
                if ($customer) {
                    $defBilling = $customer->getDefaultBillingAddress();
                    $defShipping = $customer->getDefaultShippingAddress();

                    if ($basedOn == 'billing' && $defBilling && $defBilling->getCountryId()) {
                        $billingAddress = $defBilling;
                    } else if ($basedOn == 'shipping' && $defShipping && $defShipping->getCountryId()) {
                        $shippingAddress = $defShipping;
                    } else {
                        $basedOn = 'default';
                    }
                } else {
                    $basedOn = 'default';
                }
            }
        }

        switch ($basedOn) {
            case 'billing':
                $address = $billingAddress;
                break;
            case 'shipping':
                $address = $shippingAddress;
                break;
            case 'origin':
                $address = $this->getRateOriginRequest($store);
                break;
            case 'default':
                $address
                        ->setCountryId(Mage::getStoreConfig(
                                        Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_COUNTRY, $store))
                        ->setRegionId(Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_REGION, $store))
                        ->setPostcode(Mage::getStoreConfig(
                                        Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_POSTCODE, $store));
                break;
        }

        if (is_null($customerTaxClass) && $customer) {
            $customerTaxClass = $customer->getTaxClassId();
        } elseif (($customerTaxClass === false) || !$customer) {
            if(version_compare(Mage::getVersion(), '1.8.1.0', '<')){
                $customerTaxClass = $this->getDefaultCustomerTaxClass($store);
            }else{
                $customerTaxClass = Mage::getModel('customer/group')
                        ->getTaxClassId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
            }
        }

        $request = new Varien_Object();

        if ($address->getBuyWithoutVat() > 0) {

            $mode = null;

            $country_code = $address->getCountry();
            if ($country_code == "GR") {
                $country_code = "EL";
            }

            if ($address->getCountry() == Mage::getStoreConfig('onestepcheckout/vat/country', Mage::app()->getStore()->getStoreId())) {

                $mode = Mage::helper('onestepcheckout/euvat')->getVatBaseCountryMode();
            } elseif (in_array($country_code, array("AT", "BE", "BG", "CY", "CZ", "DE", "DK", "EE", "EL", "ES", "FI", "FR", "GB", "HU", "IE", "IT", "LT", "LU", "LV", "MT", "NL", "PL", "PT", "RO", "SE", "SI", "SK"))) {

                $mode = Mage::helper('onestepcheckout/euvat')->getVatWithinCountryMode();
            }

            if ($mode) {

                $rule_ids = Mage::getStoreConfig('onestepcheckout/vat/rule', Mage::app()->getStore()->getStoreId());


                if ($rule_ids) {

                    switch ($mode) {

                        case(1):                                   
                            if($address->getIsValidVat()>0){
                                $request->setDisableByRule($rule_ids);
                            }
                            break;
                        case(2):
                            $request->setDisableByRule($rule_ids);
                            break;
                    }
                }
            }
        }


        $request
                ->setCountryId($address->getCountryId())
                ->setRegionId($address->getRegionId())
                ->setPostcode($address->getPostcode())
                ->setStore($store)
                ->setCustomerClassId($customerTaxClass);

        return $request;
    }

}
