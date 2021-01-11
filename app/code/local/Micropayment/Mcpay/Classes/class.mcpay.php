<?php
if (!defined('MCPAY_BASE_PATH')) {
  $mcpayPath = dirname(__FILE__).'/../';
  define('MCPAY_BASE_PATH', $mcpayPath);
}
if (is_dir(MCPAY_BASE_PATH.'classes/')) {
  require_once(MCPAY_BASE_PATH.'classes/class.mcpay_core.php');
} else if (is_dir(MCPAY_BASE_PATH.'Classes/')) {
  require_once(MCPAY_BASE_PATH.'Classes/class.mcpay_core.php');
} else {
  die('class.mcpay_core.php not found!');
}

class mcpay extends mcpay_core
{
  var $version = '1.2.4';

  function __construct($testMode = 1, $debugLog = NULL)
  {
    parent::__construct($testMode, $debugLog);
  }

  /**
   * getAllowedCurrenies
   *
   * @param $config
   *
   * @return mixed
   * @throws \Exception
   */
  function getAllowedCurrenies($config)
  {
    $this->setConfig($config);
    try {
      $res = $this->currenciesGetSupported();
    } catch (Exception $e) {
      throw new Exception('currenciesGetSupported failed. '.$e->getMessage());
    }
    //mail('te@st.de', __CLASS__.'->'.__FUNCTION__, print_r($res,1));
    return $res->result;
  }

  /**
   * bookCCard
   *
   * @param $userData
   * @param $config
   * @param $payData
   *
   * @return bool|\stdClass
   * @throws \Exception
   */
  function bookCCard($userData, $config, $payData)
  {
    if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->userData: '.print_r($userData, 1));
    if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->payData: '.print_r($payData, 1));

    if (empty($payData['token'])) {
      throw new Exception(__CLASS__.'->'.__FUNCTION__.': Token cant be empty!');
    }

    $retObj = new stdClass();
    $this->setConfig($config);
    $this->switchPaymethod('ccard');
    $token = $payData['token'];

    $custID = $userData['id'];
    if (empty($custID)) $custID = 'guest-'.$userData['order_id']; // guest order
    $custID             = $this->unifyID($custID);
    $custObj            = new stdClass();
    $custObj->id        = $custID;
    $custObj->firstname = $userData['firstName'];
    $custObj->lastname  = $userData['lastName'];
    $custObj->email     = $userData['email'];
    $custObj->street    = $userData['street'];
    $custObj->zip       = $userData['zip'];
    $custObj->city      = $userData['city'];
    $custObj->country   = $userData['country'];
    $custObj->company   = $userData['company'];
    $custObj->lang      = strtolower($userData['lang']); //'de-DE';
    $custObj->ip        = $_SERVER['REMOTE_ADDR'];
    $custObj->payload   = array(
      // for customerCreate
      'shop_customer' => $custID,
    );

    // guest order
    if (empty($custObj->id)) {
      $custObj->id = 'guest-'.$userData['order_id'];
    }

    //$this->customPrefix = time();
    $orderID           = $this->unifyID($userData['order_id']);
    $config['paytext'] = str_replace('{$orderid}', $orderID, $config['paytext']);
    $bookObj           = new stdClass();
    $bookObj->amount   = $userData['amount'];
    $bookObj->currency = $userData['currency'];
    $bookObj->orderID  = $orderID;
    $bookObj->title    = 'Order '.$orderID; // shown in control center
    $bookObj->paytext  = $config['paytext']; // shown on customer bank account
    $bookObj->payload  = array(
      'orderid'    => $orderID,
      'customerid' => $custID,
    );
    if (!empty($config['payload'])) {
      foreach ($config['payload'] AS $k => $v) {
        $bookObj->payload[$k] = $v;
      }
    }
    if (isset($config['autogenSession'])) {
      $bookObj->autogenSession = (bool)$config['autogenSession'];
    }

    if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->custObj: '.print_r($custObj, 1));
    if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->bookObj: '.print_r($bookObj, 1));
    if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->config: '.print_r($config, 1));
    $retObj->custObj = $custObj;
    $retObj->bookObj = $bookObj;

    // create customer and session in one step but with update = 2 for customer update
    try {
      $res = $this->tokenSessionCreate($token, $custObj, $bookObj);
      if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->tokenSessionCreate: '.print_r($res, 1)); // log result
    } catch (Exception $e) {
      throw new Exception('tokenSessionCreate failed. '.$e->getMessage());
    }

    try {
      $res2 = $this->addressSet($custObj->id, $custObj);
      if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->addressSet: '.print_r($res, 1)); // log result
    } catch (Exception $e) {
      throw new Exception('addressSet failed. '.$e->getMessage());
    }

    if ($res->error) {
      throw new Exception('tokenSessionCreate failed. '.$res->errorMessage);
    }
    try {
      $sessionID = $res->sessionId;
      $returnURL = $config['returnURL'];
      $start3d   = 1;
      //$res       = $this->check3dAvailability($sessionID, $returnURL);
      //if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->check3dAvailability: '.print_r($res, 1)); // log result
      $res = $this->transactionPurchase($sessionID, 1, 0, 1, $returnURL, $start3d);
      if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->transactionPurchase: '.print_r($res, 1)); // log result
      $retObj->trxInfo = $res;

      switch ($res->secureStatus) {
        case 'BILLING':
          if ($res->transactionStatus == 'SUCCESS') {
            //$res = $this->transactionPurchase($sessionID);
            //if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->transactionPurchase: '.print_r($res, 1)); // log result
            //if ($res->transactionStatus == 'SUCCESS') {
            $retObj->trxInfo->sessionId = $sessionID; // to have it in return value
            if (!empty($config['returnURLNO3D'])) {
              $retObj->forward    = 1;
              $retObj->forwardURL = $config['returnURLNO3D'].$sessionID;
            }
            return $retObj;
            //}
          } else {
            throw new Exception('Error: '.$res->transactionResultMessage);
          }

          return $res;
          break;
        case 'START3D':
          $retObj->forward    = 1;
          $retObj->forwardURL = $res->forwardUrl;
          return $retObj;
          break;
        case 'DECLINED':
          throw new Exception('Transaction delined.');
          break;
        case 'FAILURE':
          throw new Exception('Transaction failed.');
          break;
      }
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }

    return $retObj;
  }

