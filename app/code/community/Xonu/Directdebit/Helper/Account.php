<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Helper_Account {

    const PAIN008_DE = 'pain.008.002.02';
    const PAIN008_AT = 'pain.008.001.02';

    /**
     * returns bank account settings of the shop owner, required for DTA export
     * @return array
     */
    public function getShopAccountDTA() {
        return array(
            'account_number' => Mage::getStoreConfig('xonu_directdebit/export/dta_number'),
            'bank_code'      => Mage::getStoreConfig('xonu_directdebit/export/dta_bankcode'),
            'name'           => Mage::getStoreConfig('xonu_directdebit/export/dta_holder'),
        );
    }

    /**
     * returns bank account settings of the shop owner, required for SEPA-XML export
     * @return array
     */
    public function getShopAccountSEPA() {

        if(Mage::getStoreConfigFlag('xonu_directdebit/export/sepa_holderequalscreditor')) {
            $holder = Mage::getStoreConfig('payment/xonu_directdebit/creditor_info');
        } else {
            $holder = Mage::getStoreConfig('xonu_directdebit/export/sepa_holder');
        }

        $account = new Varien_Object();
        $account->setData(array(
            'holder'         => $holder,
            'bic'            => Mage::getStoreConfig('xonu_directdebit/export/sepa_bic'),
            'iban'           => Mage::getStoreConfig('xonu_directdebit/export/sepa_iban'),
            'ci'             => Mage::getStoreConfig('payment/xonu_directdebit/creditor_identifier'),
            'execution_date' => date('Y-m-d', strtotime($this->getCurrentDate('Y-m-d') . ' +1 Weekday'))
        ));

        return $account;
    }

    /**
     * output xml with or without redundant spaces
     * @return bool
     */
    public function getXmlCompressionEnabled() {
        return Mage::getStoreConfigFlag('xonu_directdebit/export/sepa_compression');
    }

    /**
     * outputs current date and time with correct time zone settings of magento
     * @param string $format
     * @return string
     */
    public function getCurrentDate($format = 'd.m.Y') {
        return date($format, Mage::getModel('core/date')->timestamp(time()));
    }

}