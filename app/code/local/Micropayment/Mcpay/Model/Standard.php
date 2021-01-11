<?php
if (!defined('MCPAY_BASE_PATH')) {
  $mcpayPath = Mage::getBaseDir('code').'/local/Micropayment/Mcpay/';
  define('MCPAY_BASE_PATH', $mcpayPath);
}
class Micropayment_Mcpay_Model_Standard extends Mage_Payment_Model_Method_Abstract
{
	protected $_code = 'mcpay';

  /**
	 * Availability options
   */
  protected $_isGateway = false;
  protected $_canAuthorize = false;
  protected $_canCapture = false;
  protected $_canCapturePartial = false;
  protected $_canRefund = false;
  protected $_canRefundInvoicePartial = false;
  protected $_canVoid = false;
  protected $_canUseInternal = false;
  protected $_canUseCheckout = true;
  protected $_canUseForMultishipping = false;
  protected $_isInitializeNeeded = true;
  protected $_paymentMethod = 'abstract';

	/**
	 * Default language
   */
  protected $_defaultLocale = 'en';
	protected $_localeDefault = 'en';

	/**
	 * Payment specific data
	 */
	protected $_payment_url;
	protected $_request = array();
  protected $_allowCurrencyCode = array('CHF','EUR','GBP','USD');
  protected $actualPaymethod;

  protected $_formBlockType = 'mcpay/form_default';
	protected $_infoBlockType = 'mcpay/info_payment';
	protected $baseRoute = 'micropayment/payment/';
	protected $McPay;

  /**
   * Micropayment_Mcpay_Model_Standard constructor.
   */
	public function __construct ()
  {
    $debug = (bool)$this->getSettingData('debugmode');
    $debugLog = $this->getSettingData('debuglog');

    require_once (MCPAY_BASE_PATH.'Classes/class.mcpay.php');
    $this->McPay = new mcpay($debug, $debugLog);

    $this->_allowCurrencyCode = $this->McPay->supportedCurrencies;
  }

  /**
   * getOrderPlaceRedirectUrl
   *
   * @return string
   */
	public function getOrderPlaceRedirectUrl()
  {
		return Mage::getUrl('mcpay/payment/redirect', array('_secure' => true));
	}

  /**
   * getPaymentInfo
   *
   * @return string
   */
  public function getPaymentInfo()
  {
    $info = '<div style="font-size: 10px;display: inline-block;color: #bbb">powered by <a href="http://www.micropayment.de" title="Payment Service Provider" target="_blank" style="float: none;margin: 0;color: #000">micro<span style="color: #800">payment</span></a></div>';
    $info = '';
    return $info;
  }

  /**
   * getPoweredBy
   *
   * @return string
   */
  public function getPoweredBy()
  {
    $info = '<div style="font-size: 10px;display: inline-block;color: #bbb; float: right">powered by <a href="http://www.micropayment.de" title="Payment Service Provider" target="_blank" style="float: none;margin: 0;color: #000">micro<span style="color: #800">payment</span></a></div>';
    return $info;
  }

  /**
   * getFrontendLogo
   *
   * @return string
   */
  public function getFrontendLogo()
  {
    $logoHTML = $this->getLogo() . $this->getPaymentInfo();
    //$html = '<div style="margin-left: 100px; margin-top: -20px;">' . $logoHTML . '</div>';
    $html = '<div style="float: left">' . $logoHTML . '</div>';
    return $html;
  }

  /**
   * Retrieve block type for method form generation
   *
   * @return string
   */
  public function getFormBlockType()
  {
    return $this->_formBlockType;
  }

  /**
   * Retirve block type for display method information
   *
   * @return string
   */
  public function getInfoBlockType()
  {
   	return $this->_infoBlockType;
 	}

  /**
   * getMethodCode
   *
   * @return string
   */
  public function getMethodCode()
  {
    return $this->getCode();
  }

  /**
   * getCode
   *
   * @return string
   */
  public function getCode()
  {
    return 'mcpay_'.$this->_code;
  }

  /**
   * validate
   *
   * @return $this|\Mage_Payment_Model_Abstract
   */
  public function validate()
  {
    parent::validate();
    return $this;
  }

  /**
   * getOrder
   *
   * @return mixed
   */
  public function getOrder()
  {
    if (!$this->_order) {
      $paymentInfo = $this->getInfoInstance();
      $order = $paymentInfo->getOrder();
      #echo '<pre>'.print_r($order, 1).'</pre>';
      $incID = $order->getRealOrderId();
      $this->_order = Mage::getModel('sales/order')->loadByIncrementId($incID);
    }
    return $this->_order;
  }

