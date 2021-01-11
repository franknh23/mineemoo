<?php

class mcpay_core
{
    var $version = '1.2.4';

    var $mcpayAPIURL = '';
    var $mcpayAPIURLCCard = 'https://sipg.micropayment.de/public/creditcard/v2.3/nvp/';
    var $mcpayAPIURLCCard3D = 'https://sipg.micropayment.de/public/creditcard/v2.3/nvp/';
    var $mcpayBridgeURL = 'https://sipg.micropayment.de/public/bridge/v1/mp.js';
    var $mcpayAPIURLSepa = 'https://webservices.micropayment.de/public/debit/v2.3/nvp/';
    var $mcpayAPIURLPrepay = 'https://webservices.micropayment.de/public/prepay/v1.1/nvp/';

    var $mcpayEventURLs = array(
        'ccard' => 'https://creditcard.micropayment.de/creditcard/event/',
        'sepa' => 'https://sepadirectdebit.micropayment.de/lastschrift/event/',
        'sofort' => 'https://directbanking.micropayment.de/sofort/event/',
        'obt' => 'https://directbanking.micropayment.de/obt/event/',
        'prepay' => 'https://prepayment.micropayment.de/prepay/event/',
        'call2pay' => 'https://call2pay.micropayment.de/call2pay/event/',
        'handypay' => 'https://mobilepayment.micropayment.de/handypay/event/',
        'paysafecard' => 'https://paysafecard.micropayment.de/paysafecard/event/',
        'paypal' => 'https://paypal.micropayment.de/paypal/event/',
        'paydirekt' => 'https://paydirekt.micropayment.de/paydirekt/event/',
    );

    var $logoURLs = array(
        'visa' => 'https://resources.micropayment.de/download/shop-plugins/payment-logos/visa.png',
        'mastercard' => 'https://resources.micropayment.de/download/shop-plugins/payment-logos/mastercard.png',
        'amex' => 'https://www.micropayment.de/static/img/content/products/creditcard/logo-amex-small.svg',
        'ec' => 'https://resources.micropayment.de/download/shop-plugins/payment-logos/sepa.png',
    );

    var $iconURLs = array(
        'ccard' => 'https://www.micropayment.de/resources/?what=img&group=cc&show=type-o.1',
        'sepa' => 'https://www.micropayment.de/resources/?what=img&group=dbt&show=type-o.1',
        'prepay' => 'https://www.micropayment.de/resources/?what=img&group=pp&show=type-o.1',
        'sofort' => 'https://www.micropayment.de/resources/?what=img&group=klarna-sofort&show=type-o.1',
        'paypal' => 'https://www.micropayment.de/resources/?what=img&group=ppl&show=type-o.1',
        'paysafecard' => 'https://www.micropayment.de/resources/?what=img&group=psc&show=type-o.1',
        'call2pay' => 'https://www.micropayment.de/resources/?what=img&group=c2p&show=type-o.1',
        'handypay' => 'https://www.micropayment.de/resources/?what=img&group=hp&show=type-o.1',
        'paydirekt' => 'https://www.micropayment.de/resources/?what=img&group=pdk&show=type-o.1',
    );

    var $accessKey = '';
    var $testMode = '1';
    var $token = '';
    var $doublet = '1';
    var $project = '';
    var $sendMail = TRUE;
    var $error_color = "#ffa0a0";
    var $error_off_color = "#fff";
    var $plugin_path = '';
    var $plugin_url = '';
    var $plugin_urljs = '';
    var $plugin_urlcss = '';
    var $prefixCCard = 'mcpay_card_';
    var $prefixSepa = 'mcpay_sepa_';
    var $customPrefix = ''; // prefix for customer id to keep it unique for project instead of account level
    var $log; // logging obj
    var $paymethod = 'ccard';
    var $debug = FALSE;

    var $availablePaymethods = array(
        'ccard',
        'sepa',
        'prepay',
        'sofort',
        'paysafecard',
        'paypal',
        'call2pay',
        'handypay',
        'paydirekt',
    );
    var $subscriptionPaymethods = array(
        'ccard',
        'sepa',
    );
    var $onlyVRProductsPaymethods = array(
        'call2pay',
        'handypay',
    );

    // source: https://www.postbank.de/privatkunden/themenwelten/artikel_sepa-laender-diese-nationen-machen-mit.html
    var $allowedSEPACountries = array(
        'BE', 'BG', 'DK', 'DE', 'EE',
        'FI', 'FR', 'GR', 'GB', 'IE',
        'IS', 'IT', 'HR', 'LV', 'LI',
        'LT', 'LU', 'MT', 'MC', 'NL',
        'NO', 'AT', 'PL', 'PT', 'RO',
        'SM', 'SE', 'CH', 'SK', 'SI',
        'ES', 'CZ', 'HU', 'CY',
    );
    var $allowedSOFORTCountries = array(
        'DE', 'AT', 'CH', 'ES', 'IT',
        'NL', 'BE', 'PL',
    );
    var $supportedCurrencies = array(
        'EUR', 'GBP', 'USD', 'SEK', 'JPY',
        'HKD', 'NOK', 'NZD', 'AUD', 'CAD',
        'PLN', 'CZK', 'HUF', 'CHF', 'AED',
        'CLP', 'MXN', 'SAR', 'TRY', 'EGP',
        'DKK', 'BRL', 'RUB', 'RON', 'INR',
        'HRK', 'COP', 'PEN', 'VEF', 'ARS',
        'KZT', 'UAH', 'ZAR', 'MAD', 'ILS',
        'KRW', 'LTL', 'LVL', 'MYR', 'SGD',
        'THB', 'IDR',
    );

    /**
     * mcpay_paycore constructor.
     *
     * @param int $testMode
     */
    public function __construct($testMode = 1, $debugLog = NULL)
    {
        $this->testMode = $testMode;
        // default ccard
        $this->switchPaymethod('ccard');

        if (!empty($debugLog)) {
            $this->log = new mcpayLog($debugLog);
        } else {
            $this->log = new mcpayLog(MCPAY_BASE_PATH . 'logs/mcpay.log');
        }
        if ($testMode) {
            $this->debug = TRUE;
        }
    }

    /**
     * setConfig
     *
     * @param $cfg
     *
     * @return bool
     */
    public function setConfig($cfg)
    {
        if (empty($cfg)) return FALSE;
        if (!is_array($cfg)) return FALSE;

        foreach ($cfg AS $k => $v) {
            if (isset($this->$k)) {
                if ($k == 'testMode') {
                    $this->$k = (bool)$v;
                } else if ($k == 'debug') {
                    $this->$k = (bool)$v;
                } else {
                    $this->$k = $v;
                }
            }
        }
        return TRUE;
    }

