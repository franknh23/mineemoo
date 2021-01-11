<?php
class Micropayment_Mcpay_Model_Ccard extends Micropayment_Mcpay_Model_Standard
{
  protected $_code = 'ccard';
  protected $_formBlockType = 'mcpay/form_ccard';

  /**
   * getStyle
   *
   * @return string
   */
  public function getStyle()
  {
    $url = Mage::getUrl($this->baseRoute.'getstyle', array('_secure' => 1));
    $url.= 'sn/mcpay_formstyle_ccard.css';
    return $url;
  }

  /**
   * getScript
   *
   * @return string
   */
  public function getScript()
  {
    $url = Mage::getUrl($this->baseRoute.'getscript', array('sn' => 'mcpay_formstyle_ccard_check.js', '_secure' => 1));
    return $url;
  }

  /**
   * getMyForm
   *
   * @return bool|false|string
   */
  public function getMyForm()
  {
    $filename = MCPAY_BASE_PATH.'view/mcpay_form_ccard.tpl';

    $prefillData = array();

    if(Mage::getSingleton('customer/session')->isLoggedIn()) {
      $customerData = Mage::getSingleton('customer/session')->getCustomer();
      $customerId = $customerData->getId();

      $collection = Mage::getModel('mcpayio/payio')->getCollection()->setOrder('paydata_id','asc');
      $collection->addFieldToFilter('user_id', $customerId);
      $collection->addFieldToFilter('paymethod', $this->getCode());

      $res = $collection->getData();
      //mail('webmaster@web-dezign.de', __CLASS__ . '->' . __FUNCTION__ . '->$res', print_r($res, 1));
      foreach($res AS $k => $v){
        $payDat = unserialize($v['data']);
        $prefillData[$k] = array(
          //'filldesc' => $token->get_display_name(),
          'token'   => $payDat['token'],
          'holder'  => $payDat['holder'],
          'pan'     => $payDat['pan'],
          'month'   => $payDat['month'],
          'year'    => $payDat['year'],
          'brand'   => $payDat['brand'],
          'checked' => 'checked',
        );
      }
    }
    //mail('webmaster@web-dezign.de', __CLASS__.'->'.__FUNCTION__.'->$prefillData', print_r($prefillData,1));


    $config = array(
      'project'     => $this->getSettingData('project_id'),
      'accessKey'   => $this->getSettingData('accesskey'),
      'testMode'    => $this->getSettingData('testmode'),
      'suffix'      => $this->getSettingData('suffix'),
      'theme'       => $this->getSettingData('theme'),
      'gfx'         => $this->getSettingData('gfx'),
      'bgcolor'     => $this->getSettingData('bgcolor'),
      'bggfx'       => $this->getSettingData('bggfx'),
      'paytext'     => $this->getConfigData('paytext'),
      'plugin_urljs' => Mage::getUrl('js', array('_secure' => 1)).'../'.$this->baseRoute.'getscript/sn/',
      'plugin_urlcss' => Mage::getUrl('js', array('_secure' => 1)).'../'.$this->baseRoute.'getstyle/sn/',
    );
    $this->McPay->setConfig($config);

    // Template Translations
    $translation = array(
      'Name on Card'                => $this->_getHelper('mcpay')->__('Card Holder'),
      'Remember Data'               => $this->_getHelper('mcpay')->__('Remember Card for future purchase'),
      'Use new Card'                => $this->_getHelper('mcpay')->__('Use new Card'),
      'Use old Card'                => $this->_getHelper('mcpay')->__('Use old Card'),
      'Holder'                      => $this->_getHelper('mcpay')->__('Holder'),
      'IBAN'                        => $this->_getHelper('mcpay')->__('IBAN'),
      'BIC'                         => $this->_getHelper('mcpay')->__('BIC'),
      'Remove'                      => $this->_getHelper('mcpay')->__('Remove'),
      'Please check your inputs!'   => $this->_getHelper('mcpay')->__('Please check your inputs!'),
      'Vorname Nachname'            => $this->_getHelper('mcpay')->__('Firstname Lastname'),
      'Credit Card Number'          => $this->_getHelper('mcpay')->__('Card Number'),
      'Expiration Date'             => $this->_getHelper('mcpay')->__('ExpDate'),
      'Card CVC'                    => $this->_getHelper('mcpay')->__('CVC'),
      'Month'                       => $this->_getHelper('mcpay')->__('Month'),
      'Year'                        => $this->_getHelper('mcpay')->__('Year'),
      'NAME'                        => $this->_getHelper('mcpay')->__('Card Holder'),
      'PAN'                         => $this->_getHelper('mcpay')->__('Card Number'),
      'EXPIRE'                      => $this->_getHelper('mcpay')->__('ExpDate'),
    );

    $options = array(
        'futureYears'       => 10,
        'preSeletActDate'   => FALSE,
        'showMonthNames'    => FALSE,
        'showRemember'      => TRUE,
        'showRemove'        => FALSE,
        'cardStyle'         => TRUE,
        'cardSelect'        => FALSE,
        'specialBrand'      => FALSE,
        'specialHolder'     => FALSE,
        'prefillPost'       => FALSE,
    );

    $formTPL = $this->McPay->getCCardTPL($filename, $options, $prefillData, $translation);
    $output = $formTPL;

    $output.= '<script>';
    if ($this->McPay->debug){
      $output.= 'console.log("MCPAY ModelCCard START"); ';
    }

    $payFormID = (bool)$this->getSettingData('payformid');
    $inlineCode = $this->McPay->getStartJS(true, false, $this->McPay->debug, $payFormID);
    $output.= 'mcpay_ccard_execute(); '.$inlineCode.' ';

    if ($this->McPay->debug){
      $output.= 'console.log("MCPAY ModelCCard END"); ';
    }
    $output.= '</script>';

    return $output;
  }