  /**
   * reuseCard
   *
   * @param $userData
   * @param $config
   *
   * @return array|bool|\stdClass
   * @throws \Exception
   */
  function reuseCard($userData, $config)
  {
    $retObj = new stdClass();
    $this->setConfig($config);
    $this->switchPaymethod('ccard');

    $custID             = $userData['id'];
    $custID             = $this->unifyID($custID);
    $custObj            = new stdClass();
    $custObj->id        = $custID;
    $custObj->firstname = $userData['firstName'];
    $custObj->lastname  = $userData['lastName'];
    $custObj->email     = $userData['email'];
    $custObj->street    = $userData['street'];
    $custObj->zip       = $userData['zip'];
    $custObj->city      = $userData['city'];
    $custObj->country   = $userData['country'];
    $custObj->company   = $userData['company'];
    $custObj->lang      = strtolower($userData['lang']); //'de-DE';
    $custObj->ip        = $_SERVER['REMOTE_ADDR'];


    // check if customer id does not exist
    try {
      $res = $this->customerGet($custID);
      //mail('test@test.test', __CLASS__.'->'.__FUNCTION__.'->customerGet: '.$custID, print_r($res, 1));
    } catch (Exception $e) {
      throw new Exception('Unknown Customer ID cant be used for reuse!');
    }
    if (empty($custObj->id)) {
      throw new Exception('Customer ID cant be empty!');
    }

    // look for other ip if empty
    if (empty($custObj->ip)) {
      $custObj->ip = $_SERVER['REMOTE_ADDR'];
      if (empty($custObj->ip)) {
        $custObj->ip = $_SERVER['SERVER_ADDR'];
      }
    }

    $orderID           = $this->unifyID($userData['order_id']);
    $config['paytext'] = str_replace('{$orderid}', $orderID, $config['paytext']);
    $bookObj           = new stdClass();
    $bookObj->amount   = $userData['amount'];
    $bookObj->currency = $userData['currency'];
    $bookObj->orderID  = $orderID;
    $bookObj->title    = 'Order '.$orderID; // shown in control center
    $bookObj->paytext  = $config['paytext']; // shown on customer bank account
    $bookObj->payload  = array(
      'orderid'    => $orderID,
      'customerid' => $custID,
    );
    if (!empty($config['payload'])) {
      foreach ($config['payload'] AS $k => $v) {
        $bookObj->payload[$k] = $v;
      }
    }
    if (isset($config['autogenSession'])) {
      $bookObj->autogenSession = (bool)$config['autogenSession'];
    }

    //mail('test@test.test', 'DEBUG1 '.__CLASS__.'->'.__FUNCTION__, print_r($custObj, 1).print_r($bookObj, 1).print_r($config, 1));
    if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->custObj: '.print_r($custObj, 1));
    if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->bookObj: '.print_r($bookObj, 1));
    if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->config: '.print_r($config, 1));

    if ($config['create_new_session']) {
      $bookObj->orderID .= '_'.date('His');
    }

    try {
      $res = $this->sessionCreate($custObj, $bookObj);
      //mail('test@test.test', __CLASS__.'->'.__FUNCTION__.'->sessionCreate: ', print_r($res, 1));

      $sessionID = $res->sessionId;
    } catch (Exception $e) {
      throw new Exception('sessionCreate failed. '.$e->getMessage());
    }

    try {
      $res2 = $this->addressSet($custObj->id, $custObj);
      //mail('test@test.test', __CLASS__.'->'.__FUNCTION__ . '->addressSet', print_r($res2, 1));
    } catch (Exception $e) {
      throw new Exception('addressSet failed. '.$e->getMessage());
    }

    try {
      $fraudDetection = 1;
      $avsCheck       = 0;
      $recurring      = 1;
      $res            = $this->transactionPurchase($sessionID, $fraudDetection, $avsCheck, $recurring);
      //mail('test@test.test', __CLASS__.'->'.__FUNCTION__.'->transactionPurchase: ', print_r($res, 1));
    } catch (Exception $e) {
      throw new Exception('transactionPurchase failed. '.$e->getMessage());
    }
    $retObj->trxInfo            = $res;
    $retObj->trxInfo->sessionId = $sessionID;
    if (!empty($config['returnURLNO3D'])) {
      $retObj->forward    = 1;
      $retObj->forwardURL = $config['returnURLNO3D'].$sessionID;
    }
    return $retObj;
  }

  /**
   * verify3D
   *
   * @param $check3dId
   * @param $config
   *
   * @return bool|\stdClass
   * @throws \Exception
   */
  function verify3D($check3dId, $config)
  {
    $this->setConfig($config);
    //echo __FUNCTION__.': '.$check3dId;
    try {
      $res = $this->verify3dNotification($check3dId);

      if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->verify3dNotification: '.print_r($res, 1)); // log result
      //mail('webmaster@web-dezign.de', __CLASS__.'->'.__FUNCTION__.'->$res', print_r($res,1));

      if ($res->sessionStatus == 'SUCCESS') {
        //$sessionId = $res->sessionId;
        //$res       = $this->transactionPurchase($sessionId);
        //if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->transactionPurchase: '.print_r($res, 1)); // log result
        if ($res->transactionStatus == 'SUCCESS') {
          //$res->sessionId = $sessionId; // to have it in return value
          return $res;
        } else {
          $res->error = 1;
          return $res;
        }
      } else {
        throw new Exception('3D Secure failed: '.$res->transactionResultMessage);
      }

    } catch (Exception $e) {
      throw new Exception('verify3D failed. '.$e->getMessage());
    }

    return FALSE;
  }

  /**
   * refundTRX
   *
   * @param $order
   * @param $config
   *
   * @return bool|\stdClass
   * @throws \Exception
   */
  function refundTRX($order, $config)
  {
    if (empty($config['trxid'])) throw new Exception('TrxId cant be empty');
    if (empty($config['sessid'])) throw new Exception('SessId cant be empty');
    if (empty($config['amount'])) throw new Exception('Amount cant be empty');

    // switch to cent instead of eur
    $config['amount'] = (INT)($config['amount'] * 100);

    if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->config: '.print_r($config, 1));

    $this->setConfig($config);

    try {
      $sessionID     = $config['sessid'];
      $transactionId = $config['trxid'];
      $amount        = $config['amount'];
      $currency      = $config['currency'];
      $res           = $this->transactionRefund($sessionID, $transactionId, $amount, $currency);
      //mail('test@test.test', __CLASS__.'->'.__FUNCTION__.'->transactionRefund: ', print_r($res, 1));
    } catch (Exception $e) {
      throw new Exception('transactionRefund failed. '.$e->getMessage());
    }

    $res->sessionId = $sessionID;
    return $res;
  }

