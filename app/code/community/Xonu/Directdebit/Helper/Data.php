<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * checks if the billing address country is among optional countries
     * @return bool
     */
    public function isBicOptional() {
        if(!Mage::getStoreConfig('xonu_directdebit/bic/optional_bic_active')) return false;

        if(Mage::getStoreConfig('xonu_directdebit/bic/optional_bic_restriction')) {
            $address = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress();
            $optionalCountries = explode(',', Mage::getStoreConfig('xonu_directdebit/bic/optional_bic_countries'));
            return in_array($address->getCountry(), $optionalCountries) ? true : false;
        } else {
            return true;
        }
    }


    public function getLocalTimestampSql($format = 'Y-m-d H:i:s') {
        return date($format, Mage::getModel('core/date')->timestamp(time()));
    }

    public function getGlobalTimestampSql($format = 'Y-m-d H:i:s') {
        return date($format, time());
    }

    public function getCurrentDateTime($format = 'Y-m-d H:i:s') {
        return date($format, time());
    }

    public function formatDateTime($timestamp) {
        return Mage::helper('core')->formatDate($timestamp, 'medium', true);
    }

    public function getCompatibilityMode() {
        return Mage::getStoreConfigFlag('xonu_directdebit/mandate/compatibility_mode');
    }

    /**
     * converts seconds to a proper interval
     * note: formatted DateInterval('PT3600S') will plainly output 3600s instead of 1h
     * @param $seconds
     * @return DateInterval
     */
    function convertSecondsToInterval($seconds) {
        $d1 = new DateTime(); $d2 = new DateTime();
        $d2->add(new DateInterval('PT'. $seconds .'S'));
        return $d2->diff($d1);
    }

    /**
     * returns store information
     * based on Mage_Adminhtml_Block_Sales_Order_View_Info::getOrderStoreName()
     * @param $storeId
     * @return string
     */
    public function getStoreInfo($storeId) {
        $store = Mage::app()->getStore($storeId);
        $name = array(
            $store->getWebsite()->getName(),
            $store->getGroup()->getName(),
            $store->getName()
        );
        return implode('<br/>', $name);
    }

    /**
     * directory for storage of exported data
     * @return string
     */
    public function getExportDir() {
        return Mage::getBaseDir('export').DS.'xonu_directdebit';
    }

    public function getCurrentDate($format = 'd.m.Y') {
        return date($format, Mage::getModel('core/date')->timestamp(time()));
    }

    public function getExportFilename($extension = '', $prefix = '') {
        if($extension != '') $extension = '.'.$extension; if($prefix != '') $prefix = $prefix.'_';
        return $prefix.$this->getCurrentDate(time().'_d-m-Y_h-i-s').$extension;
    }


    public function getExplanation() {
        $content = trim(Mage::getStoreConfig('xonu_directdebit/mandate/mandate_explanation'));
        if($content != '')
            return $content;
        else
            return $this->__('For the SEPA Direct Debit payment we require a mandate that you may grant in the next step. The mandate will be sent to your e-mail address afterwards.');
    }
}