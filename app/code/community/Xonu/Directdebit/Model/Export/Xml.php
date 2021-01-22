<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
require_once Mage::getBaseDir('lib').'/Xonu/XML/Loader.php';

class Xonu_Directdebit_Model_Export_Xml extends Sepa_DirectDebit {

    public function getFile($collection) {

        $helper = Mage::helper('xonu_directdebit/account');

        $jobId = 'SEPA--'.$helper->getCurrentDate('d-m-Y--').time(); // job identifier

        // creditor and job information
        $account = $helper->getShopAccountSEPA();

        $this->setPainFormat(Mage::getStoreConfig('xonu_directdebit/export/sepa_format'));
        $this->setInitiatingPartyName($account->getHolder());
        $this->setMessageIdentification($jobId); // export job identifier
        $this->setPaymentInfoId($jobId); // internal unique identifier
        $this->setCreditorIban(str_replace(' ', '', $account->getIban()));
        $this->setCreditorBic($account->getBic());
        $this->setCreditorId($account->getCi());
        $this->setRequestedExecutionDate($account->getExecutionDate());
        $this->setDebitType(Mage::getStoreConfig('xonu_directdebit/export/sepa_type'));

        $seqTp = Mage::getStoreConfigFlag('xonu_directdebit/export/sepa_seqtp');

        $errorIds = array();
        foreach($collection as $row) {
            // $orderDate = date('Y-m-d', strtotime($row->getData('created_at')));

            // Prepare Sequence Type
            if($row->getData('recurrent')) {
                $orderDate = $row->getData('created_at');
                $mandateSignatureDate = $row->getData('sepa_mandate_date');

                if($orderDate == $mandateSignatureDate) {
                    $sequenceType = self::SeqTp_FirstRecurrent;
                } else {
                    $sequenceType = self::SeqTp_Recurrent;
                }
            } else {
                $sequenceType = self::SeqTp_OneTime;
            }

            // file_put_contents('/order-'.$row->getData('increment_id').'.txt', print_r($row->getData(),1) ."\n\n");

            $transaction = Sepa_DirectDebit_Transaction::factory()
                ->setEndToEndId($row->getData('increment_id')) // transaction purpose
                ->setAmount($row->getData('base_amount_ordered'))
                ->setTransactionIdentifier($row->getData('sepa_mandate_id'))
                ->setSignatureDate(substr($row->getData('sepa_mandate_date'), 0, 10))
                ->setDebtorName($row->getData('sepa_holder'))
                ->setDebtorIban($row->getData('sepa_iban'))
                ->SetDebtorBic($row->getData('sepa_bic'))
                ->setTransactionDescription($row->getData('increment_id'))
            ;

            if($seqTp) $transaction = $transaction->setSequenceType($sequenceType);

            $this->addTransaction($transaction);
        }

        if($helper->getXmlCompressionEnabled()) {
            $file = $this->asXML();
        } else {
            $simpleXml = new SimpleXmlElement($this);
            $dom = dom_import_simplexml($simpleXml)->ownerDocument;
            $dom->formatOutput = true;
            $file = $dom->saveXML();
        }

        return array('file' => $file, 'error_ids' => $errorIds);
    }
}