  /**
   * bookSepa
   *
   * @param $userData
   * @param $config
   * @param $payData
   *
   * @return array|bool|\stdClass
   * @throws \Exception
   */
  function bookSepa($userData, $config, $payData)
  {
    $retObj = new stdClass();
    $this->setConfig($config);
    $this->switchPaymethod('sepa');

    $custID = $userData['id'];
    if (empty($custID)) $custID = 'guest-'.$userData['order_id']; // guest order
    $custID             = $this->unifyID($custID);
    $custObj            = new stdClass();
    $custObj->id        = $custID;
    $custObj->firstname = $userData['firstName'];
    $custObj->lastname  = $userData['lastName'];
    $custObj->email     = $userData['email'];
    $custObj->street    = $userData['street'];
    $custObj->zip       = $userData['zip'];
    $custObj->city      = $userData['city'];
    $custObj->country   = $userData['country'];
    $custObj->company   = $userData['company'];
    $custObj->lang      = strtolower($userData['lang']); //'de-DE';
    $custObj->ip        = $_SERVER['REMOTE_ADDR'];
    $custObj->payload   = array(
      // for customerCreate
      'shop_customer' => $custID,
    );

    $orderID           = $this->unifyID($userData['order_id']);
    $config['paytext'] = str_replace('{$orderid}', $orderID, $config['paytext']);
    $bookObj           = new stdClass();
    $bookObj->amount   = $userData['amount'];
    $bookObj->currency = $userData['currency'];
    $bookObj->orderID  = $orderID;
    $bookObj->title    = 'Order '.$orderID; // shown in control center
    $bookObj->paytext  = $config['paytext']; // shown on customer bank account
    $bookObj->payload  = array(
      'orderid'    => $orderID,
      'customerid' => $custID,
    );
    if (!empty($config['payload'])) {
      foreach ($config['payload'] AS $k => $v) {
        $bookObj->payload[$k] = $v;
      }
    }
    $bookObj->iban       = $payData['iban'];
    $bookObj->bic        = $payData['bic'];
    $bookObj->holder     = $payData['holder'];
    $bookObj->country    = $userData['country'];
    $bookObj->bankName   = $payData['bankName'];
    $bookObj->mandateRef = NULL; // SEPA Mandats Referenz, wird ansonsten erzeugt
    if (!empty($config['mandateRef'])) {
      $bookObj->mandateRef = $config['mandateRef'];
    }
    $bookObj->mandateSignDate = NULL; // Datum der Mandatserteilung, ansonsten sessionApprove-Datum
    $bookObj->mandateRecur    = 'ONEOFF'; // SEPA Mandat für einzelne - Standard für neue Mandate: "ONEOFF", oder wiederkehrende Zahlungen: "RECURRING" oder "FINAL"
    // default is ONEOFF - RECURRING only for abo payment
    if ($config['isAbo']) {
      $bookObj->mandateRecur = 'RECURRING';
    }

    if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->custObj: '.print_r($custObj, 1));
    if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->bookObj: '.print_r($bookObj, 1));
    if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->config: '.print_r($config, 1));
    $retObj->custObj = $custObj;
    $retObj->bookObj = $bookObj;

    // create customer and session in one step but with update = 2 for customer update
    try {
      // customerGet check if customer exists
      $oldMandate = '';
      $res        = $this->customerGet($custObj->id);
      //mail('test@test.test', __CLASS__.'->'.__FUNCTION__ . '->customerGet('.$custObj->id.')', print_r($res, 1));
      if (!empty($res->freeParams['mandateRef'])) {
        $oldMandate = $res->freeParams['mandateRef'];
      }
    } catch (Exception $e) {
      // if customer doesnt exists
      if ($e->getCode() == '3006') {
        try {
          $res = $this->customerCreate($custObj);
          //mail('test@test.test', __CLASS__.'->'.__FUNCTION__ . '->customerCreate', print_r($res, 1));

          $res = $this->bankaccountSet($custObj, $bookObj); // only mandatory if different data
          //mail('test@test.test', __CLASS__.'->'.__FUNCTION__ . '->bankaccountSet', print_r($res, 1));
        } catch (Exception $e) {
          throw new Exception('customerCreate failed. '.$e->getMessage());
        }
      } else {
        throw new Exception('customerGet failed. '.$e->getMessage());
      }
    }

    // check if there is something to set here! otherwise skip it.
    if (!empty($custObj->zip)) {
      try {
        $res = $this->addressSet($custObj->id, $custObj);
        //mail('test@test.test', __CLASS__.'->'.__FUNCTION__ . '->addressSet', print_r($res, 1));
      } catch (Exception $e) {
        throw new Exception('addressSet failed. '.$e->getMessage());
      }
    }

    try {
      $res = $this->contactDataSet($custObj->id, $custObj);
      //mail('test@test.test', __CLASS__.'->'.__FUNCTION__ . '->contactDataSet', print_r($res, 1));
    } catch (Exception $e) {
      throw new Exception('contactDataSet failed. '.$e->getMessage());
    }

    try {
      // get bank name
      if (empty($bookObj->bankName)) {
        $res = $this->bankaccountGet($custObj->id); // only mandatory if different data
        //mail('test@test.test', __CLASS__.'->'.__FUNCTION__ . '->bankaccountGet', print_r($res, 1));
        $bookObj->bankName = $res->bankName;
      }
    } catch (Exception $e) {
      $res1 = $this->bankaccountSet($custObj, $bookObj); // only mandatory if different data
      $res2 = $this->bankaccountGet($custObj->id); // only mandatory if different data
    }
    try {
      // check change in bankdata and update bankaccount
      if ($res->iban != $bookObj->iban) {
        $res = $this->bankaccountSet($custObj, $bookObj); // only mandatory if different data
        //mail('test@test.test', __CLASS__.'->'.__FUNCTION__ . '->bankaccountSet', print_r($res, 1));

        $res = $this->bankaccountGet($custObj->id); // only mandatory if different data
        //mail('test@test.test', __CLASS__.'->'.__FUNCTION__ . '->bankaccountGet', print_r($res, 1));
        $bookObj->bankName = $res->bankName;
      }
    } catch (Exception $e) {
      throw new Exception('bankaccountGet or Set failed. '.$e->getMessage(), $e->getCode());
    }
    try {
      // check if old mandate exists and use it
      if ($config['isAbo'] && !empty($oldMandate)) $bookObj->mandateRef = $oldMandate;
      // create new session
      $res = $this->sessionCreate($custObj, $bookObj);
      //mail('test@test.test', __CLASS__.'->'.__FUNCTION__.'->sessionCreate', print_r($res, 1));
      $sessionId         = $res->sessionId;
      $retObj->resCreate = $res;
    } catch (Exception $e) {
      throw new Exception('sessionCreate failed. '.$e->getMessage());
    }
    try {
      if ($config['isAbo'] && empty($oldMandate)) {
        $params = array('mandateRef' => $retObj->resCreate->mandateRef);
        $res    = $this->customerSet($custObj->id, $params);
        //mail('test@test.test', __CLASS__.'->'.__FUNCTION__ . '->customerSet', print_r($res, 1));
      }
    } catch (Exception $e) {
      throw new Exception('customerSet failed. '.$e->getMessage());
    }

    $payRecurStates = array(
      'OOFF' => ' einmalig',
      'FRST' => ' erstmals',
      'RCUR' => ' wiederholt',
      'FNAL' => ' letztmalig',
    );
    $textDE         = 'Unsere Forderung in Höhe von {$amount} {$currency} ziehen wir mit der SEPA-Lastschrift zum Mandat Nr. {$mandateRef} '
      .'zu der Gläubiger-Identifikationsnummer {$creditorId} von Ihrem Konto IBAN {$iban} bei {$bankname} {$bic} '
      .'zum Fälligkeitstag {$payDateDE}{$payRecurSt} ein. Wir bitten Sie für Kontodeckung zu sorgen.';
    $textEN         = 'Please confirm the direct debit authorization. '
      .'We will deduct the payment amounting to {$amount} {$currency} using SEPA direct debit on the day it is due {$payDateEN}. '
      .'Please ensure that your account has sufficient funds.';
    if (!empty($config['textDE'])) $textDE = $config['textDE'];
    if (!empty($config['textEN'])) $textEN = $config['textEN'];
    if (empty($bookObj->bankName)) {
      $bookObj->bankName = 'Ihrer Bank';
    }

    $repl = array(
      '{$amount}'     => sprintf('%1.2f', ($retObj->resCreate->amount / 100)),
      '{$currency}'   => $retObj->resCreate->currency,
      '{$mandateRef}' => $retObj->resCreate->mandateRef,
      '{$creditorId}' => $retObj->resCreate->creditorId,
      '{$iban}'       => $bookObj->iban,
      '{$bankname}'   => $bookObj->bankName,
      '{$bic}'        => 'BIC '.$bookObj->bic,
      '{$payDateDE}'  => date('d.m.Y', strtotime($retObj->resCreate->payDate)),
      '{$payDateEN}'  => date('m/d/Y', strtotime($retObj->resCreate->payDate)),
      '{$payRecurSt}' => $payRecurStates[$retObj->resCreate->payRecurState],
    );
    if (empty($bookObj->bic)) $repl['{$bic}'] = ''; // if no bic so no bic text
    $retObj->mandateTextDE = strtr($textDE, $repl);
    $retObj->mandateTextEN = strtr($textEN, $repl);

    try {
      // approve payment
      $res = $this->sessionApprove($sessionId);
      //mail('test@test.test', __CLASS__.'->'.__FUNCTION__.'->sessionApprove', print_r($res, 1));
      $retObj->resApprove = $res;
    } catch (Exception $e) {
      throw new Exception('sessionApprove failed. '.$e->getMessage());
    }

    if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->retObj: '.print_r($retObj, 1));
    return $retObj;
  }