    /**
     * invalidCFG
     *
     * @param bool $withToken
     *
     * @return bool
     * @throws \Exception
     */
    private function invalidCFG($withToken = TRUE)
    {
        $mandatory = array('accessKey', 'project', 'token');
        if (!$withToken) {
            $mandatory = array('accessKey', 'project');
        }
        foreach ($mandatory AS $k) {
            if (empty($this->$k)) throw new Exception($k . ' cant be empty');
        }
        return FALSE;
    }

    /**
     * doCall
     *
     * @param      $url
     * @param      $data
     * @param bool $urlencode
     * @param int $returnHeader
     *
     * @return mixed
     * @throws \Exception
     */
    public function doCall($url, $data, $urlencode = TRUE, $returnHeader = 0)
    {
        $pString = '';
        $act = '';
        foreach ($data AS $k => $v) {
            if ($k == 'action') $act = $v;
            if (is_array($v)) {
                foreach ($v AS $kk => $vv) {
                    if ($urlencode) {
                        $pString .= '&' . $k . '[' . $kk . ']=' . urlencode($vv);
                    } else {
                        $pString .= '&' . $k . '[' . $kk . ']=' . $vv;
                    }
                }
            } else {
                if ($urlencode) {
                    $pString .= '&' . $k . '=' . urlencode($v);
                } else {
                    $pString .= '&' . $k . '=' . $v;
                }
            }
        }

        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, $returnHeader);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $pString);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_USERAGENT, "gwreq");

            $res = curl_exec($ch);
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);

            //mail('te@st.de', __CLASS__.'->'.__FUNCTION__.'->data '.$act, $url."\n\n".print_r($pString,1)."\n\n".print_r($res,1));

            if (!$res && $error) {
                throw new Exception('CURL Error (' . $act . '): ' . $error, $errno);
            }

        } else {
            throw new Exception('No CURL installed');
        }

        return $res;
    }

    /**
     * parseResult
     *
     * @param $res
     *
     * @return \stdClass
     * @throws \Exception
     */
    private function parseResult($res)
    {
        $retObj = new stdClass();
        $raw = str_replace("\n", "&", $res);
        parse_str($raw, $rows);
        //mail('te@st.de', __CLASS__.'->'.__FUNCTION__, print_r($rows,1));

        foreach ($rows AS $k => $v) {
            $retObj->$k = $v;
        }
        if ($retObj->error > 0) {
            $this->log->log(__FUNCTION__, $retObj);
            throw new Exception($retObj->errorMessage, $retObj->error);
        }
        return $retObj;
    }

    /**
     * tokenSessionCreate
     *
     * @param $token
     * @param $custObj
     * @param $bookObj
     *
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function tokenSessionCreate($token, $custObj, $bookObj)
    {
        try {
            $data = $this->getBaseData(__FUNCTION__, 'token', $token);
        } catch (Exception $e) {
            throw $e;
        }
        if (!$data) return FALSE;

        $data['firstname'] = $custObj->firstname; // Vorname des Kunden
        $data['surname'] = $custObj->lastname; // Nachname des Kunden
        $data['doublet'] = (int)$this->doublet; // Dublette erlauben, d.h. eine Kreditkartennummer kann von mehreren
        // Customern verwendet werden. Wenn 0, dann scheitert die Funktion, siehe code 4105
        $data['email'] = $custObj->email; // E-Mail-Adresse des Kunden, wenn nach den Transaktionen einen
        // E-Mail an der Kunden versand werden soll
        $data['culture'] = $custObj->lang; // Sprache & Land des Kunden | gültige Beispielwerte sind 'de', 'de-DE', 'en-US'
        $data['customerId'] = NULL; // eigene eindeutige ID des Kunden, wird anderenfalls erzeugt [min./max. Zeichen 10/40, alphanumerisch]
        $data['freeParams'] = $custObj->payload; // Liste mit freien Parametern, die dem Kunden zugeordnet werden
        $data['project'] = $this->project; // Projektkürzel für den Vorgang
#		$data['projectCampaign'] 		= ''; // ein Kampagnenkürzel des Projektbetreibers
#		$data['account'] 						= ''; // Account des beteiligten Webmasters sonst eigener - setzt eine Aktivierung der Webmasterfähigkeit
        // des Projekts vorraus - Hinweis: Webmasterfähigkeit steht momentan nicht zur Verfügung
#		$data['webmasterCampaign'] 	= ''; // ein Kampagnenkürzel des Webmasters
        $data['amount'] = $bookObj->amount; // abzurechnender Betrag, wird kein Betrag übergeben, wird der Betrag aus der Konfiguration verwendet
        $data['currency'] = $bookObj->currency; // 'EUR'	Währung
        $data['title'] = $bookObj->title; // Bezeichnung der zu kaufenden Sache - Verwendung in Falle einer auftretenden Benachrichtigung
        // wird dieser Wert als Produktidentifizierung mit geschickt, wird kein Wert übergeben,
        // wird Der aus der Konfiguration verwendet (max 150 Zeichen)
        $data['paytext'] = $bookObj->paytext; // Bezeichnung der zu kaufenden Sache - Verwendung beim Mailversand, sollten Sie Diesen wünschen
        $data['ip'] = $custObj->ip; // IPv4 des Benutzers
        $data['sendMail'] = (bool)$this->sendMail; // mail vom Payment verschicken bool
        $data['freeParamsSession'] = $bookObj->payload; // Liste mit freien Parametern, die dem Vorgang zugeordnet werden
#		$data['sessionId'] 					= ''; // eigene eindeutige ID des Vorgangs, wird anderenfalls erzeugt [max. 40 Zeichen]
        if (!$bookObj->autogenSession) {
            $data['sessionId'] = $bookObj->orderID; // order id to identify trx
        }
        $data['update'] = '2'; // bei bestehendem Customer: 0=error, 1=update aller Parameter, 2=update nur von gesetzten Parametern
        if (!empty($custObj->id)) {
            $data['customerId'] = $custObj->id;
        }
        //mail('test@test.test', 'DEBUG1 '.__CLASS__.'->'.__FUNCTION__, print_r($data, 1));
        $res = $this->doCall($this->mcpayAPIURL, $data);
        //mail('test@test.test', 'DEBUG2 '.__CLASS__.'->'.__FUNCTION__, print_r($res, 1));
        return $this->parseResult($res);
    }

    /**
     * tokenCustomerCreate
     *
     * @param $token
     * @param $custObj
     *
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function tokenCustomerCreate($token, $custObj)
    {
        try {
            $data = $this->getBaseData(__FUNCTION__, 'token', $token);
        } catch (Exception $e) {
            throw $e;
        }
        if (!$data) return FALSE;

        $data['firstname'] = $custObj->firstname; // Vorname des Kunden
        $data['surname'] = $custObj->lastname; // Nachname des Kunden
        $data['doublet'] = (int)$this->doublet; // Dublette erlauben, d.h. eine Kreditkartennummer kann von mehreren
        // Customern verwendet werden. Wenn 0, dann scheitert die Funktion, siehe code 4105
        $data['email'] = $custObj->email; // E-Mail-Adresse des Kunden, wenn nach den Transaktionen einen
        // E-Mail an der Kunden versand werden soll
        $data['culture'] = $custObj->lang; // Sprache & Land des Kunden | gültige Beispielwerte sind 'de', 'de-DE', 'en-US'
        $data['customerId'] = NULL; // eigene eindeutige ID des Kunden, wird anderenfalls erzeugt [min./max. Zeichen 10/40, alphanumerisch]
        $data['freeParams'] = $custObj->payload; // Liste mit freien Parametern, die dem Kunden zugeordnet werden
#		$data['update'] 						= ''; // bei bestehendem Customer: 0=error, 1=update aller Parameter, 2=update nur von gesetzten Parametern
        if (!empty($custObj->id)) {
            $data['customerId'] = $custObj->id;
        }
        //mail('test@test.test', 'DEBUG1 '.__CLASS__.'->'.__FUNCTION__, print_r($data, 1));
        $res = $this->doCall($this->mcpayAPIURL, $data);
        //mail('test@test.test', 'DEBUG2 '.__CLASS__.'->'.__FUNCTION__, print_r($res, 1));
        return $this->parseResult($res);
    }

    /**
     * sessionCreate
     *
     * @param $custObj
     * @param $bookObj
     *
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function sessionCreate($custObj, $bookObj)
    {
        try {
            $data = $this->getBaseData(__FUNCTION__, 'customerId', $custObj->id); // use shop-shortcode as prefix
        } catch (Exception $e) {
            throw $e;
        }
        //mail('test@test.test', 'DEBUG0 '.__CLASS__.'->'.__FUNCTION__, print_r($data, 1));
        if (!$data) return FALSE;

        if (in_array($this->paymethod, array('prepay'))) {
            $data['sessionMode'] = 'EXPIRE'; // Modus EXPIRE oder PERMANENT
            $data['sessionId'] = $bookObj->orderID; // order id to identify trx
            if (!empty($bookObj->sessionID)) {
                $data['sessionId'] = $bookObj->sessionID;
            }
        }
        //$data['customerId'] 			= $custObj->id; // eigene eindeutige ID des Kunden, wird anderenfalls erzeugt [min./max. Zeichen 10/40, alphanumerisch]
        // set order id here per account use shop-shortcode as prefix
#		$data['sessionId'] 					= ''; // eigene eindeutige ID des Vorgangs, wird anderenfalls erzeugt [max. 40 Zeichen]
        $data['project'] = $this->project; // Projektkürzel für den Vorgang
#		$data['projectCampaign'] 		= ''; // ein Kampagnenkürzel des Projektbetreibers
#		$data['account'] 						= ''; // Account des beteiligten Webmasters sonst eigener - setzt eine Aktivierung der Webmasterfähigkeit
        // des Projekts vorraus - Hinweis: Webmasterfähigkeit steht momentan nicht zur Verfügung
#		$data['webmasterCampaign'] 	= ''; // ein Kampagnenkürzel des Webmasters
        $data['amount'] = $bookObj->amount; // abzurechnender Betrag, wird kein Betrag übergeben, wird der Betrag aus der Konfiguration verwendet
        $data['currency'] = $bookObj->currency; // 'EUR'	Währung
        $data['title'] = $bookObj->title; // Bezeichnung der zu kaufenden Sache - Verwendung in Falle einer auftretenden Benachrichtigung
        // wird dieser Wert als Produktidentifizierung mit geschickt, wird kein Wert übergeben,
        // wird Der aus der Konfiguration verwendet
        $data['paytext'] = $bookObj->paytext; // Bezeichnung der zu kaufenden Sache - Verwendung beim Mailversand, sollten Sie Diesen wünschen
        if (in_array($this->paymethod, array('prepay'))) {
            $data['expireDays'] = '21'; // Ablauf der Session in Tagen, genauer Ablauf wird als $expireDate zurückgegeben
            if (!empty($bookObj->expireDays)) {
                $data['expireDays'] = $bookObj->expireDays;
            }
        }
        $data['ip'] = $custObj->ip; // IPv4 des Benutzers
        $data['freeParams'] = $bookObj->payload; // Liste mit freien Parametern, die dem Vorgang zugeordnet werden
        if (in_array($this->paymethod, array('ccard'))) {
            $data['sendMail'] = (bool)$this->sendMail; // mail vom Payment verschicken bool
            if (!$bookObj->autogenSession) {
                $data['sessionId'] = $bookObj->orderID; // order id to identify trx
            }
        }
        if (in_array($this->paymethod, array('sepa'))) {
            $data['mandateRef'] = $bookObj->mandateRef; // SEPA Mandats Referenz, wird ansonsten erzeugt
            $data['mandateSignDate'] = $bookObj->mandateSignDate; // Datum der Mandatserteilung, ansonsten sessionApprove-Datum
            $data['mandateRecur'] = $bookObj->mandateRecur; // SEPA Mandat für einzelne - Standard für neue Mandate: "ONEOFF", oder wiederkehrende Zahlungen: "RECURRING" oder "FINAL"
            $data['sessionId'] = $bookObj->orderID; // order id to identify trx
            if (!empty($bookObj->sessionID)) {
                $data['sessionId'] = $bookObj->sessionID;
            }
        }

        //mail('test@test.test', 'DEBUG1 '.__CLASS__.'->'.__FUNCTION__, print_r($data, 1));
        $res = $this->doCall($this->mcpayAPIURL, $data);
        //mail('test@test.test', 'DEBUG2 '.__CLASS__.'->'.__FUNCTION__, print_r($res, 1));
        return $this->parseResult($res);
    }

    /**
     * sessionApprove
     *
     * @param $sessionId
     *
     * @return array|\stdClass
     * @throws \Exception
     */
    public function sessionApprove($sessionId)
    {
        return $this->getBaseData(__FUNCTION__, 'sessionId', $sessionId, TRUE);
    }

    /**
     * sessionRefund
     *
     * @param      $sessionId
     * @param      $amount
     * @param null $payText
     *
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function sessionRefund($sessionId, $amount, $payText = NULL)
    {
        $data = $this->getBaseData(__FUNCTION__, 'sessionId', $sessionId);
        if (!$data) return FALSE;

        $data['amount'] = $amount; // Überweisungsbetrag in EUR-Cent, stdm. wird der gesamte eingegangene Betrag überwiesen
        $data['payText'] = $payText; // Buchungstext, stdm. wird der ursprüngliche Buchungstext verwendet

        $res = $this->doCall($this->mcpayAPIURL, $data);
        return $this->parseResult($res);
    }

    /**
     * sessionRefundTest
     * test method for prepay
     *
     * @param $sessionId
     * @param $bookObj
     *
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function sessionRefundTest($sessionId, $bookObj)
    {
        if (in_array($this->paymethod, array('prepay'))) {
            return $this->getBaseData(__FUNCTION__, 'sessionId', $sessionId, TRUE);
        }
        return FALSE;
    }

    /**
     * sessionRefundHelper
     * helper method for testing sepa
     *
     * @param $sessionId
     * @param $bookObj
     *
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function sessionRefundHelper($sessionId, $bookObj)
    {
        if (!in_array($this->paymethod, array('sepa'))) {
            return FALSE;
        }
        $amount = $bookObj->amount;
        $payText = $bookObj->payText;
        return $this->sessionRefund($sessionId, $amount, $payText);
    }

    /**
     * customerCreate
     *
     * @param $custObj
     *
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function customerCreate($custObj)
    {
        try {
            $data = $this->getBaseData(__FUNCTION__, 'customerId', $custObj->id);
        } catch (Exception $e) {
            throw $e;
        }
        if (!$data) return FALSE;

        //$data['customerId'] 				= NULL; // eigene eindeutige ID des Kunden, wird anderenfalls erzeugt [min./max. Zeichen 10/40, alphanumerisch]
        $data['freeParams'] = $custObj->payload; // Liste mit freien Parametern, die dem Kunden zugeordnet werden
        // only specific params
        if (in_array($this->paymethod, array('ccard'))) {
            $data['firstname'] = $custObj->firstname; // Vorname des Kunden
            $data['surname'] = $custObj->lastname; // Nachname des Kunden
            $data['email'] = $custObj->email; // E-Mail-Adresse des Kunden, wenn nach den Transaktionen einen
            // E-Mail an der Kunden versand werden soll
            $data['culture'] = $custObj->lang; // Sprache & Land des Kunden | gültige Beispielwerte sind 'de', 'de-DE', 'en-US'
#		  $data['update'] 					= ''; // bei bestehendem Customer: 0=error, 1=update aller Parameter, 2=update nur von gesetzten Parametern
        }
        if (in_array($this->paymethod, array('sepa', 'prepay'))) {
            //$data['update'] 					= '1';
        }
        /*
        if (!empty($custObj->id)){
          $data['customerId'] 			= $custObj->id;
        }
        */
        //mail('test@test.test', 'DEBUG1 '.__CLASS__.'->'.__FUNCTION__, print_r($data, 1));
        $res = $this->doCall($this->mcpayAPIURL, $data);
        //mail('test@test.test', 'DEBUG2 '.__CLASS__.'->'.__FUNCTION__, print_r($res, 1));
        return $this->parseResult($res);
    }

    /**
     * bankaccountSet
     *
     * @param $custObj
     * @param $bookObj
     *
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function bankaccountSet($custObj, $bookObj)
    {
        try {
            $data = $this->getBaseData(__FUNCTION__, 'customerId', $custObj->id);
        } catch (Exception $e) {
            throw $e;
        }
        if (!$data) return FALSE;

        $data['iban'] = $bookObj->iban; // iban	string	nein	[NULL] 	IBAN
        $data['bic'] = $bookObj->bic; // bic	string	nein	[NULL] 	BIC, opt. bei deutscher IBAN
        $data['country'] = $bookObj->country; // country	string	nein	'DE'	Sitz der Bank, wenn IBAN leer ist
        //$data['bankCode'] = $bookObj->; // bankCode	string	nein	[NULL] 	Bankleitzahl, wenn IBAN leer ist
        //$data['accountNumber'] = $bookObj->; // accountNumber	string	nein	[NULL] 	Kontonummer, wenn IBAN leer ist
        $data['accountHolder'] = $bookObj->holder; // accountHolder	string	ja	nein	Kontoinhaber
        //$data['holderAddress'] = $bookObj->; // holderAddress	string	nein	[NULL] 	Kontoinhaber Adresse
        //$data['holderZip'] = $bookObj->; // holderZip	string	nein	[NULL] 	Kontoinhaber Postleitzahl
        //$data['holderCity'] = $bookObj->; // holderCity	string	nein	[NULL] 	Kontoinhaber Ort
        //$data['holderCountry'] = $bookObj->; // holderCountry	string	nein	[NULL] 	Kontoinhaber Land

        //mail('test@test.test', 'DEBUG1 '.__CLASS__.'->'.__FUNCTION__, print_r($data, 1));
        $res = $this->doCall($this->mcpayAPIURL, $data);
        //mail('test@test.test', 'DEBUG2 '.__CLASS__.'->'.__FUNCTION__, print_r($res, 1));
        return $this->parseResult($res);
    }

    /**
     * bankaccountGet
     *
     * @param $customerId
     *
     * @return array|\stdClass
     * @throws \Exception
     */
    public function bankaccountGet($customerId)
    {
        return $this->getBaseData(__FUNCTION__, 'customerId', $customerId, TRUE);
    }


    /**
     * transactionPurchase
     *
     * @param      $sessionId
     * @param int $fraudDetection
     * @param int $avsCheck
     * @param int $recurring
     * @param null $returnURL
     * @param int $start3d
     *
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function transactionPurchase($sessionId, $fraudDetection = 1, $avsCheck = 0, $recurring = 1, $returnURL = NULL, $start3d = 0)
    {
        $data = $this->getBaseData(__FUNCTION__, 'sessionId', $sessionId);
        if (!$data) return FALSE;

#		$data['cvc2'] 					= ''; // CVC2-Code der Kreditkarte, muß min einmal pro Kreditkarte/Verfallszeit angegeben worden sein
        $data['fraudDetection'] = $fraudDetection; // de/aktiviert FraudDetection
        $data['avsCheck'] = $avsCheck; // de/aktiviert das AddressVerificationSystem
        $data['recurring'] = $recurring; // Wenn 1 und account dafür freigeschaltet ist, kann der Vorgang als Teil einer Zahlungsfolge markiert werden.
        $data['returnUrl'] = $returnURL; // Url an die nach Abschluß des 3D-Secure Verfahrens weitergeleitet werden soll.
        $data['start3d'] = $start3d; // Wenn 1 wird der 3dSecure Vorgang gestartet. Entsprechender Response muss beachtet werden.

        $res = $this->doCall($this->mcpayAPIURL, $data);
        return $this->parseResult($res);
    }

    /**
     * transactionRefund
     *
     * @param      $sessionId
     * @param      $transactionId
     * @param      $amount
     * @param null $currency
     * @param int $fraudDetection
     *
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function transactionRefund($sessionId, $transactionId, $amount, $currency = NULL, $fraudDetection = 1)
    {
        $data = $this->getBaseData(__FUNCTION__, 'sessionId', $sessionId);
        if (!$data) return FALSE;

        $data['transactionId'] = $transactionId; //Transaktionsnummer der Transaktion die zurückgebucht werden soll
        $data['amount'] = $amount; //zurückzubuchender Betrag, falls abweichend von Orginaltransaktion
        $data['currency'] = $currency; //Währung, falls abweichend von Originaltransaktion [nur relevant wenn `amount` abweichend]
        $data['fraudDetection'] = $fraudDetection; // de/aktiviert FraudDetection

        $res = $this->doCall($this->mcpayAPIURL, $data);
        return $this->parseResult($res);
    }

    /**
     * transactionRefundTest
     * helper method for testing
     *
     * @param $transactionId
     * @param $bookObj
     *
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function transactionRefundTest($transactionId, $bookObj)
    {
        $sessionId = $bookObj->sessionId;
        $amount = $bookObj->amount;
        $currency = $bookObj->currency;
        $fraudDetection = $bookObj->fraudDetection;
        return $this->transactionRefund($sessionId, $transactionId, $amount, $currency, $fraudDetection);
    }

    /**
     * transactionAuthorization
     *
     * @param $sessionId
     * @param int $fraudDetection
     * @param int $avsCheck
     * @param int $start3d
     * @param null $returnURL
     * @return bool|stdClass
     * @throws Exception
     */
    public function transactionAuthorization($sessionId, $fraudDetection = 1, $avsCheck = 0, $start3d = 0, $returnURL = NULL)
    {
        $data = $this->getBaseData(__FUNCTION__, 'sessionId', $sessionId);
        if (!$data) return FALSE;

#		$data['cvc2'] 					= ''; // CVC2-Code der Kreditkarte, muß min einmal pro Kreditkarte/Verfallszeit angegeben worden sein
        $data['fraudDetection'] = $fraudDetection; // de/aktiviert FraudDetection
        $data['avsCheck'] = $avsCheck; // de/aktiviert das AddressVerificationSystem
        $data['start3d'] = $start3d; // Wenn 1 wird der 3dSecure Vorgang gestartet. Entsprechender Response muss beachtet werden.
        $data['returnUrl'] = $returnURL; // Url an die nach Abschluß des 3D-Secure Verfahrens weitergeleitet werden soll.

        $res = $this->doCall($this->mcpayAPIURL, $data);
        return $this->parseResult($res);
    }

    /**
     * transactionCapture
     *
     * @param $sessionId
     * @param $transactionId
     * @param null $amount
     * @param null $currency
     * @param int $fraudDetection
     * @param int $sendMail
     * @return bool|stdClass
     * @throws Exception
     */
    public function transactionCapture($sessionId, $transactionId, $amount = NULL, $currency = NULL, $fraudDetection = 1, $sendMail = 0)
    {
        $data = $this->getBaseData(__FUNCTION__, 'sessionId', $sessionId);
        if (!$data) return FALSE;

        $data['transactionId'] = $transactionId; // Transaktionsnummer von "transactionAuthorization"
        $data['amount'] = $amount; // [NULL] - entspricht Betrag aus Vorautorisierung | wenn abweichend, der zu buchende Betrag <= Betrag aus Vorautorisierung
        $data['currency'] = $currency; // Währung, falls abweichend von Originaltransaktion [nur relevant wenn `amount` abweichend]
        $data['fraudDetection'] = $fraudDetection; // de/aktiviert FraudDetection
        $data['sendMail'] = $sendMail; // wenn gesetzt, überschreibt er den Wert der bei sessionCreate gesetzt wurde

        $res = $this->doCall($this->mcpayAPIURL, $data);
        return $this->parseResult($res);
    }

    /**
     * transactionReversal
     *
     * @param $sessionId
     * @param $transactionId
     * @param int $fraudDetection
     * @return bool|stdClass
     * @throws Exception
     */
    public function transactionReversal($sessionId, $transactionId, $fraudDetection = 1)
    {
        $data = $this->getBaseData(__FUNCTION__, 'sessionId', $sessionId);
        if (!$data) return FALSE;

        $data['transactionId'] = $transactionId; //Transaktionsnummer der Transaktion die zurückgebucht werden soll
        $data['fraudDetection'] = $fraudDetection; // de/aktiviert FraudDetection

        $res = $this->doCall($this->mcpayAPIURL, $data);
        return $this->parseResult($res);
    }

    /**
     * transactionGetCrypt
     *
     * @param $cryptId
     *
     * @return array|\stdClass
     * @throws \Exception
     */
    public function transactionGetCrypt($cryptId)
    {
        return $this->getBaseData(__FUNCTION__, 'cryptId', $cryptId, TRUE);
    }

    /**
     * tokenGet
     *
     * @param $token
     *
     * @return array|\stdClass
     * @throws \Exception
     */
    public function tokenGet($token)
    {
        return $this->getBaseData(__FUNCTION__, 'token', $token, TRUE);
    }

    /**
     * creditcardDataGet
     *
     * @param $customerId
     *
     * @return array|\stdClass
     * @throws \Exception
     */
    public function creditcardDataGet($customerId)
    {
        return $this->getBaseData(__FUNCTION__, 'customerId', $customerId, TRUE);
    }

    /**
     * sessionGet
     *
     * @param $sessionId
     *
     * @return array|\stdClass
     * @throws \Exception
     */
    public function sessionGet($sessionId)
    {
        return $this->getBaseData(__FUNCTION__, 'sessionId', $sessionId, TRUE);
    }

    /**
     * customerGet
     *
     * @param $customerId
     *
     * @return array|\stdClass
     * @throws \Exception
     */
    public function customerGet($customerId)
    {
        return $this->getBaseData(__FUNCTION__, 'customerId', $customerId, TRUE);
    }

    /**
     * customerSet
     *
     * @param $customerId
     * @param $params
     * @param $newCustomerId
     *
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function customerSet($customerId, $params, $newCustomerId = NULL)
    {
        $data = $this->getBaseData(__FUNCTION__, 'customerId', $customerId);
        if (!$data) return FALSE;

        $data['freeParams'] = $params;
        if (!empty($newCustomerId)) {
            $data['newCustomerId'] = $newCustomerId;
        }

        $res = $this->doCall($this->mcpayAPIURL, $data);
        return $this->parseResult($res);
    }

    /**
     * customerList
     *
     * @param array $params
     *
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function customerList($params = array())
    {
        $data = $this->getBaseData(__FUNCTION__);
        if (!$data) return FALSE;

        if (!empty($params['customerId'])) {
            $data['customerId'] = $params['customerId'];
        }
        if (!empty($params['onlyNewer'])) {
            $data['onlyNewer'] = $params['onlyNewer'];
        }
        if (!empty($params['onlyDoublet'])) {
            $data['onlyDoublet'] = $params['onlyDoublet'];
        }
        if (!empty($params['from'])) {
            $data['from'] = $params['from'];
        }
        if (!empty($params['to'])) {
            $data['to'] = $params['to'];
        }
        if (!empty($params['limit'])) {
            $data['limit'] = $params['limit'];
        }

        $res = $this->doCall($this->mcpayAPIURL, $data);
        return $this->parseResult($res);
    }

    /**
     * customerDoubletList
     *
     * @param array $params
     *
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function customerDoubletList($params = array())
    {
        $data = $this->getBaseData(__FUNCTION__);
        if (!$data) return FALSE;

        if (!empty($params['customerId'])) {
            $data['customerId'] = $params['customerId'];
        }
        if (!empty($params['from'])) {
            $data['from'] = $params['from'];
        }
        if (!empty($params['to'])) {
            $data['to'] = $params['to'];
        }
        if (!empty($params['limit'])) {
            $data['limit'] = $params['limit'];
        }

        $res = $this->doCall($this->mcpayAPIURL, $data);
        return $this->parseResult($res);
    }

    /**
     * transactionGet
     *
     * @param $transactionId
     *
     * @return array|\stdClass
     * @throws \Exception
     */
    public function transactionGet($transactionId)
    {
        return $this->getBaseData(__FUNCTION__, 'transactionId', $transactionId, TRUE);
    }

    /**
     * addressGet
     *
     * @param $customerId
     *
     * @return array|\stdClass
     * @throws \Exception
     */
    public function addressGet($customerId)
    {
        return $this->getBaseData(__FUNCTION__, 'customerId', $customerId, TRUE);
    }

    /**
     * addressSet
     *
     * @param $customerId
     * @param $addrObj
     *
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function addressSet($customerId, $addrObj)
    {
        $data = $this->getBaseData(__FUNCTION__, 'customerId', $customerId);
        if (!$data) return FALSE;

        if (in_array($this->paymethod, array('sepa', 'prepay'))) {
            if (in_array($this->paymethod, array('prepay'))) {
                if (!empty($addrObj->salut))
                    $data['form'] = $addrObj->salut; // Anrede "NONE", "SIR", "MADAM", "MISS", "COMPANY"
            }
            $data['firstName'] = $addrObj->firstname; // Vorname
            $data['surName'] = $addrObj->lastname; // Nachname
            if (in_array($this->paymethod, array('prepay'))) {
                if (!empty($addrObj->extra))
                    $data['address'] = $addrObj->extra; // Zusätzliche Angaben z.B. "bei Schmidt"
            }
            if (in_array($this->paymethod, array('sepa'))) {
                if (!empty($addrObj->company))
                    $data['company'] = $addrObj->company; // Firma
            }
            $data['street'] = $addrObj->street; // Strasse und Hausnummer
            $data['zip'] = $addrObj->zip; // Postleitzahl
            $data['city'] = $addrObj->city; // Ort
        }
        if (in_array($this->paymethod, array('ccard'))) {
            $data['address'] = $addrObj->street; //	Strasse und Hausnummer
            $data['zipcode'] = $addrObj->zip; //	Postleitzahl
            $data['town'] = $addrObj->city; //	Ort
        }
        $data['country'] = $addrObj->country; //	Land [ISO 3166-1-alpha-2] bspw. DE, AT, CH

        $res = $this->doCall($this->mcpayAPIURL, $data);
        return $this->parseResult($res);
    }

    /**
     * contactDataGet
     *
     * @param $customerId
     *
     * @return array|\stdClass
     * @throws \Exception
     */
    public function contactDataGet($customerId)
    {
        return $this->getBaseData(__FUNCTION__, 'customerId', $customerId, TRUE);
    }

    /**
     * contactDataSet
     *
     * @param $customerId
     * @param $custObj
     *
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function contactDataSet($customerId, $custObj)
    {
        $data = $this->getBaseData(__FUNCTION__, 'customerId', $customerId);
        if (!$data) return FALSE;
        if (!in_array($this->paymethod, array('sepa', 'prepay'))) return FALSE;

        $data['email'] = $custObj->email; // Emailadresse
        if (!empty($custObj->phone))
            $data['phone'] = $custObj->phone; // Festnetzanschluss
        if (!empty($custObj->mobile))
            $data['mobile'] = $custObj->mobile; // Handynummer
        if (in_array($this->paymethod, array('prepay'))) {
            if (!empty($custObj->language))
                $data['language'] = $custObj->language; // Sprache
        }

        $res = $this->doCall($this->mcpayAPIURL, $data);
        return $this->parseResult($res);
    }

    /**
     * currenciesGetSupported
     *
     * @param bool $online
     *
     * @return array|\stdClass
     * @throws \Exception
     */
    public function currenciesGetSupported($online = FALSE)
    {
        if ($online) {
            return $this->getBaseData(__FUNCTION__, NULL, NULL, TRUE);
        } else {
            $retObj = new stdClass();
            $retObj->result = $this->supportedCurrencies;
            return $retObj;
        }
    }

    /**
     * currenciesGetNativeSupported
     *
     * @return array|\stdClass
     * @throws \Exception
     */
    public function currenciesGetNativeSupported()
    {
        return $this->getBaseData(__FUNCTION__, NULL, NULL, TRUE);
    }

    /**
     * getBaseData
     *
     * @param string $func
     * @param string $paramName
     * @param string $paramVal
     * @param bool $doCall
     *
     * @return array|stdClass
     * @throws Exception
     */
    private function getBaseData($func, $paramName = NULL, $paramVal = NULL, $doCall = FALSE)
    {
        //mail('te@st.de', __CLASS__.'->'.__FUNCTION__, print_r(func_get_args(),1));
        if (empty($func)) throw new Exception('Func cant be empty.');
        if (!empty($paramName) && empty($paramVal)) throw new Exception('paramVal cant be empty when paramName (' . $paramName . ') is set.');

        $this->invalidCFG(FALSE);

        $data = array(
            'action' => $func,
            'accessKey' => $this->accessKey, // AccessKey aus dem Controlcenter
            'testMode' => (int)(bool)$this->testMode, // aktiviert Testumgebung
            $paramName => $paramVal,
        );

        if ($doCall) {
            $res = $this->doCall($this->mcpayAPIURL, $data, FALSE);
            return $this->parseResult($res);
        }

        return $data;
    }

    /**
     * isAllowedCountry
     * check if paymethod is allowed in country
     *
     * @param string $isocode
     * @param string $paymethod
     *
     * @return boolean
     */
    public function isAllowedCountry($isocode, $paymethod)
    {
        if (empty($isocode)) return FALSE;
        $paymethod = str_replace('mipa_', '', strtolower($paymethod)); // legacy
        $paymethod = str_replace('mcpay_', '', strtolower($paymethod));
        if (!in_array($paymethod, $this->availablePaymethods)) return FALSE;

        switch ($paymethod) {
            case 'ccard':
                // internatioanl
                return TRUE;
                break;
            case 'sofort':
                if (in_array($isocode, $this->allowedSOFORTCountries)) return TRUE;
                return FALSE;
                break;
            case 'sepa':
                if (in_array($isocode, $this->allowedSEPACountries)) return TRUE;
                return FALSE;
                break;
            case 'prepay':
                // internatioanl
                return TRUE;
                break;
            case 'paysafecard':
                // internatioanl
                return TRUE;
                break;
            case 'paypal':
                // internatioanl
                return TRUE;
                break;
        }
        return FALSE;
    }

    public function switchPaymethod($pm)
    {
        $pm = strtolower($pm);
        if (!in_array($pm, $this->availablePaymethods)) return FALSE;
        $this->paymethod = $pm;
        if ($pm == 'sepa') { // sepa
            $this->mcpayAPIURL = $this->mcpayAPIURLSepa;
        } else if ($pm == 'prepay') { // prepay
            $this->mcpayAPIURL = $this->mcpayAPIURLPrepay;
        } else if ($pm == 'sofort') { // sofort
            $this->mcpayAPIURL = $this->mcpayAPIURLPrepay;
        } else if ($pm == 'paysafecard') { // paysafecard
            $this->mcpayAPIURL = $this->mcpayAPIURLPrepay;
        } else if ($pm == 'paypal') { // paypal
            $this->mcpayAPIURL = $this->mcpayAPIURLPrepay;
        } else { // ccard
            $this->mcpayAPIURL = $this->mcpayAPIURLCCard;
        }
        return TRUE;
    }

    /**
     * unifyID
     * get unique id for project level
     * instead it is unique for account level
     *
     * @param $id
     *
     * @return string
     */
    public function unifyID($id)
    {
        $prefix = $this->customPrefix;
        if (empty($prefix)) {
            $prefix = $this->project;
        }
        if (strpos($id, $prefix) !== FALSE) return $id; // prefix already added
        return $prefix . '_' . $id;
    }

    /**
     * sessionChargeTest
     * simluate bankfile creation
     * this decides between quit (before bankfile) or reversal (after bankfile)
     * this is the differenz between money is moved or not
     * Typ "BOOKING"
     *
     * @return array|\stdClass
     * @throws \Exception
     */
    public function sessionChargeTest()
    {
        return $this->getBaseData(__FUNCTION__, NULL, NULL, TRUE);
    }

    /**
     * resetTest
     * deletes all customer and trx in test system
     *
     * @return array|\stdClass
     * @throws \Exception
     */
    public function resetTest()
    {
        return $this->getBaseData(__FUNCTION__, NULL, NULL, TRUE);
    }

    /**
     * config
     *
     * @param $project
     *
     * @return array|\stdClass
     * @throws \Exception
     */
    public function config($project)
    {
        if (!in_array($this->paymethod, array('prepay'))) return FALSE;
        $this->setConfig(array('testMode' => 0)); // must be 0 always!
        return $this->getBaseData(__FUNCTION__, 'project', $project, TRUE);
    }

    /**
     * sessionReverseTest
     * create a quit or reversal of a session
     * depends on sessionChargeTest
     * Typ "REVERSAL"
     *
     * @param $sessionId
     * @param $bookObj
     *
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function sessionReverseTest($sessionId, $bookObj)
    {
        $data = $this->getBaseData(__FUNCTION__, 'sessionId', $sessionId);
        if (!$data) return FALSE;

        $data['reverseCode'] = $bookObj->reverseCode; //	Textschlüssel der Bank z.B. 9055 - Widerspruch
        $data['reverseReason'] = $bookObj->reverseReason; //	Rücklastschriftgrund "UNSPECIFIED", "ACCOUNT_EXPIRED", "ACCOUNT_WRONG", "NOT_AUTHORIZED", "CONTRADICTED", "UNKNOWN"

        $res = $this->doCall($this->mcpayAPIURL, $data);
        return $this->parseResult($res);
    }

    /**
     * sessionRechargeTest
     * Typ "BACKPAY"
     *
     * @param $sessionId
     * @param $bookObj
     *
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function sessionRechargeTest($sessionId, $bookObj)
    {
        $data = $this->getBaseData(__FUNCTION__, 'sessionId', $sessionId);
        if (!$data) return FALSE;

        $data['amount'] = $bookObj->amount; //	optional der nachgezahlte Teilbetrag in EUR-Cent

        $res = $this->doCall($this->mcpayAPIURL, $data);
        return $this->parseResult($res);
    }

    /**
     * sessionRefundReverseTest
     * Typ "REFUNDREVERSAL"
     *
     * @param $sessionId
     *
     * @return array|\stdClass
     * @throws \Exception
     */
    public function sessionRefundReverseTest($sessionId)
    {
        return $this->getBaseData(__FUNCTION__, 'sessionId', $sessionId, TRUE);
    }

    /**
     * sessionChange
     *
     * @param $sessionId
     * @param $bookObj
     *
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function sessionChange($sessionId, $bookObj)
    {
        $data = $this->getBaseData(__FUNCTION__, 'sessionId', $sessionId);
        if (!$data) return FALSE;

        $data['amount'] = $bookObj->amount; //	Minderung der Forderung, als positiver Betrag

        $res = $this->doCall($this->mcpayAPIURL, $data);
        return $this->parseResult($res);
    }

    /**
     * sessionPayinTest
     *
     * @param $sessionId
     * @param $bookObj
     *
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function sessionPayinTest($sessionId, $bookObj)
    {
        $data = $this->getBaseData(__FUNCTION__, 'sessionId', $sessionId);
        if (!$data) return FALSE;

        $data['amount'] = $bookObj->amount; //	Minderung der Forderung, als positiver Betrag
        if (!empty($bookObj->bankCountry))
            $data['bankCountry'] = $bookObj->bankCountry; //	'DE'	Land der Bank
        if (!empty($bookObj->bankCode))
            $data['bankCode'] = $bookObj->bankCode; // Bankleitzahl des Kunden
        if (!empty($bookObj->accountNumber))
            $data['accountNumber'] = $bookObj->accountNumber; //	Kontonummer des Kunden
        if (!empty($bookObj->accountHolder))
            $data['accountHolder'] = $bookObj->accountHolder; //	Kontoinhaber des Kunden

        $res = $this->doCall($this->mcpayAPIURL, $data);
        return $this->parseResult($res);
    }

    /**
     * sessionRemindTest
     *
     * @param      $sessionId
     * @param bool $lastRemind
     *
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function sessionRemindTest($sessionId, $bookObj)
    {
        $data = $this->getBaseData(__FUNCTION__, 'sessionId', $sessionId);
        if (!$data) return FALSE;

        if (empty($bookObj->lastRemind)) $bookObj->lastRemind = FALSE;

        $data['lastRemind'] = (bool)$bookObj->lastRemind; //	Letzte Erinnerung

        $res = $this->doCall($this->mcpayAPIURL, $data);
        return $this->parseResult($res);
    }

    /**
     * sessionExpireTest
     *
     * @return array|\stdClass
     * @throws \Exception
     */
    public function sessionExpireTest($sessionId)
    {
        return $this->getBaseData(__FUNCTION__, 'sessionId', $sessionId, TRUE);
    }

    /**
     * transactionBackpayNotificationTest
     * simulate BACKPAY TRX (like rebill after chargeback or refund)
     * money goes from customer to merchant
     *
     * @param $transactionId
     *
     * @return array|\stdClass
     * @throws \Exception
     */
    public function transactionBackpayNotificationTest($transactionId)
    {
        return $this->getBaseData(__FUNCTION__, 'transactionId', $transactionId, TRUE);
    }

    /**
     * transactionChargebackNotificationTest
     * simulate CHARGEBACK TRX
     *
     * @param $transactionId
     *
     * @return array|\stdClass
     * @throws \Exception
     */
    public function transactionChargebackNotificationTest($transactionId)
    {
        return $this->getBaseData(__FUNCTION__, 'transactionId', $transactionId, TRUE);
    }

    /**
     * transactionCreditNotificationTest
     * simulate CREDIT TRX
     *
     * @param $transactionId
     *
     * @return array|\stdClass
     * @throws \Exception
     */
    public function transactionCreditNotificationTest($transactionId)
    {
        return $this->getBaseData(__FUNCTION__, 'transactionId', $transactionId, TRUE);
    }

    /**
     * transactionRefundNotificationTest
     * simulate REFUND TRX
     *
     * @param $transactionId
     *
     * @return array|\stdClass
     * @throws \Exception
     */
    public function transactionRefundNotificationTest($transactionId)
    {
        return $this->getBaseData(__FUNCTION__, 'transactionId', $transactionId, TRUE);
    }

    /**
     * check3dAvailability
     *
     * @param $sessionId
     * @param $returnURL
     *
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function check3dAvailability($sessionId, $returnURL)
    {
        $data = $this->getBaseData(__FUNCTION__, 'sessionId', $sessionId);
        if (!$data) return FALSE;

        $data['returnUrl'] = $returnURL;
        $this->mcpayAPIURL = $this->mcpayAPIURLCCard3D;

        $res = $this->doCall($this->mcpayAPIURL, $data);
        return $this->parseResult($res);
    }

    /**
     * check3dAvailabilityTest
     * Helper method for testing
     *
     * @param $sessionId
     * @param $bookObj
     *
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function check3dAvailabilityTest($sessionId, $bookObj)
    {
        $returnURL = $bookObj->url;
        return $this->check3dAvailability($sessionId, $returnURL);
    }

    /**
     * verify3dNotification
     *
     * @param $check3dId
     *
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function verify3dNotification($check3dId)
    {
        $data = $this->getBaseData(__FUNCTION__, 'check3dId', $check3dId);
        if (!$data) return FALSE;

        $this->mcpayAPIURL = $this->mcpayAPIURLCCard3D;

        $res = $this->doCall($this->mcpayAPIURL, $data);
        return $this->parseResult($res);
    }


}

// logger class
class mcpayLog
{
    var $error_log = 'mcpay.log';

    function __construct($logfile)
    {
        if (!empty($logfile)) $this->error_log = $logfile;
    }

    function debug($msg)
    {
        error_log(date('Y-m-d H:i:s') . ' ' . $msg . "\n", 3, $this->error_log);
    }

    function log($func, $msg)
    {
        if (is_array($msg)) $msg = print_r($msg, 1);
        if (is_object($msg)) $msg = print_r($msg, 1);
        error_log(date('Y-m-d H:i:s') . ' ' . $func . ': ' . $msg . "\n", 3, $this->error_log);
    }
}