  /**
   * getTitle
   *
   * @return string
   */
	public function getTitle()
	{
		return $this->_getHelper('mcpay')->__($this->getConfigData('title')); // shown in list payment infos while checkout
	}

  /**
   * getLogo
   *
   * @return string
   */
  public function getLogo()
  {
    $title = $this->_getHelper('mcpay')->__($this->getConfigData('title'));
    $img = $this->_getHelper('mcpay')->__($this->getConfigData('logo'));
    return '<img src="'.$img.'"  title="'.$title.'" style="vertical-align: top; display: inline-block; float: none; padding: 0 10px;"/>';
  }

  /**
   * getAdminTitle
   *
   * @return string
   */
	public function getAdminTitle()
	{
	  return $this->_getHelper('mcpay')->__($this->getConfigData('title'));
	}

  /**
   * Get checkout session namespace
   *
   * @return Mage_Checkout_Model_Session
   */
  public function getCheckout()
  {
    return Mage::getSingleton('checkout/session');
  }

  /**
   * Get current quote
   *
   * @return Mage_Sales_Model_Quote
   */
  public function getQuote()
  {
    return $this->getCheckout()->getQuote();
  }

  /**
	 * Get core session namespace
	 *
	 * @return Mage_Core_Model_Session
	 */
	public function getSession()
  {
    return Mage::getSingleton('core/session');
	}

  /**
   * isAvailable
   *
   * @param null $quote
   *
   * @return bool
   */
	public function isAvailable($quote = null)
  {
    // check VR Products
    if (in_array($this->_code, $this->McPay->onlyVRProductsPaymethods)){
      $VRCount = $this->getQuote()->getItemVirtualQty();
      $allCount = $this->getQuote()->getItemsQty();
      if ($VRCount != $allCount) return false;
    }

    //return true;
    return parent::isAvailable($quote);

  	# Allowed Currency
  	$currency_code=$this->getQuote()->getQuoteCurrencyCode();
    if (!empty($currency_code) && !in_array($currency_code,$this->_allowCurrencyCode)) return false;

    # Minimum and maximum amount
  	$totals = $this->getQuote()->getTotals();
    if(!isset($totals['grand_total']) ) return false;

    $amount = sprintf('%1.2f', $totals['grand_total']->getData('value'));
    $amount = $amount * 100;
  	$minamount = $this->getConfigData('min_amount');
    $maxamount = $this->getConfigData('max_amount');
  	if (is_numeric($minamount) && $minamount > 0 && $minamount > $amount) return false;
  	if (is_numeric($maxamount) && $maxamount > 0 && $maxamount < $amount) return false;
  	return parent::isAvailable($quote);
  }

  /**
   * getPaymentData
   *
   * @param $lang
   *
   * @return \stdClass
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
    $pm = $this->_code;

    $order = $this->getOrder();
    $userData = $this->getUserDataFromOrder($order, $config);

    $res = $this->McPay->getPaymentWindowParams($userData, $config, $pm);

    $return = new stdClass();
    $return->forward = TRUE;
    $return->url = $res;
    return $return;
  }

  /**
   * refundCall
   *
   * @param $order
   * @param $amount
   * @param $currency
   *
   * @return mixed
   * @throws \Mage_Core_Exception
   */
  public function refundCall($order, $amount, $currency)
  {
    $amount = sprintf('%1.2f', $amount / 100);

    $amountToRefund = $amount;
    $amountRefunded = $order->getBaseTotalOnlineRefunded();
    $amountFull = $order->getBaseGrandTotal();
    $amountOpen = $amountFull - $amountRefunded;
    if ($amountOpen > 0){
      if ($amountOpen < $amountToRefund){
        $order->addStatusHistoryComment('Refund to big.', $this->getCancelState())->save();
        Mage::throwException('Refund to big. '.$amountOpen.' '.$currency.' left.');
      }
    } else {
      $order->addStatusHistoryComment('Nothing to refund left.', $this->getCancelState())->save();
      Mage::throwException('Nothing to refund left.');
    }

    try {
      if ($amountFull > $amountToRefund) {
        // partial refund
        $order->addStatusHistoryComment('Partial Refund '.$amount.' '.$currency.' (REFUND)', $this->getCancelState());
      } else {
        // full refund
        $order->addStatusHistoryComment('Payment Refund (REFUND)', $this->getCancelState());
      }
      $newAmount = $amountRefunded + $amountToRefund;
      $order->setTotalRefunded($newAmount)
        ->setBaseTotalRefunded($newAmount)
        ->setBaseTotalOnlineRefunded($newAmount)
        ->save();

    } catch(Mage_Core_Exception $e) {
      Mage::throwException($e->getMessage());
    } catch(Exception $e) {
      Mage::throwException($e->getMessage());
    }
    return $order;
  }