  /**
   * refundSepa
   *
   * @param $order
   * @param $config
   *
   * @return bool|\stdClass
   * @throws \Exception
   */
  function refundSepa($order, $config)
  {
    if (empty($config['sessid'])) throw new Exception('SessId cant be empty');
    if (empty($config['amount'])) throw new Exception('Amount cant be empty');

    // switch to cent instead of eur
    $config['amount'] = (INT)($config['amount'] * 100);

    if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->config: '.print_r($config, 1));

    $this->setConfig($config);
    $this->switchPaymethod('sepa');

    try {
      $sessionID = $config['sessid'];
      $amount    = $config['amount'];
      $payText   = NULL;
      if (!empty($config['payText'])) {
        $payText = $config['payText'];
      }
      $res = $this->sessionRefund($sessionID, $amount, $payText);
      //mail('test@test.test', __CLASS__.'->'.__FUNCTION__.'->sessionRefund: ', print_r($res, 1));
    } catch (Exception $e) {
      throw new Exception('sessionRefund failed. '.$e->getMessage());
    }

    $res->sessionId = $sessionID;
    return $res;
  }

  /**
   * bookPrepay
   *
   * @param $userData
   * @param $config
   *
   * @return \stdClass
   * @throws \Exception
   */
  function bookPrepay($userData, $config)
  {
    $retObj = new stdClass();
    $this->setConfig($config);
    $this->switchPaymethod('prepay');

    $custID = $userData['id'];
    if (empty($custID)) $custID = 'guest-'.$userData['order_id']; // guest order
    $custID             = $this->unifyID($custID);
    $custObj            = new stdClass();
    $custObj->id        = $custID;
    $custObj->firstname = $userData['firstName'];
    $custObj->lastname  = $userData['lastName'];
    $custObj->email     = $userData['email'];
    $custObj->street    = $userData['street'];
    $custObj->zip       = $userData['zip'];
    $custObj->city      = $userData['city'];
    $custObj->country   = $userData['country'];
    $custObj->company   = $userData['company'];
    $custObj->language  = strtolower($userData['lang']); //'de-DE';
    $custObj->ip        = $_SERVER['REMOTE_ADDR'];
    // for customerCreate
    $custObj->payload = array(
      'shop_customer' => $custID,
    );

    //$orderID           = $this->unifyID($userData['order_id']);
    $orderID             = $userData['order_id'];
    $parts               = explode('-', $orderID);
    $orderID             = $parts[0]; // filter customer id
    $config['paytext']   = str_replace('{$orderid}', $orderID, $config['paytext']);
    $config['paytext']   = str_replace('{$projectid}', $config['project'], $config['paytext']);
    $bookObj             = new stdClass();
    $bookObj->amount     = $userData['amount'];
    $bookObj->currency   = $userData['currency'];
    $bookObj->orderID    = $orderID;
    $bookObj->title      = $config['paytext']; // shown in control center
    $bookObj->paytext    = $config['paytext']; // shown on customer bank account
    $bookObj->expireDays = $config['expiredays'];
    $bookObj->payload    = array(
      'orderid'    => $orderID,
      'customerid' => $custID,
    );
    if (!empty($config['payload'])) {
      foreach ($config['payload'] AS $k => $v) {
        $bookObj->payload[$k] = $v;
      }
    }
    $bookObj->country = $userData['country'];
    if (!empty($config['sessionID'])) {
      $bookObj->sessionID = $config['sessionID'];
    }

    if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->custObj: '.print_r($custObj, 1));
    if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->bookObj: '.print_r($bookObj, 1));
    if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->config: '.print_r($config, 1));
    $retObj->custObj = $custObj;
    $retObj->bookObj = $bookObj;

    try {
      // customerGet check if customer exists
      $res = $this->customerGet($custObj->id);
      //mail('test@test.test', __CLASS__.'->'.__FUNCTION__ . '->customerGet('.$custObj->id.')', print_r($res, 1));
    } catch (Exception $e) {
      // if customer doesnt exists
      if ($e->getCode() == '3006') {
        try {
          $res = $this->customerCreate($custObj);
          //mail('test@test.test', __CLASS__.'->'.__FUNCTION__ . '->customerCreate', print_r($res, 1));
        } catch (Exception $e) {
          throw new Exception('customerCreate failed. '.$e->getMessage());
        }
      } else {
        throw new Exception('customerGet failed. '.$e->getMessage());
      }
    }

    try {
      $res = $this->addressSet($custObj->id, $custObj);
      //mail('test@test.test', __CLASS__.'->'.__FUNCTION__ . '->addressSet', print_r($res, 1));
    } catch (Exception $e) {
      throw new Exception('addressSet failed. '.$e->getMessage());
    }

    try {
      $res = $this->contactDataSet($custObj->id, $custObj);
      //mail('test@test.test', __CLASS__.'->'.__FUNCTION__ . '->contactDataSet', print_r($res, 1));
    } catch (Exception $e) {
      throw new Exception('contactDataSet failed. '.$e->getMessage());
    }

    try {
      $res = $this->sessionCreate($custObj, $bookObj);
      //mail('test@test.test', __CLASS__.'->'.__FUNCTION__.'->sessionCreate', print_r($res, 1));
      $retObj->resCreate = $res;
    } catch (Exception $e) {
      throw new Exception('sessionCreate failed. '.$e->getMessage());
    }

    $textDE = 'Sehr geehrte Kundin, sehr geehrter Kunde,<br><br>'
      .'vielen Dank für Ihre Bestellung vom {$orderDateDE}. micropayment übernimmt für uns die Zahlungsabwicklung.<br><br>'
      .'Bitte überweisen Sie {$amount} {$currency} bis zum {$dueDateDE} auf die folgende Kontoverbindung, um den Kauf abzuschließen.<br><br>'
      .'Kontoinhaber: {$accountHolder}<br>'
      .'IBAN: {$iban}<br>'
      .'BIC: {$bic}<br>'
      .'Bank: {$bankName}<br>'
      .'Gesamtbetrag: {$amount} {$currency}<br>'
      .'Verwendungszweck: {$payText}';
    $textEN = 'Dear Customer,<br><br>'
      .'Thank you for purchase on {$orderDateEN}. Payment processing for us is provided by micropayment.<br><br>'
      .'Please make transfer in the amount of {$amount} {$currency} by {$dueDateEN} in order to complete your purchase. '
      .'Payment should be made to the following bank account.<br><br>'
      .'Beneficiary: {$accountHolder}<br>'
      .'IBAN: {$iban}<br>'
      .'BIC: {$bic}<br>'
      .'Bank: {$bankName}<br>'
      .'Total amount: {$amount} {$currency}<br>'
      .'Payment purpose: {$payText}';
    if (!empty($config['textDE'])) $textDE = $config['textDE'];
    if (!empty($config['textEN'])) $textEN = $config['textEN'];
    if (!empty($config['prepay_text_de'])) $textDE = $config['prepay_text_de'];
    if (!empty($config['prepay_text_en'])) $textEN = $config['prepay_text_en'];

    $repl = array(
      '{$amount}'        => sprintf('%1.2f', ($retObj->resCreate->amount / 100)),
      '{$currency}'      => $retObj->resCreate->currency,
      '{$dueDateDE}'     => date('d.m.Y', strtotime($retObj->resCreate->dueDate)),
      '{$dueDateEN}'     => date('m/d/Y', strtotime($retObj->resCreate->dueDate)),
      '{$orderDateDE}'   => date('d.m.Y'),
      '{$orderDateEN}'   => date('m/d/Y'),
      '{$iban}'          => $retObj->resCreate->iban,
      '{$bic}'           => $retObj->resCreate->bic,
      '{$accountHolder}' => $retObj->resCreate->accountHolder,
      '{$bankName}'      => $retObj->resCreate->bankName,
      '{$payText}'       => $retObj->resCreate->payText,
    );
    if (empty($bookObj->bic)) $repl['{$bic}'] = ''; // if no bic so no bic text
    $retObj->prepayTextDE = strtr($textDE, $repl);
    $retObj->prepayTextEN = strtr($textEN, $repl);

    if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->retObj: '.print_r($retObj, 1));
    return $retObj;
  }

