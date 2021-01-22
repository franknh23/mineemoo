<?php

class Xonu_Sepaone_Helper_Data extends Mage_Core_Helper_Abstract {

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

    public function getTransactionStatusCodes() {
        $statusCodes = array(
            "transfered" => "Transfered",
            "funds_received" => "Funds Received",
            "refund_initiated" => "Refund Initiated",
            "refunded" => "Refunded",
            "chargeback" => "Chargeback",
            "chargeback_by_customer" => "Chargeback By Customer",
            "chargeback_insufficient_funds" => "Chargeback Insufficient Funds",
            "chargeback_account_incorrect" => "Chargeback Account Incorrect",
            "chargeback_account_closed" => "Chargeback Account Closed",
            "chargeback_account_blocked" => "Chargeback Account Blocked",
            "chargeback_mandate_invalid" => "Chargeback Mandate Invalid",
            "chargeback_mandate_missing" => "Chargeback Mandate Missing",
            "chargeback_mandate_missing" => "Chargeback Mandate Missing",
            "chargeback_cutoff_exceeded" => "Chargeback Cutoff Exceeded",
            "chargeback_recalled" => "Chargeback Recalled",
            "chargeback_fraud" => "Chargeback Fraud"
        );

        return $statusCodes;
    }

    public function getWebhookUrl() {
        $baseUrl = Mage::getStoreConfig('web/secure/base_url', 0);
        $secret = $this->getWebhookSecret();
        if($secret !== false) $secret = '?' . $secret;
        return $baseUrl . 'sepaone' . $secret;
    }

    public function getWebhookSecret() {
        $secret = trim(Mage::getStoreConfig('xonu_directdebit/sepaone/webhook_secret'));
        if($secret == '') $secret = false;
        return $secret;
    }

    public function getAlphaNumericString($length = 9) {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; $len = strlen($chars);
        $password = '';
        for ($i=0; $i<$length; $i++) $password .= substr($chars, rand(0, $len - 1), 1);
        return $password;
    }
}