  /**
   * refund
   *
   * @param \Varien_Object $payment
   * @param float          $amount
   *
   * @return $this|\Mage_Payment_Model_Abstract
   */
  public function refund($payment, $amount)
  {
    $payment->getLastTransId();
    return $this;
  }

  /**
   * getPaymentState
   *
   * @return mixed|string
   */
  public function getPaymentState()
	{
		return $this->getConfigData('payment_status') ? $this->getConfigData('payment_status') : Mage_Sales_Model_Order::STATE_PROCESSING;
	}

  /**
   * getOrderState
   *
   * @return mixed|string
   */
	public function getOrderState()
	{
		return $this->getConfigData('order_status') ? $this->getConfigData('order_status') : Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
	}

  /**
   * getPendingState
   *
   * @return mixed|string
   */
  public function getPendingState()
  {
    return $this->getConfigData('pending_status') ? $this->getConfigData('pending_status') : Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
  }

  /**
   * getErrorState
   *
   * @return mixed|string
   */
	public function getErrorState()
	{
		return $this->getConfigData('error_status') ? $this->getConfigData('error_status') : Mage_Sales_Model_Order::STATE_HOLDED;
	}

  /**
   * getCancelState
   *
   * @return mixed|string
   */
  public function getCancelState()
  {
    return $this->getConfigData('cancel_status') ? $this->getConfigData('cancel_status') : Mage_Sales_Model_Order::STATE_CANCELED;
  }

  /**
   * getBaseConfig
   *
   * @return array
   */
  public function getBaseConfig()
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
      'payformid'   => $this->getSettingData('payformid'),
      'sepaformid'  => $this->getSettingData('sepaformid'),
      'ccardformid' => $this->getSettingData('ccardformid'),
    );
    return $config;
  }

  /**
   * getConfigData
   *
   * @param string $field
   * @param null   $storeId
   *
   * @return mixed
   */
	public function getConfigData($field, $storeId = null)
	{
    if (null === $storeId) {
      $storeId = $this->getStore();
    }
    $path = 'payment/'.$this->getCode().'/'.$field;
    $value = Mage::getStoreConfig($path, $storeId);
    return $value;
  }

  /**
   * getSettingData
   *
   * @param      $field
   * @param null $storeId
   *
   * @return mixed
   */
  public function getSettingData($field, $storeId = null)
	{
	  if (null === $storeId) {
      $storeId = $this->getStore();
    }
    $path = 'micropayment/settings/'.$field;
    $value = Mage::getStoreConfig($path, $storeId);
    return $value;
  }

  function plugin_url()
  {
    return Mage::getUrl('mcpay/payment', array('_secure' => true));
  }

  /**
   * getUserDataFromOrder
   *
   * @param $order
   * @param $config
   *
   * @return array
   */
  function getUserDataFromOrder($order, $config)
  {
    $this->McPay->setConfig($config);

    // set transaction ID for order process !! IMPORTANT !!
    $order->getPayment()->getMethodInstance()->setTransactionId($order->getRealOrderId());

    $custID  = $order->getCustomerId();
    $orderId  = $order->getPayment()->getMethodInstance()->getTransactionId();
    //mail('webmaster@web-dezign.de', __CLASS__.'->'.__FUNCTION__.'->$orderId', print_r($orderId,1));
    if (empty($custID)) { // guest order
      $custID = 'guest-'.$orderId;
    } else {
      $orderId .= '-'.$custID;
    }
    $custID = $this->McPay->unifyID($custID);

    // Base Shop Currency
    //$amount		= number_format($this->getOrder()->getBaseGrandTotal(), 2, '.', '');
    //$currency	= $this->getOrder()->getBaseCurrencyCode();
    // Order Currency
    $amount		= number_format($order->getGrandTotal(), 2, '.', '');
    $currency	= $order->getOrderCurrencyCode();

    $billing	= $order->getBillingAddress();
    $street		= $billing->getStreet();
    $userData = array(
      'company'   => $billing->getCompany(),
      'firstName' => $billing->getFirstname(),
      'lastName'  => $billing->getLastname(),
      'salutation'=> 'MR',
      'street'    => $street[0],
      'zip'       => $billing->getPostcode(),
      'city'      => $billing->getCity(),
      'country'   => $billing->getCountry(),
      'email'     => $order->getCustomerEmail(),
      'ip'        => $order->getRemoteIp(),
      'id'        => $custID,
      'order_id'  => $orderId,
      'lang'      => $config['lang'],
      'amount'    => ($amount * 100),
      'currency'  => $currency,
    );
    if (empty($userData['ip'])) $userData['ip'] = $_SERVER['REMOTE_ADDR']; // Falls IP Leer, dann aus dem Server holen

    return $userData;
  }

} // end of class
?>