  /**
   * refundPrepay
   *
   * @param $order
   * @param $config
   *
   * @return bool|\stdClass
   * @throws \Exception
   */
  function refundPrepay($order, $config)
  {
    if (empty($config['sessid'])) throw new Exception('SessId cant be empty');
    if (empty($config['amount'])) throw new Exception('Amount cant be empty');

    // switch to cent instead of eur
    $config['amount'] = (INT)($config['amount'] * 100);

    if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->config: '.print_r($config, 1));

    $this->setConfig($config);
    $this->switchPaymethod('prepay');

    try {
      $sessionID       = $config['sessid'];
      $refundAmount    = $config['amount'];
      $bookObj         = new stdClass();
      $bookObj->amount = ($order->get_total() * 100) - $refundAmount; // in CENT
      $res             = $this->sessionChange($sessionID, $bookObj);
      mail('test@test.test', __CLASS__.'->'.__FUNCTION__.'->sessionChange: ', print_r($res, 1));
    } catch (Exception $e) {
      throw new Exception('sessionChange failed. '.$e->getMessage());
    }

    $res->sessionId = $sessionID;
    return $res;
  }

  /**
   * getPaymentWindowParams
   *
   * @param $order
   * @param $config
   * @param $pm
   *
   * @return string
   */
  function getPaymentWindowParams($userData, $config, $pm)
  {
    if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->config('.$pm.'): '.print_r($config, 1));

    $params = array(
      'project'  => $config['project'],
      'testmode' => (int)(bool)$config['testMode'],
      'theme'    => $config['theme'],
      'gfx'      => $config['gfx'],
      'bgcolor'  => $config['bgcolor'],
      'bggfx'    => $config['bggfx'],
      'amount'   => $userData['amount'],
      'title'    => $_SERVER['HTTP_HOST'].' - ORDER '.$userData['order_id'],
      'paytext'  => str_replace('{$orderid}', $userData['order_id'], $config['paytext']),
      'currency' => $userData['currency'],
      'orderid'  => $userData['order_id'],

      'mp_user_email'     => $userData['email'],
      'mp_user_firstname' => $userData['firstName'],
      'mp_user_surname'   => $userData['lastName'],
      'mp_user_address'   => $userData['street'],
      'mp_user_zip'       => $userData['zip'],
      'mp_user_city'      => $userData['city'],
      'mp_user_country'   => $userData['country'],
      'mp_user_id'        => $userData['id'],
      'lang'              => strtolower($userData['lang']),
    );
    if (in_array($pm, array('sofort', 'obt'))) {
      $params['lang'] = strtoupper($params['lang']);
    }
    // check if extra params given
    if (!empty($config['extraParams'])) {
      foreach ($config['extraParams'] AS $k => $v) {
        // check if already filled. no overwrite !
        if (empty($params[$k])) {
          $params[$k] = $v;
        }
      }
    }
    $query  = http_build_query($params, '', '&');
    $seal   = md5($query.$config['accessKey']);
    $url    = str_replace('micropayment.de', $config['suffix'], $this->mcpayEventURLs[$pm]);
    $return = $url.'?'.$query.'&seal='.$seal;
    if ($this->debug) $this->log->debug(__CLASS__.'->'.__FUNCTION__.'->$params: '.print_r($params, 1).$return);
    return $return;
  }

  /**
   * getBaseJS
   *
   * @param int  $tokenize
   * @param int  $autobill
   * @param bool $formSubmit
   * @param bool $debug
   * @param null $formID
   *
   * @return string
   */
  public function getBaseJS($tokenize = 1, $autobill = 0, $formSubmit = TRUE, $debug = FALSE, $formID = NULL)
  {
    $jscript = '<script src="'.$this->mcpayBridgeURL.'"
			project 		= "'.$this->project.'"
			testmode 		= "'.(int)(bool)$this->testMode.'"
			panformat 	= "1"
			tokenize 		= "'.$tokenize.'"
			error 			= "'.$this->error_color.'"
			error_off 	= "'.$this->error_off_color.'"
			prefix 			= "'.$this->prefixCCard.'"
			autobill 		= "'.$autobill.'"
    ';
    if (!empty($formID)) {
      $jscript .= ' form = "'.$formID.'" ';
    }
    if ($formSubmit) {
      $jscript .= ' form_submit = "auto" ';
    } else {
      $jscript .= ' form_submit = "off" ';
    }
    if ($debug) $jscript .= ' debug = 1 ';
    //$jscript.= ' trxid = "1234567890-'.$tokenize.'"';
    $jscript .= '></script>';
    return $jscript;
  }

  /**
   * getBaseJSInline
   *
   * @param int  $tokenize
   * @param int  $autobill
   * @param bool $formSubmit
   * @param bool $debug
   * @param null $formID
   *
   * @return string
   */
  public function getBaseJSInline($tokenize = 1, $autobill = 0, $formSubmit = FALSE, $debug = FALSE, $formID = NULL)
  {
    $jscript = 'var s = document.createElement("script");
      s.setAttribute( "src", "'.$this->mcpayBridgeURL.'" );
      s.setAttribute( "project", "'.$this->project.'" );
      s.setAttribute( "testmode", "'.(int)(bool)$this->testMode.'" );
      s.setAttribute( "panformat", "1" );
      s.setAttribute( "tokenize", "'.$tokenize.'" );
      s.setAttribute( "error", "'.$this->error_color.'" );
      s.setAttribute( "error_off", "'.$this->error_off_color.'" );
      s.setAttribute( "prefix", "'.$this->prefixCCard.'" );
      s.setAttribute( "autobill", "'.$autobill.'" );
    ';
    if (!empty($formID)) {
      $jscript .= ' s.setAttribute( "form", "'.$formID.'" ); ';
    }
    if ($formSubmit) {
      $jscript .= ' s.setAttribute( "form_submit", "auto" ); ';
    } else {
      $jscript .= ' s.setAttribute( "form_submit", "off" ); ';
    }
    if ($debug) $jscript .= ' s.setAttribute( "debug", "1" ); ';
    $jscript .= ' document.head.appendChild(s); ';
    return $jscript;
  }

  /**
   * getInlineJS
   * get outer part of the inline script
   * to inject the jscript via jscript
   *
   * @param string $mcpayJSInline - return of getBaseJSIline()
   *
   * @return string
   */
  private function getInlineJS($mcpayJSInline)
  {
    $jscript = '//console.log("getInlineJS");
      // remember first event
      var CHDone = false;
      // get '.$this->prefixCCard.'holder input to set onfocus event
      var CH = document.getElementById("'.$this->prefixCCard.'holder");
      // add onfocus event
      CH.onfocus = function(){
        // console.log("mcpay CH started");
        // check first event done
        if (CHDone) return;
        doMIPA();
      };
      var CP = document.getElementById("'.$this->prefixCCard.'pan");
      // add onclick event
      CP.onclick = function(){
        // console.log("mcpay CP started");
        // check first event done
        if (CHDone) return;
        doMIPA();
      };
      
      function doMIPA()
      {
        // set first event done
        CHDone = true;
        '.$mcpayJSInline.'
        // when micropayment script loaded
        s.onload = function(){
          // add styling script for iframe fields
          var s2 = document.createElement("script");
          s2.setAttribute( "src", "'.$this->plugin_urljs.'mcpay_formstyle_ccard_iframe.js" );
          document.head.appendChild(s2);
        };
      }
    ';
    return $jscript;
  }

