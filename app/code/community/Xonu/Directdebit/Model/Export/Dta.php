<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
require_once Mage::getBaseDir('lib').'/Xonu/DTA/DTAZV.php';
require_once Mage::getBaseDir('lib').'/Xonu/DTA/DTA.php';

class Xonu_Directdebit_Model_Export_Dta {

    public function getFile($collection) {

        $helper = Mage::helper('xonu_directdebit/account');

        $dta = new DTAZV(DTA_DEBIT);
        $dta->setAccountFileSender($helper->getShopAccountDTA());

        $columns = array(
            'name'             => 'sepa_holder',
            'account_number'   => 'sepa_iban',
            'bank_code'        => 'sepa_bic',
            // 'amount'           => 'base_amount_ordered',
            // 'currency'         => 'base_currency_code',
            // 'purpose'          => 'increment_id'
        );

        $errorIds = array();
        foreach($collection as $row) {

            $account = array();
            foreach($columns as $target => $source) {
                $account[$target] = $row->getData($source);
            }

            $amount = round($row->getData('base_amount_ordered'), 2);
            $purpose = $row->getData('increment_id');

            if(!$dta->addExchange($account, $amount, $purpose)) $errorIds[] = $row->getData('order_id');
        }

        return array('file' => $dta->getFileContent(), 'error_ids' => $errorIds);
    }

}