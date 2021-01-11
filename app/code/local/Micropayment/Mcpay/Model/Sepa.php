<?php
class Micropayment_Mcpay_Model_Sepa extends Micropayment_Mcpay_Model_Standard
{
  protected $_code = 'sepa';
  protected $_formBlockType = 'mcpay/form_sepa';

  /**
   * getStyle
   *
   * @return string
   */
  public function getStyle()
  {
    $url = Mage::getUrl($this->baseRoute.'getstyle', array('_secure' => 1));
    $url.= 'sn/mcpay_formstyle_sepa.css';
    return $url;
  }

  /**
   * getScript
   *
   * @return string
   */
  public function getScript()
  {
    $url = Mage::getUrl($this->baseRoute.'getscript', array('sn' => 'mcpay_formstyle_sepa.js', '_secure' => 1));
    return $url;
  }

  /**
   * getMyForm
   *
   * @return bool|false|string
   */
  public function getMyForm()
  {
    $filename = MCPAY_BASE_PATH.'view/mcpay_form_sepa.tpl';

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
        $prefillData[$k+1] = array(
          'holder'  => $payDat['holder'],
          'iban'    => $payDat['iban'],
          'bic'     => $payDat['bic'],
          'checked' => 'checked',
        );
      }
    }
    //mail('webmaster@web-dezign.de', __CLASS__.'->'.__FUNCTION__.'->$prefillData', print_r($prefillData,1));

    // Template Translations
    $translation = array(
      'Name on Card'                => $this->_getHelper('mcpay')->__('Account Holder'),
      'Remember Data'               => $this->_getHelper('mcpay')->__('Remember Account for future purchase'),
      'Use new Card'                => $this->_getHelper('mcpay')->__('Use new Account'),
      'Use old Card'                => $this->_getHelper('mcpay')->__('Use old Account'),
      'Holder'                      => $this->_getHelper('mcpay')->__('Holder'),
      'IBAN'                        => $this->_getHelper('mcpay')->__('IBAN'),
      'BIC'                         => $this->_getHelper('mcpay')->__('BIC'),
      'Remove'                      => $this->_getHelper('mcpay')->__('Remove'),
      'Please check your inputs!'   => $this->_getHelper('mcpay')->__('Please check your inputs!'),
      'Vorname Nachname'            => $this->_getHelper('mcpay')->__('Firstname Lastname'),
      'NAME'                        => $this->_getHelper('mcpay')->__('Account Holder'),
    );

    $options = array(
      'showRemember' => TRUE,
      'showRemove' => FALSE,
      'cardStyle' => TRUE,
    );

    $formTPL = $this->McPay->getSepaTPL($filename, $options, $prefillData, $translation);
    $output = $formTPL;

    $output.= '<script>';
    if ($this->McPay->debug){
      $output.= 'console.log("MCPAY ModelSepa START");';
    }

    $output.= 'mcpay_sepa_execute();';

    if ($this->McPay->debug){
      $output.= 'console.log("MCPAY ModelSepa END");';
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
    if (!empty($req['payment']) && !empty($req['payment']['method']) && $req['payment']['method'] == $this->getMethodCode()) {

      if (!empty($req['mcpay_sepa_reuse']) && $req['mcpay_sepa_reuse'] != 'new') {
        // remove old session values
        $this->getSession()->unsMcpayHolder();
        $this->getSession()->unsMcpayIBAN();
        $this->getSession()->unsMcpayBIC();
        // set reuse values
        if (!empty($req['mcpay_sepa_reuse_holder'])) {
          $this->getSession()->setMcpayHolder($req['mcpay_sepa_reuse_holder']);
        }
        if (!empty($req['mcpay_sepa_reuse_iban'])) {
          $this->getSession()->setMcpayIBAN($req['mcpay_sepa_reuse_iban']);
        }
        if (!empty($req['mcpay_sepa_reuse_bic'])) {
          $this->getSession()->setMcpayBIC($req['mcpay_sepa_reuse_bic']);
        }

        $this->getSession()->setMcpaySepaReuse(TRUE);
      } else {
        $this->getSession()->setMcpaySepaReuse(FALSE);

        // holder
        if (empty($req['mcpay_sepa_holder'])) {
          Mage::throwException(Mage::helper('mcpay')->__('Account Holder is mandatory.'));
        } else {
          $this->getSession()->setMcpayHolder($req['mcpay_sepa_holder']);
        }
        // iban
        if (empty($req['mcpay_sepa_iban'])) {
          Mage::throwException(Mage::helper('mcpay')->__('IBAN is mandatory.'));
        } else {
          $this->getSession()->setMcpayIBAN(str_replace(' ', '', $req['mcpay_sepa_iban']));
        }
        // bic if not DE
        $paymentInfo = $this->getInfoInstance();
        if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
          $billingCountry = $paymentInfo->getOrder()->getBillingAddress()->getCountryId();
        } else {
          $billingCountry = $paymentInfo->getQuote()->getBillingAddress()->getCountryId();
        }
        if ($billingCountry != 'DE' && empty($req['mcpay_sepa_bic'])) {
          Mage::throwException(Mage::helper('mcpay')->__('BIC is mandatory.'));
        } else {
          $this->getSession()->setMcpayBIC($req['mcpay_sepa_bic']);
        }
        // save paymethod
        if (!empty($req['mcpay_sepa_remember'])) {
          $this->getSession()->setMcpaySepaRemember(TRUE);
        } else {
          $this->getSession()->setMcpaySepaRemember(FALSE);
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

    $payData = array(
      'holder'    => $this->getSession()->getMcpayHolder(),
      'iban'      => $this->getSession()->getMcpayIBAN(),
      'bic'       => $this->getSession()->getMcpayBIC(),
      'remember'  => $this->getSession()->getMcpayRemember(),
    );

    try{
      $res = $this->McPay->bookSepa($userData, $config, $payData);
      $this->McPay->log->log(__CLASS__.'->'.__FUNCTION__, $res); // log result
      $this->getSession()->setMcpayTrxResult($res); // save result into session
      //mail('webmaster@web-dezign.de', __CLASS__.'->'.__FUNCTION__.'->$res', print_r($res,1));
    } catch (Exception $e) {
      $this->McPay->log->log(__CLASS__.'->'.__FUNCTION__, $e->getMessage()); // log error
      Mage::throwException('bookSepa: '.$e->getMessage());
    }
    $return = new stdClass();
    $return->forward = TRUE;
    $return->url = Mage::getUrl($this->baseRoute.'success/', array('_secure' => TRUE)) . '?oId=' . $userData['order_id'];
    return $return;
  }

}