  /**
   * getStartJS
   *
   * @param bool $inline
   * @param bool $formSubmit
   * @param bool $debug
   * @param null $formID
   *
   * @return string
   */
  public function getStartJS($inline = FALSE, $formSubmit = FALSE, $debug = FALSE, $formID = NULL)
  {
    if ($inline) {
      $mcpayJSInline = $this->getBaseJSInline(1, 0, $formSubmit, $debug, $formID);
      $jscript       = $this->getInlineJS($mcpayJSInline);
    } else {
      $jscript = $this->getBaseJS(1, 0, $formSubmit, $debug, $formID).
        '<script src="'.$this->plugin_urljs.'mcpay_formstyle_ccard_check.js"></script>'.
        '<script src="'.$this->plugin_urljs.'mcpay_formstyle_ccard_iframe.js"></script>'.
        '<link href="'.$this->plugin_urlcss.'mcpay_formstyle_ccard.css" rel="stylesheet" />';
    }
    return $jscript;
  }

  /**
   * getFinishJS
   *
   * @param        $sessionID
   * @param        $responseURL
   * @param string $method
   * @param bool   $formSubmit
   * @param bool   $debug
   * @param null   $formID
   *
   * @return string
   */
  public function getFinishJS($sessionID, $responseURL, $method = 'Purchase', $formSubmit = FALSE, $debug = FALSE, $formID = NULL)
  {
    $possibleVals = array('Purchase', 'Authorization');
    if (!in_array($method, $possibleVals)) $method = 'Purchase';
    //mail('te@st.de', __CLASS__.'->'.__FUNCTION__, 'getFinishJS');
    $jscript = $this->getBaseJS(0, 0, $formSubmit, $debug, $formID).
      '<script type="text/javascript">
				var prefix = "'.$this->prefixCCard.'";
				getElem = function(name)
        {
          return document.getElementById(name);
        }
        getElem(prefix+"error").onclick = function()
        {
          history.back();
        }
        getElem(prefix+"modal").onclick = function()
        {
          history.back();
        }
				onError = function(e)
        {
          console.log(e.msg);
          getElem(prefix+"error").innerHTML = e.msg+"";
          getElem(prefix+"error").setAttribute("class", "show");
        }
        window.onload = function(){
				  Micropayment.addEvent("error", onError);
				  Micropayment.session("'.$sessionID.'", "'.$responseURL.'", "", "dieId", "'.$method.'");
				  Micropayment.process();
				}
			</script>';
    $output  = '<div id="'.$this->prefixCCard.'error" class="hide"></div>';
    $output  .= '<div id="'.$this->prefixCCard.'load" class="show"></div>';
    $output  .= '<div id="'.$this->prefixCCard.'modal" class="show"></div>';
    $output  .= $jscript;
    $output  .= '<link href="'.$this->plugin_urlcss.'mcpay_formstyle_ccard.css" rel="stylesheet" />';
    return $output;
  }

  /**
   * getCCardTPL
   *
   * @param       $filename
   * @param array $options
   * @param array $prefillData
   * @param array $translation
   *
   * @return bool|string
   */
  public function getCCardTPL($filename, $options = array(), $prefillData = array(), $translation = array())
  {
    if (empty($filename)) return FALSE;

    $formTPL = file_get_contents($filename);

    $imgStyle = 'float: right; padding-right: 9px; margin-top: -25px;';
    if (!empty($options['imgStyle'])) {
      $imgStyle = $options['imgStyle'];
    }
    if (!empty($options['hideImg'])) {
      $imgStyle = 'display: none;';
    }

    $years = '<option value="0">Year</option>';
    for ($i = date('y'); $i <= (date('y') + $options['futureYears']); $i++) {
      $selected = '';
      if ($options['preSeletActDate'] && date('y') == $i) {
        $selected = ' selected';
      }
      if (!empty($options['prefillPost'][$this->prefixCCard.'year']) && $options['prefillPost'][$this->prefixCCard.'year'] == $i) {
        $selected = ' selected';
      }
      $years .= '<option value="'.$i.'" '.$selected.'>'.($i + 2000).'</option>';
    }

    $month = '<option value="0">Month</option>';
    for ($i = 1; $i <= 12; $i++) {
      $selected = '';
      if ($options['preSeletActDate'] && date('m') == $i) {
        $selected = ' selected';
      }
      if (!empty($options['prefillPost'][$this->prefixCCard.'month']) && $options['prefillPost'][$this->prefixCCard.'month'] == $i) {
        $selected = ' selected';
      }
      $month .= '<option value="'.$i.'"'.$selected.'>';
      if (!empty($options['showMonthNames'])) {
        $month .= '#'.date('M', mktime(0, 0, 0, $i)).'#'.' ('.sprintf('%02d', $i).')';
      } else {
        //$month .= date('m', mktime(0, 0, 0, $i));
        $month .= sprintf('%02d', $i); // use numbers only because date function makes problems at the end of month
      }
      $month .= '</option>';
    }

    $showNewForm = 'show';
    $checked     = 'checked'; // preselect first saved
    if (!empty($options['preselectNewCard'])) {
      $showNewForm = 'hide';
      $checked     = '';
    }
    $checkedOP = 'checked';
    if ($checked == 'checked') $checkedOP = '';
    //$remember = '';
    //if ($options['showRemember']) {
    $remember = '<br><div id="'.$this->prefixCCard.'remember"';
    if (!empty($options['showRemember'])) {
      $remember .= 'class="show"';
    } else {
      $remember .= 'class="hide"';
    }
    $remember .= '><input type="checkbox" id="'.$this->prefixCCard.'remember_chk" name="'.$this->prefixCCard.'remember" value="1">';
    $remember .= '<label for="'.$this->prefixCCard.'remember_chk">Remember Data</label></div>';
    //}
    $savedCards = '';
    $newCard    = '';
    if (!empty($prefillData)) {
      $newCard .= '<div class="reuse">';
      $newCard .= '<input type="radio" id="'.$this->prefixCCard.'reuse" name="'.$this->prefixCCard.'reuse" value="new" onclick="handleCCardRadioClick(this);" '.$checkedOP.'>';
      $newCard .= '<label for="'.$this->prefixCCard.'reuse">Use new Card</label><br>';
      $newCard .= '</div>';
      //$checked = 'checked';
      foreach ($prefillData AS $k => $v) {
        //$checked    = $v['checked'];
        $savedCards .= '<div class="reuse prefill">';
        if (!empty($options['cardSelect'])) {
          $savedCards .= '<span class="'.$this->prefixCCard.'reuseselect">';
          $savedCards .= '<input type="radio" name="'.$this->prefixCCard.'reuse" value="'.$v['token'].'" onclick="handleCCardRadioClick(this);" '.$checked.'>';
        } else {
          $savedCards .= '<span class="'.$this->prefixCCard.'reuse_select">';
          $savedCards .= '<input type="radio" id="'.$this->prefixCCard.'reuse_old" name="'.$this->prefixCCard.'reuse" value="'.$v['token'].'" onclick="handleCCardRadioClick(this);" '.$checked.'>';
          $savedCards .= '<label for="'.$this->prefixCCard.'reuse_old">Use old Card</label><br>';
        }
        $savedCards .= '<div id="'.$this->prefixCCard.'reuse_card" class="'.$showNewForm.'">';
        if (!empty($options['cardStyle'])) {
          if (!empty($options['specialBrand'])) {
            $brand = substr($v['holder'], 0, strpos($v['holder'], '(') - 1);
          } else {
            $brand = $v['brand'];
          }
          if (!empty($options['specialHolder'])) {
            $holder = substr($v['holder'], strpos($v['holder'], '(') + 1, -1);
          } else {
            $holder = $v['holder'];
          }
          $savedCards .= '<div class="'.$this->prefixCCard.'reusecard">';
          //$savedCards .= '<img src="' . $options['imgPath'] . $brand . '.png">';
          $savedCards .= '<img src="'.$this->logoURLs[$brand].'">';

          $savedCards .= '<span class="'.$this->prefixCCard.'cardlabel">NAME:</span>'.$holder.'<br>';
          $savedCards .= '<span class="'.$this->prefixCCard.'cardlabel">PAN:</span>'.$v['pan'].'<br>';
          $savedCards .= '<span class="'.$this->prefixCCard.'cardlabel">EXPIRE:</span>'.$v['month'].'/'.$v['year'].'<br>';
          $savedCards .= '</div>';
        } else {
          $savedCards .= 'Holder: '.$v['holder'].' CardNo: '.$v['pan'].' Expire: '.$v['month'].'/'.$v['year'];
        }
        $savedCards .= '</span>';
        if (!empty($options['showRemove'])) {
          $savedCards .= '<span style="display: inline-block; float: right; margin-top: -5px">';
          $savedCards .= '<input type="checkbox" name="'.$this->prefixCCard.'remove[]" value="'.$k.'"> Remove';
          $savedCards .= '</span>';
        }
        $savedCards .= '</div>';
        $savedCards .= '</div>';
        //$checked = '';
      }
      if ($showNewForm == 'show')
        $showNewForm = 'hide';
      else
        $showNewForm = 'show';
    }

    $repl    = array(
      'value="" placeholder="Vorname Nachname' => 'value="'.$options['prefillPost'][$this->prefixCCard.'holder'].'" placeholder="Vorname Nachname',
      '<option>Year</option>'                  => $years,
      '<option>Month</option>'                 => $month,
      '<div id="PREFIX_remember"></div>'       => $remember,
      '<div id="PREFIX_reuse"></div>'          => $savedCards,
      '<div id="PREFIX_newcard"></div>'        => '<div id="'.$this->prefixCCard.'newcard">'.$newCard.'</div>',
      '<div id="PREFIX_newform" class="show">' => '<div id="'.$this->prefixCCard.'newform" class="'.$showNewForm.'">',
      'PREFIX_'                                => $this->prefixCCard,
      '{IMG_STYLE}'                            => $imgStyle,
    );
    $formTPL = strtr($formTPL, $repl);
    if (!empty($translation)) {
      $formTPL = strtr($formTPL, $translation);
    }

    return $formTPL;
  }

