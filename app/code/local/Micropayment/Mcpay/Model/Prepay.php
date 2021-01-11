<?php

class Micropayment_Mcpay_Model_Prepay extends Micropayment_Mcpay_Model_Standard
{
  protected $_code          = 'prepay';
  protected $_formBlockType = 'mcpay/form_prepay';

  public function getPaymentData($lang)
  {
    $config               = $this->getBaseConfig();
    $config['lang']       = $lang;
    $config['expiredays'] = $this->getConfigData('expiredays');
    $config['textDE']     = $this->getConfigData('emailtextde');
    $config['textEN']     = $this->getConfigData('emailtexten');

    $order    = $this->getOrder();
    $userData = $this->getUserDataFromOrder($order, $config);

    try {
      $res = $this->McPay->bookPrepay($userData, $config);
      $this->McPay->log->log(__CLASS__.'->'.__FUNCTION__, $res); // log result
      $this->getSession()->setMcpayTrxResult($res); // save result into session
    } catch (Exception $e) {
      $this->McPay->log->log(__CLASS__.'->'.__FUNCTION__, $e->getMessage()); // log error
      Mage::throwException('bookPrepay: '.$e->getMessage());
    }
    $return          = new stdClass();
    $return->forward = TRUE;
    $return->url     = Mage::getUrl($this->baseRoute.'success/', array('_secure' => TRUE)).'?oId='.$userData['order_id'];
    return $return;
  }
}