  /**
   * validate
   *
   * @return $this|\Mage_Payment_Model_Abstract|\Micropayment_Mcpay_Model_Standard
   * @throws \Mage_Core_Exception
   */
  public function validate()
  {
    parent::validate();
    $req = Mage::app()->getRequest()->getParams();
    if ($this->McPay->debug) $this->McPay->log->debug(__CLASS__.'->'.__FUNCTION__ . '->req: '.print_r($req, 1));
    if (!empty($req['payment']) && !empty($req['payment']['method']) && $req['payment']['method'] == $this->getMethodCode()){

      if (!empty($req['mcpay_card_reuse']) && $req['mcpay_card_reuse'] != 'new'){
        // no inputs needed
        $this->getSession()->setMcpayCardReuse(TRUE);
      } else {
        $this->getSession()->setMcpayCardReuse(FALSE);

        // holder
        if (empty($req['mcpay_card_holder'])) {
          Mage::throwException(Mage::helper('mcpay')->__('Card Holder is mandatory.'));
        } else {
          $this->getSession()->setMcpayCardHolder($req['mcpay_card_holder']);
        }
        // month
        if (empty($req['mcpay_card_month'])) {
          Mage::throwException(Mage::helper('mcpay')->__('Expire Month is mandatory.'));
        } else {
          $this->getSession()->setMcpayCardMonth($req['mcpay_card_month']);
        }
        // year
        if (empty($req['mcpay_card_year'])) {
          Mage::throwException(Mage::helper('mcpay')->__('Expire Year is mandatory.'));
        } else {
          $this->getSession()->setMcpayCardYear($req['mcpay_card_year']);
        }
        // pan
        if (empty($req['mcpay_card_pan'])) {
          Mage::throwException(Mage::helper('mcpay')->__('Card Pan is mandatory.'));
        } else {
          $this->getSession()->setMcpayCardPan($req['mcpay_card_pan']);
        }
        // cvc
        if (empty($req['mcpay_card_cvc'])) {
          Mage::throwException(Mage::helper('mcpay')->__('Card CVC is mandatory.'));
        } else {
          $this->getSession()->setMcpayCardCVC($req['mcpay_card_cvc']);
        }
        // token
        if (empty($req['mcpay_card_token'])) {
          Mage::throwException(Mage::helper('mcpay')->__('Card Token is mandatory.'));
        } else {
          $this->getSession()->setMcpayCardToken($req['mcpay_card_token']);
        }

        // save paymethod
        if (!empty($req['mcpay_card_remember'])) {
          $this->getSession()->setMcpayCardRemember(TRUE);
        } else {
          $this->getSession()->setMcpayCardRemember(FALSE);
        }
      }

    }
    return $this;
  }