  /**
   * getSepaTPL
   *
   * @param       $filename
   * @param array $options
   * @param array $prefillData
   * @param array $translation
   *
   * @return bool|false|string
   */
  public function getSepaTPL($filename, $options = array(), $prefillData = array(), $translation = array())
  {
    if (empty($filename)) return FALSE;
    $crlf = "\n";
    $tab = '  ';

    $formTPL = file_get_contents($filename);

    $imgStyle = 'float: right; padding-right: 9px; margin-top: -25px;';
    if (!empty($options['imgStyle'])) {
      $imgStyle = $options['imgStyle'];
    }
    if (!empty($options['hideImg'])) {
      $imgStyle = 'display: none;';
    }

    $showNewForm = 'show';
    //$remember = '';
    //if ($options['showRemember']) {
    $remember = '<br><div id="'.$this->prefixSepa.'remember"';
    if (!empty($options['showRemember'])) {
      $remember .= 'class="show"';
    } else {
      $remember .= 'class="hide"';
    }
    $remember .= '><input type="checkbox" id="'.$this->prefixSepa.'remember_chk" name="'.$this->prefixSepa.'remember" value="1">';
    $remember .= '<label for="'.$this->prefixSepa.'remember_chk">Remember Data</label></div>';
    //}
    $savedCards = '';
    $newCard    = '';
    if (!empty($prefillData)) {
      $newCard .= '<div class="reuse">'.$crlf;
      $newCard .= $tab.$tab.'<input type="radio" id="'.$this->prefixSepa.'reuse" name="'.$this->prefixSepa.'reuse" value="new" onclick="handleSepaRadioClick(this);">'.$crlf;
      $newCard .= $tab.$tab.'<label for="'.$this->prefixSepa.'reuse">Use new Card</label><br>'.$crlf;
      $newCard .= $tab.'</div>'.$crlf;
      //$checked = 'checked';
      foreach ($prefillData AS $k => $v) {
        $checked    = $v['checked'];
        $checked    = 'checked'; // preselect first saved
        $savedCards .= '<div class="reuse prefill">'.$crlf;
        $savedCards .= $tab.$tab.'<span class="'.$this->prefixSepa.'reuse_select">'.$crlf;
        $savedCards .= $tab.$tab.$tab.'<input type="radio" id="'.$this->prefixSepa.'reuse_old" name="'.$this->prefixSepa.'reuse" value="'.$k.'" onclick="handleSepaRadioClick(this);" '.$checked.'>'.$crlf;
        $savedCards .= $tab.$tab.$tab.'<label for="'.$this->prefixSepa.'reuse_old">Use old Card</label><br>'.$crlf;
        $savedCards .= $tab.$tab.$tab.'<div id="'.$this->prefixSepa.'reuse_card">'.$crlf;
        if (!empty($options['cardStyle'])) {
          $brand      = 'ec';
          $savedCards .= $tab.$tab.$tab.$tab.'<div class="'.$this->prefixSepa.'reusecard">'.$crlf;
          //$savedCards .= '<img src="' . $options['imgPath'] . $brand . '.png">';
          $savedCards .= $tab.$tab.$tab.$tab.$tab.'<img src="'.$this->logoURLs[$brand].'">'.$crlf;
          $savedCards .= $tab.$tab.$tab.$tab.$tab.'<span class="'.$this->prefixSepa.'cardlabel">NAME:</span>'.$v['holder'].'<br>'.$crlf;
          $savedCards .= $tab.$tab.$tab.$tab.$tab.'<input type="hidden" name="'.$this->prefixSepa.'reuse_holder" value="'.$v['holder'].'">'.$crlf;
          $savedCards .= $tab.$tab.$tab.$tab.$tab.'<span class="'.$this->prefixSepa.'cardlabel">IBAN:</span>'.$v['iban'].'<br>'.$crlf;
          $savedCards .= $tab.$tab.$tab.$tab.$tab.'<input type="hidden" name="'.$this->prefixSepa.'reuse_iban" value="'.$v['iban'].'">'.$crlf;
          $savedCards .= $tab.$tab.$tab.$tab.$tab.'<span class="'.$this->prefixSepa.'cardlabel">BIC:</span>'.$v['bic'].'<br>'.$crlf;
          $savedCards .= $tab.$tab.$tab.$tab.$tab.'<input type="hidden" name="'.$this->prefixSepa.'reuse_bic" value="'.$v['bic'].'">'.$crlf;
          $savedCards .= $tab.$tab.$tab.$tab.'</div>'.$crlf;
        } else {
          $savedCards .= 'Holder: '.$v['holder'].' IBAN: '.$v['iban'].' BIC: '.$v['bic'].$crlf;
        }
        if (!empty($options['showRemove'])) {
          $savedCards .= $tab.$tab.$tab.'<span style="display: block;  margin-top: -30px;margin-left: 215px;text-align: left;margin-bottom: 10px;">'.$crlf;
          $savedCards .= $tab.$tab.$tab.'<input type="checkbox" name="'.$this->prefixSepa.'remove[]" value="'.$k.'"> Remove'.$crlf;
          $savedCards .= $tab.$tab.$tab.'</span>'.$crlf;
        }
        $savedCards .= $tab.$tab.$tab.'</div>'.$crlf;
        $savedCards .= $tab.$tab.'</span>'.$crlf;
        $savedCards .= $tab.'</div>'.$crlf;
        //$checked = '';
      }
      $showNewForm = 'hide';
    }

    $repl = array(
      'PREFIX_holder_val'                      => '',
      'PREFIX_iban_val'                        => '',
      'PREFIX_bic_val'                         => '',
      'PREFIX_holder_class'                    => '',
      'PREFIX_iban_class'                      => '',
      'PREFIX_bic_class'                       => '',
      '<div id="PREFIX_remember"></div>'       => $remember,
      '<div id="PREFIX_reuse"></div>'          => $savedCards,
      '<div id="PREFIX_newcard"></div>'        => '<div id="'.$this->prefixSepa.'newcard">'.$newCard.'</div>'.$crlf,
      '<div id="PREFIX_newform" class="show">' => '<div id="'.$this->prefixSepa.'newform" class="'.$showNewForm.'">',
      'PREFIX_'                                => $this->prefixSepa,
      '{IMG_STYLE}'                            => $imgStyle,
    );
    if ($this->debug) {
      if (strpos($_SERVER['REMOTE_ADDR'], '127.0.0') !== FALSE) {
        $repl['PREFIX_holder_val'] = 'Donald Duck';
        $repl['PREFIX_iban_val']   = 'DE89888888881234567890';
        $repl['PREFIX_bic_val']    = 'TESTDE00XXX';
      }
    }
    if (!empty($options['classes']['holder'])){
      $repl['PREFIX_holder_class'] = $options['classes']['holder'];
    }
    if (!empty($options['classes']['iban'])){
      $repl['PREFIX_iban_class'] = $options['classes']['iban'];
    }
    if (!empty($options['classes']['bic'])){
      $repl['PREFIX_bic_class'] = $options['classes']['bic'];
    }
    $formTPL = strtr($formTPL, $repl);
    if (!empty($translation)) {
      $formTPL = strtr($formTPL, $translation);
    }

    return $formTPL;
  }

