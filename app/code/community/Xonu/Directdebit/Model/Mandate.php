<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Model_Mandate extends Mage_Core_Model_Abstract {

    const SQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

    const MANDATE_ID_FORMAT_INCREMENT = 9;
    const MANDATE_ID_FORMAT_UNIXTIME = 18;
    const MANDATE_ID_FORMAT_DATETIME17 = 17;
    const MANDATE_ID_FORMAT_DATETIME19 = 19;
    const MANDATE_ID_FORMAT_DATETIME23 = 32;

    const ACCOUNT_DISPLAY_MODE_FULL = 0;
    const ACCOUNT_DISPLAY_MODE_PARTIAL = 1;
    const ACCOUNT_DISPLAY_MODE_HIDE = 2;

    protected $_helper;
    protected $_documentData;
    protected $_documentHtml;

    protected $_debitor;
    protected $_creditor;

    protected $_bic;
    protected $_iban;
    protected $_accountHolder;
    protected $_displayPrepared;

    protected function _construct() {
        $this->_init('xonu_directdebit/mandate');
    }

    /**
     * prepare bank account details for display in checkout and account
     */
    protected function prepareDisplay() {
        if(!isset($this->_displayPrepared)) {
            $displayMode = (int)Mage::getStoreConfig('xonu_directdebit/mandate/account_display_mode');

            // $storeId = Mage::app()->getStore()->getStoreId();
            // if($displayMode == self::ACCOUNT_DISPLAY_MODE_FULL || $storeId == 0) {

            if($displayMode == self::ACCOUNT_DISPLAY_MODE_FULL) {
                $documentData = $this->getDocumentData();
                $this->_bic = $documentData['debitor']['bic'];
                $this->_iban = $this->beautifyIban($documentData['debitor']['iban']);
                $this->_accountHolder = $documentData['debitor']['account_holder'];
            } elseif($displayMode == self::ACCOUNT_DISPLAY_MODE_PARTIAL) {
                $documentData = $this->getDocumentData();
                $this->_bic = $documentData['debitor']['bic'];
                $this->_iban = str_repeat('* ', 10).''.substr($documentData['debitor']['iban'], -3);
            }
            $this->_displayPrepared = true;
        }
    }
    public function getBic() { $this->prepareDisplay(); return $this->_bic; }
    public function getIban() { $this->prepareDisplay(); return $this->_iban; }
    public function getAccountHolder() { $this->prepareDisplay(); return $this->_accountHolder; }


    public function createMandateIdentifier($format = false) {

        if(!$format) $format = Mage::getStoreConfig('xonu_directdebit/mandate/identifier_format');

        list($micro, $sec) = explode(" ", microtime()); unset($sec); // get microseconds
        $sec = Mage::getModel('core/date')->timestamp(time()); // time zone shift

        switch($format)
        {
            case self::MANDATE_ID_FORMAT_UNIXTIME: // unix timestamp with microseconds (18 chars)
                return $sec . str_replace('0.', '', $micro);

            case self::MANDATE_ID_FORMAT_DATETIME17: // date and time concatenated (17 chars)
                return date('YmdHis', $sec). substr($micro, 2, 3);

            case self::MANDATE_ID_FORMAT_DATETIME19: // date and time separated (19 chars)
                return date('Ymd-His-', $sec). substr($micro, 2, 3);

            case self::MANDATE_ID_FORMAT_DATETIME23: // date and time separated (23 chars)
                return date('Y-m-d-H-i-s-', $sec). substr($micro, 2, 3);

            /*
            case self::MANDATE_ID_FORMAT_INCREMENT: // sequential numbers similar to order numbers (9 chars)
                $eavIncrement = Mage::getModel('eav/entity_increment_numeric');
                $eavIncrement->setPrefix($this->getStoreId());
                $eavIncrement->setLastId($this->getLastInsertId());
                return $eavIncrement->getNextId();
            */
        }
    }

    public function getReadConnection() {
        $resource = Mage::getSingleton('core/resource');
        $this->setMainTable($resource->getTableName('xonu_directdebit/mandate'));
        return $resource->getConnection('core_read');
    }


    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_directdebit');
        return $this->_helper;
    }

    /**
     * @param $billing
     * @param $payment
     * @return array
     */
    protected function _getDebitorData($billing, $payment) {
        $b = $billing; $p = $payment;

        $debitorData = array(
            'account_holder' => $p->getSepaHolder(),
            'company' => trim($b->getCompany()),
            'address' => join(",\n", $b->getStreet()),
            'postcode' => $b->getPostcode(),
            'city' => $b->getCity(),
            'country' => Mage::app()->getLocale()->getCountryTranslation($b->getCountry()),
            'iban' => $p->getSepaIban(),
            'bic' => $p->getSepaBic(),
            'debitor' => trim($b->getFirstname()).' '.trim($b->getLastname())
        );
        if($debitorData['company'] == '') unset($debitorData['company']);

        return $debitorData;
    }

    /**
     * @return Varien_Object
     */
    public function getDebitor() {
        if(!isset($this->_debitor)) {
            $documentData = $this->getDocumentData();
            $this->_debitor = new Varien_Object();
            $this->_debitor->setData($documentData['debitor']);
        }
        return $this->_debitor;
    }

    /**
     * @return Varien_Object
     */
    public function getCreditor() {
        if(!isset($this->_creditor)) {
            $documentData = $this->getDocumentData();
            $this->_creditor = new Varien_Object();
            $this->_creditor->setData($documentData['creditor']);
        }
        return $this->_creditor;
    }

    public function getCreditorHtml() {
        $documentData = $this->getDocumentData();
        return $this->formatData($documentData['creditor']);
    }

    public function getDebitorHtml() {
        $documentData = $this->getDocumentData();
        return $this->formatData($documentData['debitor']);
    }

    /**
     * returns valid mandate object for the current logged in customer or false if not found
     * @return bool | Xonu_Directdebit_Model_Mandate
     */
    public function loadCustomerMandate() {
        $session = Mage::getSingleton('customer/session');

        if(Mage::app()->getStore()->isAdmin()) {

            $quote = Mage::getSingleton('adminhtml/session_quote');
            if($quote)
                return $this->loadByCustomerId($quote->getCustomerId());
        } else {

            if($session->isLoggedIn())
                return $this->loadByCustomerId($session->getCustomer()->getId());
        }

        return false;
    }

    /**
     * returns valid mandate object or false if not found
     * @param (int)$customerId
     * @return bool | Xonu_Directdebit_Model_Mandate
     */
    public function loadByCustomerId($customerId)
    {
        $mandateCollection = Mage::getModel('xonu_directdebit/mandate')
            ->getResource()
            ->getCollection()
            ->addFilter('customer_id', $customerId)
            ->addFilter('recurrent', 1)
            ->addFilter('revoked', 0)
            ->addFieldToFilter('last_order_created_at', array('lt' => $this->_getExpirationDate()))
            ->setOrder('last_order_created_at')
            ->setOrder('recurrent')
            ->load();

        foreach($mandateCollection as $mandate) {
            if($mandate->checkDocumentData() && $mandate->checkDocumentHtml()) {
                $this->setData($mandate->getData());
                return $this;
            } else {
                return false;
            }
        }

        return false;
    }

    public function createFromQuote($quote) { return $this->createFromOrder($quote, $isQuote = true); }
    public function createFromOrder($order, $isQuote = false) {

        $storeId    = $order->getStoreId(); $this->setStoreId($storeId);
        $billing    = $order->getBillingAddress();
        $payment    = $order->getPayment();
        $customerId = $order->getCustomerId() ? $order->getCustomerId() : null;

        $creditorInfo =  Mage::getStoreConfig('payment/xonu_directdebit/creditor_info', $storeId);
        $creditorIdentifier = Mage::getStoreConfig('payment/xonu_directdebit/creditor_identifier', $storeId);


        // mandate for one-off or recurrent payment
        $recurrent = 0; $templateConfigPath = 'xonu_directdebit/mandate/template_guest';
        if(!is_null($customerId) && Mage::getStoreConfigFlag('xonu_directdebit/mandate/recurrent_active', $storeId)) {

            $session = Mage::getSingleton('customer/session');
            $customer = $session->getCustomer();

            if(Mage::getStoreConfigFlag('xonu_directdebit/mandate/recurrent_allowallgroups', $storeId)) {
                $recurrent = 1; $templateConfigPath = 'xonu_directdebit/mandate/template';
            } else {
                $customerGroups = explode(',', Mage::getStoreConfig('xonu_directdebit/mandate/recurrent_specificgroup', $storeId));
                $customerGroupId = $customer->getCustomerGroupId();
                if($customerGroupId > 0 && in_array($customerGroupId, $customerGroups)) {
                    $recurrent = 1; $templateConfigPath = 'xonu_directdebit/mandate/template';
                }
            }
        }

        // mandate identifier
        if($this->getPreview()) {
            $mandateIdentifier = trim(Mage::getStoreConfig('xonu_directdebit/mandate/reference_notice', $storeId));
            if($mandateIdentifier == '') $mandateIdentifier = $this->_helper()->__('Will be communicated separately.');
        } else {
            $mandateIdentifier = $this->createMandateIdentifier();
        }

        // creditor and debitor data for the mandate document
        $creditorData = array(
            'creditor' => $creditorInfo,
            'creditor_identifier' => $creditorIdentifier,
        );
        $debitorData = $this->_getDebitorData($billing, $payment);

        // differing holder notice
        if(strtolower($debitorData['debitor']) != strtolower($debitorData['account_holder'])) {
            $differingHolderNotice = sprintf($this->_helper()->__('This SEPA Direct Debit Mandate is valid for the agreement with %s.'), $debitorData['debitor']);
        } else {
            $differingHolderNotice = '';
        }

        // mandate model data
        $documentData = base64_encode(serialize(array('creditor' => $creditorData, 'debitor' => $debitorData)));
        $data = array(
            'mandate_identifier' => $mandateIdentifier,
            'creditor_identifier' => $creditorIdentifier,
            'recurrent' => $recurrent,
            'store_id' => $storeId,
            'customer_id' => $customerId,
            'customer_email' => $order->getCustomerEmail(),
            'customer_firstname' => $billing->getFirstname(),
            'customer_lastname' => $billing->getLastname(),
            'document_data' => $documentData,
            'document_data_checksum' => md5($documentData),
            'differing_holder_notice' => $differingHolderNotice,
            'preview' => $this->getPreview(), 'is_preview' => $this->getPreview() // alias
        );
        // add data that is available in order but not in quote
        if(!$isQuote) {
            array_merge($data, array(
                'created_at' => $order->getCreatedAt(),
                'last_order_id' => $order->getId(),
                'last_order_created_at' => $order->getCreatedAt(),
            ));
        }
        $this->setData($data);

        try{ $paymentMethodInstance = $payment->getMethodInstance(); } catch(Exception $e) {}

        // mandate document
        $templateData = array(
            'quote'   => $order, // template might reference quote instead of order
            'order'   => $order,
            'billing' => $billing,
            'payment' => $paymentMethodInstance,

            'mandate' => $this,

            'creditor_html' => $this->getCreditorHtml(),
            'debitor_html'  => $this->getDebitorHtml(),

            'creditor' => $this->getCreditor(),
            'debitor'  => $this->getDebitor(),

            'grant_date' => $this->getLocalCreatedAt(),
        );
        $template = Mage::getModel('core/email_template');
        $templateId = Mage::getStoreConfig($templateConfigPath, $storeId);
        if(is_numeric($templateId)) {
            $template->load($templateId);
        } else {
            $localeCode = Mage::getStoreConfig('general/locale/code', $storeId);
            $template->loadDefault($templateId, $localeCode);
        }
        $document = $template->getProcessedTemplate($templateData);


        $documentCompressed = base64_encode(gzcompress($document, 9));
        $this->setData('document_html', $documentCompressed);
        $this->setData('document_html_checksum', md5($documentCompressed));

        return $this;
    }

    /**
     * @return array
     */
    public function getDocumentData() {
        if(!isset($this->_documentData)) $this->_documentData = unserialize(base64_decode($this->getData('document_data')));
        return $this->_documentData;
    }

    /**
     * @return string
     */
    public function getDocumentHtml() {
        if(!isset($this->_documentHtml)) $this->_documentHtml = gzuncompress(base64_decode($this->getData('document_html')));
        return $this->_documentHtml;
    }


    public function checkDocumentHtml() {
        $documentEncoded = $this->getData('document_html');
        if($documentEncoded == '') {
            return false;
        } else {
            $newChecksum = md5($documentEncoded);
            $savedChecksum = $this->getDocumentHtmlChecksum();
            return ($newChecksum == $savedChecksum);
        }
    }

    public function checkDocumentData() {
        $documentEncoded = $this->getData('document_data');
        if($documentEncoded == '') {
            return false;
        } else {
            $newChecksum = md5($documentEncoded);
            $savedChecksum = $this->getDocumentDataChecksum();
            return ($newChecksum == $savedChecksum);
        }
    }

    /**
     * get expiration date in the future starting from now
     * @return string
     */
    private function _getExpirationDate() {
        $periodOfValidity = Mage::getStoreConfig('xonu_directdebit/mandate/period_of_validity');
        $dateTime = new DateTime();
        $dateTime->setTimestamp(Mage::getModel('core/date')->timestamp(time()));
        $dateTime->add(new DateInterval('P'.$periodOfValidity.'M'));
        return $dateTime->format(self::SQL_DATETIME_FORMAT);
    }

    /**
     * get expiration date in the future starting from the last order date
     * @param string $format
     * @return string
     */
    public function getExpirationDate($format = self::SQL_DATETIME_FORMAT) {
        $periodOfValidity = Mage::getStoreConfig('xonu_directdebit/mandate/period_of_validity');
        $dateTime = new DateTime();
        $dateTime->setTimestamp(strtotime($this->getLastOrderCreatedAt()));
        $dateTime->add(new DateInterval('P'.$periodOfValidity.'M'));
        return $dateTime->format($format);
    }

    public function getLocalCreatedAt() {
        return Mage::helper('core')->formatDate($this->getData('created_at'), 'medium', false);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getCheckboxText($storeId = null) {
        $checkboxText = trim(Mage::getStoreConfig('xonu_directdebit/mandate/checkbox_label', $storeId));
        if($checkboxText == '') $checkboxText = $this->_helper()->__('I hereby grant the SEPA Direct Debit Mandate');
        return $checkboxText;
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    public function save($sendMandateMail = false) {
        if(!$this->getPreview()) { // prevent saving empty or preview mandate
            $storeId = $this->getStoreId();

            if(Mage::getStoreConfigFlag('xonu_directdebit/email/active', $storeId) && $sendMandateMail) {
                $this->sendMandateEmail();
            }

            $this->setUpdatedAt($this->getGlobalTimestampSql());
            return parent::save();
        }
        return $this;
    }


    /**
     * @return $this
     */
    protected function sendMandateEmail()
    {
        // $storeId = Mage::app()->getStore()->getId();
        $storeId = $this->getStoreId();

        // get the destination email addresses to send copies to
        $copyTo = $this->_getEmails('xonu_directdebit/email/receiver', $storeId);
        $copyMethod = Mage::getStoreConfig('xonu_directdebit/email/copymethod', $storeId);

        // retrieve corresponding customer name
        $customerName = $this->getCustomerFirstname().' '.$this->getCustomerLastname();

        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($this->getCustomerEmail(), $customerName);
        if ($copyTo && $copyMethod == 'bcc') {
            // add bcc to customer email
            foreach ($copyTo as $email) {
                $emailInfo->addBcc($email);
            }
        }
        $mailer->addEmailInfo($emailInfo);

        // email copies are sent as separated emails if their copy method is 'copy'
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        // set all required params and send emails
        $documentData = $this->getDocumentData();

        $creditor = new Varien_Object(); $creditor->setData($documentData['creditor']);
        $debitor  = new Varien_Object();  $debitor->setData($documentData['debitor']);

        $templateData = array(
            'mandate' => $this,
            'mandate_checkbox' => $this->getCheckboxText(),
            'mandate_content'  => $this->getDocumentHtml(),

            'creditor_html' => $this->getCreditorHtml(),
            'debitor_html'  => $this->getDebitorHtml(),

            'creditor' => $this->getCreditor(),
            'debitor'  => $this->getDebitor(),

            'grant_date' => $this->getLocalCreatedAt(),
        );

        /*
        if($this->getRecurrent())
            $templateConfigPath = 'xonu_directdebit/email/template';
        else
            $templateConfigPath = 'xonu_directdebit/email/template_guest';
        */

        $templateConfigPath = 'xonu_directdebit/email/template';

        $templateId = Mage::getStoreConfig($templateConfigPath, $storeId);
        $mailer->setSender(Mage::getStoreConfig('xonu_directdebit/email/sender', $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams($templateData);

        $mailer->send();

        return $this;
    }

    /**
     * used in sendMandateEmail to convert the list of receiver emails
     * @param $configPath
     * @param $storeId
     * @return array | bool
     */
    protected function _getEmails($configPath, $storeId)
    {
        $data = Mage::getStoreConfig($configPath, $storeId);
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }

    /**
     * @param string $format
     * @return string
     */
    public function getLocalTimestampSql($format = self::SQL_DATETIME_FORMAT) {
        return date($format, Mage::getModel('core/date')->timestamp(time()));
    }

    /**
     * @param string $format
     * @return string
     */
    public function getGlobalTimestampSql($format = self::SQL_DATETIME_FORMAT) {
        return date($format, time());
    }


    /**
     * inserts spaces every second or fourth character if the iban
     * @param $iban
     * @return mixed
     */
    public function beautifyIban($iban) {
        $ibanDisplayMode = (int)Mage::getStoreConfig('xonu_directdebit/iban/display_iban_separated');
        if($ibanDisplayMode == 1 || $ibanDisplayMode == 2)
            return preg_replace('/.{'.($ibanDisplayMode*2).'}/', '$0 ', $iban);
        else
            return $iban;
    }

    /**
     * converts associative array into an html table und translated key values
     * @param $data
     * @param bool $displayLabels
     * @return string
     */
    public function formatData($data, $displayLabels = true) {
        $output = '';

        // hide debitor equal account holder
        if(isset($data['account_holder']) && $data['account_holder'] == $data['debitor'])
            unset($data['debitor']);

        if($displayLabels) {
            $output .= '<table class="mt" border="0" cellspacing="0" cellpadding="0">'."\n";
            foreach($data as $label => $value) {
                // convert key to label
                if($label == 'bic' || $label == 'iban')
                    $label = strtoupper($label);
                else
                    $label = ucwords(str_replace('_', ' ', $label));

                // translate label
                $label = $this->_helper()->__($label);

                $output .= "<tr><td nowrap>$label</td><td>&nbsp;</td><td nowrap>$value</td></tr>\n";
            }
            $output .= "</table>\n";
        } else {
            foreach($data as $value) {
                $output .= $value."<br/>\n";
            }
        }
        return $output;
    }
}