  /**
   * getPaymentData
   *
   * @param $lang
   *
   * @return \stdClass
   * @throws \Mage_Core_Exception
   */
  public function getPaymentData($lang)
  {
    $config = array(
      'project'     => $this->getSettingData('project_id'),
      'accessKey'   => $this->getSettingData('accesskey'),
      'testMode'    => $this->getSettingData('testmode'),
      'suffix'      => $this->getSettingData('suffix'),
      'theme'       => $this->getSettingData('theme'),
      'gfx'         => $this->getSettingData('gfx'),
      'bgcolor'     => $this->getSettingData('bgcolor'),
      'bggfx'       => $this->getSettingData('bggfx'),
      'paytext'     => $this->getConfigData('paytext'),
      'lang'        => $lang,
    );
    $config['textDE'] = $this->getConfigData('emailtextde');
    $config['textEN'] = $this->getConfigData('emailtexten');

    $order = $this->getOrder();
    $userData = $this->getUserDataFromOrder($order, $config);

    $config['returnURL'] = Mage::getUrl($this->baseRoute.'threedsecure/', array('_secure' => TRUE)) . '?oId=' . $userData['order_id'];
    $config['returnURLNO3D'] = Mage::getUrl($this->baseRoute.'threedsecure/', array('_secure' => TRUE)) . '?oId=' . $userData['order_id'].'&check3dId=NO3D&mipaSID=';

    $payData = array(
      'holder'    => $this->getSession()->getMcpayCardHolder(),
      'month'     => $this->getSession()->getMcpayCardMonth(),
      'year'      => $this->getSession()->getMcpayCardYear(),
      'pan'       => $this->getSession()->getMcpayCardPan(),
      'cvc'       => $this->getSession()->getMcpayCardCVC(),
      'token'     => $this->getSession()->getMcpayCardToken(),
      'remember'  => $this->getSession()->getMcpayCardRemember(),
    );

    $return = new stdClass();
    $return->forward = TRUE;

    try {

      if ($this->getSession()->getMcpayCardReuse()){
        $res = $this->McPay->reuseCard($userData, $config);
      } else {
        $res = $this->McPay->bookCCard($userData, $config, $payData);
      }
      $this->McPay->log->log(__CLASS__.'->'.__FUNCTION__, $res); // log result
      $this->getSession()->setMcpayTrxResult($res); // save result into session

      $return->res = $res; // return result too
      if (!empty($res->forward)){
        $return->url = $res->forwardURL;
        return $return;
      }

    } catch (Exception $e) {
      $this->McPay->log->log(__CLASS__.'->'.__FUNCTION__, $e->getMessage()); // log error
      Mage::throwException('bookCCard: '.$e->getMessage());
    }


    $return->url = Mage::getUrl($this->baseRoute.'success/', array('_secure' => TRUE)) . '?oId=' . $userData['order_id'];
    return $return;
  }

  /**
   * check3D
   *
   * @param $check3dId
   *
   * @return bool
   * @throws \Mage_Core_Exception
   */
  public function check3D($check3dId)
  {
    if (empty($check3dId)){
      return false;
    }
    if ($check3dId == 'NO3D'){
      $trxResult = $this->getSession()->getMcpayTrxResult();
      $return = new stdClass();
      $return->error = FALSE;
      $return->sessionId = $trxResult->trxInfo->sessionId;
      $return->transactionId = $trxResult->trxInfo->transactionId;
      if ($this->McPay->debug) $this->McPay->log->debug(__CLASS__.'->'.__FUNCTION__ . '->return: '.$check3dId.print_r($return, 1));
      return $return;
    }

    $config = array(
      'project'     => $this->getSettingData('project_id'),
      'accessKey'   => $this->getSettingData('accesskey'),
      'testMode'    => $this->getSettingData('testmode'),
    );

    $return =  $this->McPay->verify3D($check3dId, $config);
    if ($this->McPay->debug) $this->McPay->log->debug(__CLASS__.'->'.__FUNCTION__ . '->return: '.$check3dId.print_r($return, 1));
    return $return;
  }
}