  /**
   * findLogFile
   *
   * @param $logPath
   * @param $logFile
   *
   * @return bool|string
   */
  function findLogFile($logPath, $logFile = NULL)
  {
    $files = array();
    if ($handle = opendir($logPath)) {
      while (FALSE !== ($entry = readdir($handle))) {
        if (!empty($logFile)) {
          if (substr($entry, 0, strlen($logFile)) == $logFile) {
            return $logPath.$entry;
          }
        } else {
          if (strtolower(substr($entry, -4)) == '.log') $files[] = $entry;
        }
      }
      closedir($handle);
    }
    sort($files);
    if (!empty($files)) return $files;
    return FALSE;
  }

  /**
   * readLogFile
   *
   * @param      $logFile
   * @param bool $returnRAW
   *
   * @return array|bool|false|string
   */
  function readLogFile($logFile, $returnRAW = FALSE, $date = NULL)
  {
    if (empty($logFile)) return FALSE;

    $repl    = array(
      ': Array'           => ': XArray',
      ': stdClass Object' => ': XstdClass Object',
      'script'            => 'pre',
      '<div'              => '&lt;div',
      'div>'              => 'div&gt;',
    );
    $content = file_get_contents($logFile);
    if ($returnRAW) return nl2br($content);
    $content = strtr($content, $repl);
    //$content = htmlentities($content);
    //echo $content;

    $delimiter = $date.'T';
    //$p = explode("\n\n", $content);
    $p = explode($delimiter, $content);
    //echo '<pre>'.print_r($p,1).'</pre>'; exit();
    $content = array();
    foreach ($p AS $k => $v) {
      //echo substr($v, 19, 12)."\n";
      if (substr($v, 19 - strlen($delimiter), 12) == '+00:00 DEBUG') {
        $pp                       = explode('+00:00 DEBUG ', $v);
        $ppp                      = explode(": X", $pp[1]);
        $content[$pp[0]][$ppp[0]] = $ppp[1].'<br>'.$ppp[2];
        //$content[] = $ppp[0].'<hr>'.$ppp[1].'<br>'.$ppp[2];
      } else {
        continue;
      }
    }
    //echo '<pre>'.print_r($content,1).'</pre>'; exit();
    $output = '';
    foreach ($content AS $time => $dat) {
      $output .= '<h3>'.$time.'</h3>';
      foreach ($dat AS $k => $v) {
        $output .= '<pre>'.htmlentities($k).'<br>'.str_replace('&lt;br&gt;', '<br>', htmlentities($v)).'</pre>';
      }
      $output .= '<hr>';
    }

    $styleGray   = '<b style="background-color: lightgrey; padding:2px; color: black">';
    $styleGreen  = '<b style="background-color: green; padding:2px; color: white">';
    $styleYellow = '<b style="background-color: yellow; padding:2px; color: black">';
    $styleOrange = '<b style="background-color: orange; padding:2px; color: white">';
    $styleRed    = '<b style="background-color: red; padding:2px; color: white">';
    $stylePurple = '<b style="background-color: mediumpurple; padding:2px; color: white">';
    $styleBlue   = '<b style="background-color: blue; padding:2px; color: white">';
    $styleBlack  = '<b style="background-color: black; padding:2px; color: white">';
    $styleCyan   = '<b style="background-color: cyan; padding:2px; color: black">';
    $styleEnd    = '</b>';
    $repl        = array(
      'BOOKING'              => $styleGreen.'BOOKING'.$styleEnd,
      'Mickey Maus'          => $styleGreen.'Mickey Maus'.$styleEnd,
      'REVERSED'             => $styleOrange.'REVERSED'.$styleEnd,
      'STOP'                 => $styleRed.'STOP'.$styleEnd,
      'REVERSAL'             => $styleOrange.'REVERSAL'.$styleEnd,
      'BACKPAY'              => $styleBlue.'BACKPAY'.$styleEnd,
      'RECHARGED'            => $styleBlue.'RECHARGED'.$styleEnd,
      'responseAction'       => $styleBlack.'responseAction'.$styleEnd,
      'error:'               => $styleRed.'error:'.$styleEnd,
      '[status] =&gt; ERROR' => $styleRed.'[status] =&gt; ERROR'.$styleEnd,
      'trxInfo'              => $styleCyan.'trxInfo'.$styleEnd,
    );
    $output      = strtr($output, $repl);

    $statOutput = '';
    foreach ($repl AS $k => $v) {
      $count = preg_match_all('#'.preg_quote($k, '#').'#', $output);
      if ($count > 0) {
        $statOutput .= '<div style="display: inline-block; margin-right: 5px;">'.str_replace($k, $count.'x '.$k, $repl[$k]).'</div>';
      }
    }
    echo $statOutput;
    return $output;
  }

} // end of class
?>