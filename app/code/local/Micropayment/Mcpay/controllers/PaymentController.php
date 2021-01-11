<?php
if (!defined('MCPAY_BASE_PATH')) {
  $mcpayPath = Mage::getBaseDir('code').'/local/Micropayment/Mcpay/';
  define('MCPAY_BASE_PATH', $mcpayPath);
}
class Micropayment_Mcpay_PaymentController extends Mage_Core_Controller_Front_Action
{
  var $debug = true;
  /**
	 * Get order model
	 *
	 * @return Mage_Sales_Model_Order
	 */
	public function getOrder()
	{
    return Mage::getModel('sales/order');
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
	 * Get MPIDEAL session namespace
	 *
	 * @return Mage_MCP_Model_Session
	 */
	public function getSession()
  {
    return Mage::getSingleton('core/session');
    #return Mage::getSingleton('mpideal/session');
	}

  /**
   * Get singleton with MCPay order transaction information
   *
   * @return Micropayment_Mcpay_Model_Standard
   */
  public function getMCPayPayment()
  {
    return Mage::getSingleton('mcpay/method_standard');
  }
  
  protected function _getHelper()
  {
    return Mage::helper('mcpay');
  }
  
  /**
   * redirectAction
   *
   */
	public function redirectAction() 
  {
    try {
      $session = $this->getCheckout();

      $order = Mage::getModel('sales/order');
      $order->loadByIncrementId($session->getLastRealOrderId());

      if (!$order->getId()) {
        Mage::throwException('No order for processing found');
      }

      if ($order->getPayment() !== false){
      	$payment = $order->getPayment()->getMethodInstance();
      } else {
      	Mage::throwException('Cant load payment object');
      }

      if ($order->getState() != Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
        $order->setState(
          Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
          $payment->getPendingState(),
          $this->_getHelper()->__('Customer redirected to payment.')
        )->save();
      }

      if ($session->getQuoteId() && $session->getLastSuccessQuoteId()) {
        $session->setMCPQuoteId($session->getQuoteId());
        $session->setMCPLastSuccessQuoteId($session->getLastSuccessQuoteId());
        $session->setMCPLastRealOrderId($session->getLastRealOrderId());
        $session->getQuote()->setIsActive(true)->save();
        $session->clear();
      }

      Mage::dispatchEvent('mcpay_payment_controller_redirect_action');

      $locale     = explode('_', Mage::app()->getLocale()->getLocaleCode());
      if (is_array($locale) && ! empty($locale))
        $language = $locale[0];
      else
        $language = $this->getDefaultLocale();
      
      $res = $payment->getPaymentData($language);
      //mail('webmaster@web-dezign.de', __CLASS__.'->'.__FUNCTION__.'->$res', print_r($res,1));

      if (isset($res->res->error) && $res->res->error == 0){
        $crlf = "\r\n";
        $config = $payment->getBaseConfig();
        $post = array(
          'action'        => 'transactionCreate',
          'type'          => 'BOOKING',
          'sessionId'     => $res->res->sessionId,
          'transactionId' => $res->res->transactionId,
          'testMode'      => $config['testMode'],
        );

        // Base Shop Currency
        //$post['amount']		= number_format($order->getBaseGrandTotal(), 2, '.', '');
        //$post['currency']	= $order->getBaseCurrencyCode();
        // Order Currency
        $post['amount']		= number_format($order->getGrandTotal(), 2, '.', '');
        $post['currency']	= $order->getOrderCurrencyCode();

        $tmp = $this->responseCCard3DAction($post);
        $res = explode($crlf, $tmp);
        //mail('webmaster@web-dezign.de', __CLASS__.'->'.__FUNCTION__.'->$res', print_r($res,1).print_r($tmp,1));
        $status = explode('=', $res[0]);
        if ($status[1] == 'ok'){
          $url = substr($res[1], 4);
          //mail('webmaster@web-dezign.de', __CLASS__.'->'.__FUNCTION__.'->$url', print_r($url,1));
          $this->_redirectUrl($url);
          //$this->_redirect('checkout/onepage/success', array('_secure' => TRUE));
        } else {
          $error = explode('=', $res[1]);
          $this->getCheckout()->addError($error[1]);
        }
        return;
      }

      if (!empty($res->forward)){
        header('Location: '.$res->url);
        exit();
      }

    } catch (Exception $e){
      Mage::logException($e);
      $this->_redirect('micropayment/payment/error', array('error' => $e->getMessage()));
    }
	}

  /**
   * responseAction
   *
   * @throws \Mage_Core_Exception
   */
  public function responseAction()
  {
    $post = Mage::app()->getRequest()->getParams();
    //echo '<pre>POST: '.print_r($post, 1).'</pre>';

    $return = '';
    $crlf = "\r\n";

    // check mandatory params
    if (empty($post['function']))   $return = 'status=error'.$crlf.'message=Function missing.';
    else                            $func = $post['function'];

    if ($func != 'custinfo') {
      if (empty($post['orderid']))  $return = 'status=error' . $crlf . 'message=OrderID missing.';
      else                          $orderId = $post['orderid'];

      if (empty($post['auth']))     $return = 'status=error' . $crlf . 'message=Auth missing.';
      else                          $authId = $post['auth'];

      if (empty($post['amount']))   $return = 'status=error' . $crlf . 'message=Amount missing.';
      else                          $amount = $post['amount'];

      if (empty($post['currency'])) $return = 'status=error' . $crlf . 'message=Currency missing.';
      else                          $currency = $post['currency'];

      if (!isset($post['testmode']))$return = 'status=error' . $crlf . 'message=Testmode missing.';
      else                          $testmode = $post['testmode'];

      if (!empty($orderId)) {
        // extract Order ID
        if (strpos($orderId, '-') !== FALSE) {
          $parts = explode('-', $orderId);
          $orderId = $parts[0];
          $custId = $parts[1];
        }

        // Order Object
        $order = $this->getOrder();
        if (!empty($orderId)) {
          $order->loadByIncrementId($orderId);
          // get payment object from order
          if ($order->getPayment() !== FALSE) {
            $payment = $order->getPayment()->getMethodInstance();
          } else {
            $payment = $this->getMCPayPayment();
          }
          #echo '<pre>'.print_r($payment, 1).'</pre>';
        }
      } else {
        $return = 'status=error' . $crlf . 'message=OrderID missing!';
      }

      if (!empty($return)) {
        echo $return;
        return;
      }

      // check secretfield if given
      if (!empty($post['secretfield'])) {
        $secretfield = $payment->getSettingData('secretfield');
        if ($secretfield != $post['secretfield']) {
          $return = 'status=error' . $crlf . 'message=Communication is insecure.';
        }
      }
    } else {
      if (empty($post['ak']))   $return = 'status=error'.$crlf.'message=Missing KEY!';
      else                      $ak = $post['ak'];
    }

    // return error if some is right there
    if (!empty($return)) {
      echo $return;
      return;
    }
    // default error after pre-checks
    $return = 'status=error'.$crlf.'message=Unknown Error';

    require_once (MCPAY_BASE_PATH.'Classes/class.mcpay.php');
    $McPay = new mcpay();

    $this->sendNotifyEMail($orderId, $func);

    switch($func){

      // status call
      case 'custinfo':
        $accessKey = Mage::getConfig()->getNode('default/micropayment/settings/accesskey');
        //echo $accessKey.' == '.$ak;
        if ($accessKey == $ak) {
          if (!empty($post['log'])){
            echo $McPay->readLogFile($McPay->log->error_log, true);
            exit();
          } else {
            $modules    = Mage::getConfig()->getNode('modules')->children();
            $moduleName = $this->_getRealModuleName();
            $modules    = $modules->$moduleName;
            echo '<pre>'.print_r($modules, 1).'</pre>';
            $sysconfig = Mage::getConfig()->getNode('default/micropayment');
            echo '<pre>'.print_r($sysconfig, 1).'</pre>';
            foreach ($McPay->mcpayEventURLs AS $payName => $url) {
              $config = Mage::getConfig()->getNode('default/payment/'.$payName);
              echo '<pre>'.print_r($config, 1).'</pre>';
            }
          }
        } else {
          $return = 'status=error' . $crlf . 'message=Access denied!';
        }
        break;

      // billing call
      case 'billing':
        // Response Amount Check
        if ($order->getGrandTotal() != ($amount / 100)) {
          $return = 'status=error' . $crlf . 'message=Amount mismatch.' . $crlf;
          if ($this->debug) $McPay->log->debug(__CLASS__.'->'.__FUNCTION__ . '->error: Order(' . $orderId . ') Amount mismatch ' . $order->get_total() . ' != ' . ($_GET['amount'] / 100));
        // Response Currency Check
        } else if ($order->getOrderCurrencyCode() != $currency) {
        //} else if ($order->getStoreCurrencyCode() != $currency) {
          $return = 'status=error' . $crlf . 'message=Currency mismatch.' . $crlf;
          if ($this->debug) $McPay->log->debug(__CLASS__ . '->' . __FUNCTION__ . '->error: Order(' . $orderId . ') Currency mismatch ' . $order->getOrderCurrencyCode() . ' != ' . $_GET['currency']);
        } else {
          // Response Testmode Comment
          if (!empty($testmode)) {
            $order->addStatusHistoryComment('!! TEST MODE !!', $payment->getPaymentState());
          }

          // check if success
          if ($order->getState() == $payment->getPaymentState()) {
            $url = Mage::getUrl('micropayment/payment/success/', array('_secure' => TRUE)) . '?oId=' . $orderId;
            $return = 'status=ok' . $crlf // ok  | error
              . 'url=' . $url . $crlf
              . 'target=_top' . $crlf
              . 'forward=1' . $crlf
              . 'message=OK' . $crlf;
            echo $return;
            return;
          }

          // fill order
          $invoiceMailComment = 'Auth ID: ' . $authId;
          $INVOICEMODE = $payment->getConfigData('invoicemode');
          if ($INVOICEMODE == 'AUTOINVOICE') {
            try {
              if ($order->canInvoice()) {
                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                if (!$invoice->getTotalQty()) {
                  Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
                }
                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
                $invoice->register();
                Mage::getModel('core/resource_transaction')
                  ->addObject($invoice)
                  ->addObject($invoice->getOrder())
                  ->save();

                $invoice->getOrder()->setIsInProcess(TRUE);
                $invoice->getOrder()->addStatusHistoryComment('Invoice created automatically', TRUE);
                $invoice->sendEmail(TRUE, $invoiceMailComment);
                $invoice->setTransactionId($authId);
                $invoice->save();
              }
            } catch (Mage_Core_Exception $e) {
              Mage::logException($e);
              $this->getCheckout()->addError($this->_getHelper()->__($e->getMessage()));
            } catch (Exception $e) {
              Mage::logException($e);
            }
          }

          //Mage::log("MCPay - responseAction: order->setState " .$payment->getPaymentState());
          $order->setState($payment->getPaymentState());
          $order->addStatusHistoryComment($invoiceMailComment, $payment->getPaymentState());
          // save last Transaction ID
          $order->getPayment()->setLastTransId($authId);
          $order->setCustomerNote($invoiceMailComment); // Kommentar auch in EMail
          $order->save();

          $url = Mage::getUrl('micropayment/payment/success/', array('_secure' => TRUE)) . '?oId=' . $orderId;
          $return = 'status=ok' . $crlf // ok  | error
            . 'url=' . $url . $crlf
            . 'target=_top' . $crlf
            . 'forward=1' . $crlf
            . 'message=OK' . $crlf;
        }

        break;

      case 'storno': // chargeback
        $order->setState($payment->getCancelState());
        if (!empty($post['paypal_reversalreason'])){ // paypal special
          $order->addStatusHistoryComment('Payment Cancelled. (STORNO) '.$post['paypal_reversalreason'], $payment->getCancelState());
        } else {
          $order->addStatusHistoryComment('Payment Cancelled. (STORNO)', $payment->getCancelState());
        }
        $order->save();
        $return = 'status=ok' . $crlf . 'message=STORNO done.' . $crlf;
        break;
      case 'backpay': // chargeback reversal
        $order->addStatusHistoryComment('Payment Cancellation refunded (BACKPAY)', $payment->getPaymentState());
        $order->save();
        $return = 'status=ok' . $crlf . 'message=BACKPAY done.' . $crlf;
        break;
      case 'refund': // refund
        try{
          $REFUNDMODE = $payment->getConfigData('refundmode');
          if ($REFUNDMODE == 'CREDITMEMO') {
            $service = Mage::getModel('sales/service_order', $order);
            $invoices = array();
            foreach ($order->getInvoiceCollection() as $invoice) {
              if ($invoice->canRefund()) {
                $invoices[] = $invoice;
              }
            }
            foreach ($invoices as $invoice) {
              $creditmemo = $service->prepareInvoiceCreditmemo($invoice);
              $creditmemo->setRefundRequested(TRUE)
                ->setOfflineRequested(FALSE)// request to refund online
                ->register();

              Mage::getModel('core/resource_transaction')
                ->addObject($creditmemo)
                ->addObject($creditmemo->getOrder())
                ->addObject($creditmemo->getInvoice())
                ->save();
            }
          } else {
            $payment->refundCall($order, $amount, $currency);
          }
          $return = 'status=ok' . $crlf . 'message=Refund done.' . $crlf;
        } catch (Mage_Core_Exception $e) {
          $return = 'status=error' . $crlf . 'message='.$e->getMessage() . $crlf;
          Mage::logException($e);
        } catch (Exception $e) {
          Mage::logException($e);
        }
        break;
      case 'quit': // reversal SEPA only
        $order->addStatusHistoryComment('Payment Cancelled. (QUIT)', $payment->getCancelState());
        //$order->update_status('cancelled', __('Payment Cancelled. (QUIT)', 'mipa'));
        $order->save();
        $return = 'status=ok' . $crlf . 'message=QUIT done.' . $crlf;
        break;
    }
    echo $return;
    return;
  }

  /**
   * responseSepaAction
   *
   * @throws \Mage_Core_Exception
   */
  public function responseSepaAction()
  {
    $post = Mage::app()->getRequest()->getParams();
    //echo '<pre>POST: '.print_r($post, 1).'</pre>';

    $crlf = "\r\n";
    $return = '';

    // check mandatory params
    if (empty($post['action']))         $return = 'status=error'.$crlf.'message=action missing.';
    else                                $act = $post['action'];

    if ($act != 'sessionStatus') {
      if (empty($post['type']))         $return = 'status=error' . $crlf . 'message=type missing.';
      else                              $type = $post['type'];

      if (empty($post['sessionId']))    $return = 'status=error' . $crlf . 'message=sessionId missing.';
      else                              $sessID = $post['sessionId'];

      if (empty($post['transactionId']))$return = 'status=error' . $crlf . 'message=transactionId missing.';
      else                              $trxID = $post['transactionId'];

      if (empty($post['amount']))       $return = 'status=error' . $crlf . 'message=Amount missing.';
      else                              $amount = $post['amount'];

      if (!isset($post['testMode']))    $return = 'status=error' . $crlf . 'message=Testmode missing.';
      else                              $testmode = $post['testMode'];

      if (empty($post['description']))  $return = 'status=error' . $crlf . 'message=description missing.';
      else                              $desc = $post['description'];
    }

    // return error if some is right there
    if (!empty($return)) {
      echo $return;
      return;
    }
    // default error after pre-checks
    $return = 'status=error'.$crlf.'message=Unknown Error';

    require_once (MCPAY_BASE_PATH.'Classes/class.mcpay.php');
    $McPay = new mcpay();

    if ($act == 'sessionStatus'){
      if ($this->debug) $McPay->log->debug(__CLASS__.'->'.__FUNCTION__ . '->GET: ' . print_r($_GET, 1).' POST: '.print_r($_POST, 1));
      $return = 'status=ok' . $crlf;
      echo $return;
      exit();
    }
    if ($act == 'transactionCreate'){
      if ($this->debug) $McPay->log->debug(__CLASS__.'->'.__FUNCTION__ . '->GET: ' . print_r($_GET, 1).' POST: '.print_r($_POST, 1));

      $p = explode('_', $sessID);
      $orderId = $p[1];
      if (!empty($orderId)) {
        // extract Order ID and Cust ID
        if (strpos($orderId, '-') !== FALSE) {
          $parts = explode('-', $orderId);
          $orderId = $parts[0];
          $custId = $parts[1];
        }

        // Order Object
        $order = $this->getOrder();
        if (!empty($orderId)) {
          $order->loadByIncrementId($orderId);
          // get payment object from order
          if ($order->getPayment() !== FALSE) {
            $payment = $order->getPayment()->getMethodInstance();
          } else {
            $payment = $this->getMCPayPayment();
          }
          #echo '<pre>'.print_r($payment, 1).'</pre>';
        }
      } else {
        $return = 'status=error' . $crlf . 'message=OrderID missing!';
      }
      $currency = $order->getOrderCurrencyCode();

      $this->sendNotifyEMail($orderId, $type);

      switch ($type){
        // SEPA direct debit callbacks
        case 'BOOKING': // normal trx
          // Response Amount Check
          if ($order->getGrandTotal() != ($amount / 100)) {
            $return = 'status=error' . $crlf . 'message=Amount mismatch.' . $crlf;
            if ($this->debug) $McPay->log->debug(__CLASS__.'->'.__FUNCTION__ . '->error: Order(' . $orderId . ') Amount mismatch ' . $order->getGrandTotal() . ' != ' . ($amount / 100));
            // Response Currency Check
          } else if ($order->getOrderCurrencyCode() != $currency) {
          //} else if ($order->getStoreCurrencyCode() != $currency) {
            $return = 'status=error' . $crlf . 'message=Currency mismatch.' . $crlf;
            if ($this->debug) $McPay->log->debug(__CLASS__ . '->' . __FUNCTION__ . '->error: Order(' . $orderId . ') Currency mismatch ' . $order->getOrderCurrencyCode() . ' != ' . $currency);
          } else {
            // Response Testmode Comment
            if (!empty($testmode)) {
              $order->addStatusHistoryComment('!! TEST MODE !!', $payment->getPaymentState());
            }

            // check if success
            if ($order->getState() == $payment->getPaymentState()) {
              $url = Mage::getUrl('micropayment/payment/success/', array('_secure' => TRUE)) . '?oId=' . $orderId;
              $return = 'status=ok' . $crlf // ok  | error
                . 'url=' . $url . $crlf
                . 'target=_top' . $crlf
                . 'forward=1' . $crlf
                . 'message=OK' . $crlf;
              echo $return;
              return;
            }

            $locale     = explode('_', Mage::app()->getLocale()->getLocaleCode());
            if (is_array($locale) && ! empty($locale))
              $language = $locale[0];
            else
              $language = $this->getDefaultLocale();

            // fill order
            $trxResult = $this->getSession()->getMcpayTrxResult();
            $mandat = $trxResult->mandateTextEN;
            if (strtoupper($language) == 'DE'){
              $mandat = $trxResult->mandateTextDE;
            }
            $invoiceMailComment = $mandat;
            $INVOICEMODE = $payment->getConfigData('invoicemode');
            if ($INVOICEMODE == 'AUTOINVOICE') {
              try {
                if ($order->canInvoice()) {
                  $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                  if (!$invoice->getTotalQty()) {
                    Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
                  }
                  $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
                  $invoice->register();
                  Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder())
                    ->save();

                  $invoice->getOrder()->setIsInProcess(TRUE);
                  $invoice->getOrder()->addStatusHistoryComment('Invoice created automatically', TRUE);
                  $invoice->sendEmail(TRUE, $invoiceMailComment);
                  $invoice->setTransactionId($trxID);
                  $invoice->save();
                }
              } catch (Mage_Core_Exception $e) {
                Mage::logException($e);
                $this->getCheckout()->addError($this->_getHelper()->__($e->getMessage()));
              } catch (Exception $e) {
                Mage::logException($e);
              }
            }

            $order->setState($payment->getPaymentState());
            $order->addStatusHistoryComment($invoiceMailComment, $payment->getPaymentState());
            // save last Transaction ID
            $order->getPayment()->setLastTransId($trxID);
            $order->addStatusHistoryComment('TRX ID: ' . $trxID, $payment->getPaymentState());
            $order->setCustomerNote($invoiceMailComment); // Kommentar auch in EMail
            $order->save();

            $url = Mage::getUrl('micropayment/payment/success/', array('_secure' => TRUE)) . '?oId=' . $orderId;
            $return = 'status=ok' . $crlf // ok  | error
              . 'url=' . $url . $crlf
              . 'target=_top' . $crlf
              . 'forward=1' . $crlf
              . 'message=OK' . $crlf;
          }
          break;

        case 'REFUND':
        case 'STOP':
          // detect refund source in case it is from control center it should be booked otherwise not
          if (!empty($desc) && substr($desc, 0, 2) == '##'){
            // refund from inside shop so do nothing
          } else {
            try{
              $REFUNDMODE = $payment->getConfigData('refundmode');
              if ($REFUNDMODE == 'CREDITMEMO') {
                $service = Mage::getModel('sales/service_order', $order);
                $invoices = array();
                foreach ($order->getInvoiceCollection() as $invoice) {
                  if ($invoice->canRefund()) {
                    $invoices[] = $invoice;
                  }
                }
                foreach ($invoices as $invoice) {
                  $creditmemo = $service->prepareInvoiceCreditmemo($invoice);
                  $creditmemo->setRefundRequested(TRUE)
                    ->setOfflineRequested(FALSE)// request to refund online
                    ->register();

                  Mage::getModel('core/resource_transaction')
                    ->addObject($creditmemo)
                    ->addObject($creditmemo->getOrder())
                    ->addObject($creditmemo->getInvoice())
                    ->save();
                }
              } else {
                $payment->refundCall($order, $amount, $currency);
              }
              $return = 'status=ok' . $crlf . 'message=Refund done.' . $crlf;
            } catch (Mage_Core_Exception $e) {
              $return = 'status=error' . $crlf . 'message='.$e->getMessage() . $crlf;
              Mage::logException($e);
            } catch (Exception $e) {
              Mage::logException($e);
            }
          }
          break;

        case 'REVERSAL': // chargeback
          $order->setState($payment->getCancelState());
          if (!empty($post['paypal_reversalreason'])){ // paypal special
            $order->addStatusHistoryComment('Payment Cancelled. (STORNO) '.$post['paypal_reversalreason'], $payment->getCancelState());
          } else {
            $order->addStatusHistoryComment('Payment Cancelled. (STORNO)', $payment->getCancelState());
          }
          $order->save();
          $return = 'status=ok' . $crlf . 'message=REVERSAL done.' . $crlf;
          break;

        case 'REFUNDREVERSAL':
          $order->addStatusHistoryComment('Payment Refund Reversal (REFUNDREVERSAL)', $payment->getPaymentState());
          $order->save();
          $return = 'status=ok' . $crlf . 'message=REFUNDREVERSAL done.' . $crlf;
          break;

        case 'BACKPAY': // backpay the chargeback
          $order->addStatusHistoryComment('Payment Cancellation refunded (BACKPAY)', $payment->getPaymentState());
          $order->save();
          $return = 'status=ok' . $crlf . 'message=BACKPAY done.' . $crlf;
          break;
      }

      if ($this->debug) $McPay->log->debug(__CLASS__.'->'.__FUNCTION__ . '->return: ' . print_r($return, 1));
    }
    echo $return;
    exit();
  }

  /**
   * responsePrepayAction
   *
   * @throws \Mage_Core_Exception
   */
  public function responsePrepayAction()
  {
    $post = Mage::app()->getRequest()->getParams();
    //echo '<pre>POST: '.print_r($post, 1).'</pre>';

    $crlf = "\r\n";
    $status = $return = '';

    // check mandatory params
    if (empty($post['action']))         $return = 'status=error'.$crlf.'message=action missing.';
    else                                $act = $post['action'];

    if (empty($post['sessionId']))      $return = 'status=error' . $crlf . 'message=sessionId missing.';
    else                                $sessID = $post['sessionId'];

    if ($act == 'sessionStatus') {
      if (empty($post['status']))       $return = 'status=error' . $crlf . 'message=status missing.';
      else                              $status = $post['status'];
    }

    if ($act != 'sessionStatus') {
      if (empty($post['type']))         $return = 'status=error' . $crlf . 'message=type missing.';
      else                              $type = $post['type'];

      if (empty($post['transactionId']))$return = 'status=error' . $crlf . 'message=transactionId missing.';
      else                              $trxID = $post['transactionId'];

      if ($act != 'sessionEmail') {
        if (!isset($post['orderAmount']))$return = 'status=error' . $crlf . 'message=orderAmount missing.';
        else                            $orderAmount = $post['orderAmount'];

        if (!isset($post['paidAmount']))$return = 'status=error' . $crlf . 'message=paidAmount missing.';
        else                            $paidAmount = $post['paidAmount'];

        if (!isset($post['openAmount']))$return = 'status=error' . $crlf . 'message=openAmount missing.';
        else                            $openAmount = $post['openAmount'];
      }

      if (!isset($post['testMode']))    $return = 'status=error' . $crlf . 'message=Testmode missing.';
      else                              $testmode = $post['testMode'];

    }

    // return error if some is right there
    if (!empty($return)) {
      echo $return;
      return;
    }
    // default error after pre-checks
    $return = 'status=error'.$crlf.'message=Unknown Error';

    require_once (MCPAY_BASE_PATH.'Classes/class.mcpay.php');
    $McPay = new mcpay();

    if ($act == 'sessionStatus'){
      if ($this->debug) $McPay->log->debug(__CLASS__.'->'.__FUNCTION__ . '->GET: ' . print_r($_GET, 1).' POST: '.print_r($_POST, 1));
      if ($status == 'CLOSED') { // payment closed
        if (!empty($post['freeParams']['orderid'])) {
          $orderId = $post['freeParams']['orderid'];
          // Order Object
          $order = $this->getOrder();
          $order->loadByIncrementId($orderId);
          // get payment object from order
          if ($order->getPayment() !== FALSE) {
            $payment = $order->getPayment()->getMethodInstance();
          } else {
            $payment = $this->getMCPayPayment();
          }
        } else {
          $return = 'status=error' . $crlf . 'message=OrderID missing!';
        }

        $order->setState($payment->getCancelState());
        $order->addStatusHistoryComment('Order closed.', $payment->getCancelState());
        $order->save();
        $return = 'status=ok'.$crlf; // ok  | error

        $this->sendNotifyEMail($orderId, 'CLOSED');
      } else {
        $return = 'status=ok'.$crlf;
      }
      echo $return;
      exit();
    }
    if ($act == 'sessionEmail'){
      $p = explode('_', $sessID);
      $orderId = $p[1];
      if (!empty($orderId)) {
        // extract Order ID and Cust ID
        if (strpos($orderId, '-') !== FALSE) {
          $parts = explode('-', $orderId);
          $orderId = $parts[0];
          $custId = $parts[1];
        }

        // Order Object
        $order = $this->getOrder();
        if (!empty($orderId)) {
          $order->loadByIncrementId($orderId);
          // get payment object from order
          if ($order->getPayment() !== FALSE) {
            $payment = $order->getPayment()->getMethodInstance();
          } else {
            $payment = $this->getMCPayPayment();
          }
          #echo '<pre>'.print_r($payment, 1).'</pre>';

          // activate lines which emails you want to log
          $allowedTypes = array(
            //'CREATE', // Zahlung erzeugt
            //'CHANGE', // Zahlung geändert
            //'PAYIN', // Zahlung eingegangen
            'REMIND', // Erinnerung
            'LASTREMIND', // Letzte Erinnerung
            'EXPIRE', // Abgelaufen
          ); // only allowed values need to be logged
          //echo $orderId.'-'.$type;
          if ($orderId > 0 && in_array($type, $allowedTypes)) {
            $order->addStatusHistoryComment('EMAIL SENT: ' . $type . ' ' . date('d.m.Y H:i:s'), $order->getState());
            $order->save();
          }
          $return = 'status=ok' . $crlf;
          echo $return;
          exit();
        }
      } else {
        $return = 'status=error' . $crlf . 'message=OrderID missing!';
      }

    }
    if ($act == 'transactionCreate'){
      if ($this->debug) $McPay->log->debug(__CLASS__.'->'.__FUNCTION__ . '->GET: ' . print_r($_GET, 1).' POST: '.print_r($_POST, 1));

      $p = explode('_', $sessID);
      $orderId = $p[1];
      if (empty($orderId)) $orderId = $p[0]; // if no customer id provided

      if (!empty($orderId)) {
        // extract Order ID and Cust ID
        if (strpos($orderId, '-') !== FALSE) {
          $parts = explode('-', $orderId);
          $orderId = $parts[0];
          $custId = $parts[1];
        }

        // Order Object
        $order = $this->getOrder();
        if (!empty($orderId)) {
          $order->loadByIncrementId($orderId);
          // get payment object from order
          if ($order->getPayment() !== FALSE) {
            $payment = $order->getPayment()->getMethodInstance();
          } else {
            $payment = $this->getMCPayPayment();
          }
          #echo '<pre>'.print_r($payment, 1).'</pre>';
        }
      } else {
        $return = 'status=error' . $crlf . 'message=OrderID missing!';
      }
      $currency = $order->getOrderCurrencyCode();

      $this->sendNotifyEMail($orderId, $type);

      switch ($type){

        case 'CREATE': // normal trx
          // Response Amount Check
          if ($order->getGrandTotal() != ($orderAmount / 100)) {
            $return = 'status=error' . $crlf . 'message=Amount mismatch.' . $crlf;
            if ($this->debug) $McPay->log->debug(__CLASS__.'->'.__FUNCTION__ . '->error: Order(' . $orderId . ') Amount mismatch ' . $order->getGrandTotal() . ' != ' . ($orderAmount / 100));
            // Response Currency Check
          } else if ($order->getOrderCurrencyCode() != $currency) {
          //} else if ($order->getStoreCurrencyCode() != $currency) {
            $return = 'status=error' . $crlf . 'message=Currency mismatch.' . $crlf;
            if ($this->debug) $McPay->log->debug(__CLASS__ . '->' . __FUNCTION__ . '->error: Order(' . $orderId . ') Currency mismatch ' . $order->getOrderCurrencyCode() . ' != ' . $currency);
          } else {
            // Response Testmode Comment
            if (!empty($testmode)) {
              $order->addStatusHistoryComment('!! TEST MODE !!');
            }

            // check if success
            /*
            if ($order->getState() == $payment->getPendingState()) {
              $url = Mage::getUrl('micropayment/payment/success/', array('_secure' => TRUE)) . '?oId=' . $orderId;
              $return = 'status=ok' . $crlf // ok  | error
                . 'url=' . $url . $crlf
                . 'target=_top' . $crlf
                . 'forward=1' . $crlf
                . 'message=OK' . $crlf;
              echo $return;
              return;
            }
            */

            $locale     = explode('_', Mage::app()->getLocale()->getLocaleCode());
            if (is_array($locale) && ! empty($locale))
              $language = $locale[0];
            else
              $language = $this->getDefaultLocale();

            $confField = 'emailtexten';
            if (strtoupper($language) == 'DE'){
              $confField = 'emailtextde';
            }
            $invoiceMailComment = $payment->getConfigData($confField);
            //$order->setState($payment->getPendingState());
            //$order->addStatusHistoryComment($invoiceMailComment, $payment->getPendingState());
            // save last Transaction ID
            $order->getPayment()->setLastTransId($trxID);
            $order->addStatusHistoryComment('TRX ID: ' . $trxID);
            $order->setCustomerNote($invoiceMailComment); // Kommentar auch in EMail
            $order->save();

            $url = Mage::getUrl('micropayment/payment/success/', array('_secure' => TRUE)) . '?oId=' . $orderId;
            $return = 'status=ok' . $crlf // ok  | error
              . 'url=' . $url . $crlf
              . 'target=_top' . $crlf
              . 'forward=1' . $crlf
              . 'message=OK' . $crlf;
          }
          break;

        case 'PAYIN':
          // fill order
          $invoiceMailComment = 'PAYIN TRX ID: ' . $trxID;

          $paidAmount = $paidAmount / 100; // in cent

          // check if paid complete
          $completePaid = FALSE;
          try {
            $config = $payment->getBaseConfig();
            $McPay->setConfig($config);
            $McPay->switchPaymethod('prepay');
            $sessionInfo = $McPay->sessionGet($sessID);
            if ($this->debug) $McPay->log->debug(__CLASS__.'->'.__FUNCTION__ . '->sessionInfo: ' . print_r($sessionInfo, 1));
            if ($sessionInfo->openAmount <= 0){
              $completePaid = TRUE;
            } else {
              $invoiceMailComment.= ' OPEN_AMOUNT: '.sprintf('%1.2f', ($sessionInfo->openAmount/100)).' '.$currency;
            }
          } catch (Exception $e) {
            if ($this->debug) $McPay->log->debug(__CLASS__.'->'.__FUNCTION__ . '->sessionInfo->error: ' . $e->getMessage());
            Mage::logException($e);
          }

          if (!empty($invoiceMailComment)){
            $order->addStatusHistoryComment($invoiceMailComment);
            $order->save();
          }

          if ($sessionInfo->paidAmount > 0) {
            //$order->setTotalPaid($order->getTotalPaid() + $paidAmount);
            //$order->setBaseTotalPaid($order->getBaseTotalPaid() + $paidAmount);
            $order->setTotalPaid(($sessionInfo->paidAmount/100));
            $order->setBaseTotalPaid(($sessionInfo->paidAmount/100));
            $order->addStatusHistoryComment('PayIn: '.$paidAmount.' '.$currency);
            $order->save();
            $return = 'status=ok' . $crlf; // ok  | error
          }

          if ($completePaid && $order->getTotalDue() <= 0) { // paid now ?
            $INVOICEMODE = $payment->getConfigData('invoicemode');
            if ($INVOICEMODE == 'AUTOINVOICE') {
              try {
                if ($order->canInvoice()) {
                  $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                  if (!$invoice->getTotalQty()) {
                    Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
                  }
                  $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
                  $invoice->register();
                  Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder())
                    ->save();

                  $invoice->getOrder()->setIsInProcess(TRUE);
                  $invoice->getOrder()->addStatusHistoryComment('Invoice created automatically', TRUE);
                  $invoice->sendEmail(TRUE, $invoiceMailComment);
                  $invoice->setTransactionId($trxID);
                  $invoice->save();
                }
              } catch (Mage_Core_Exception $e) {
                Mage::logException($e);
                $this->getCheckout()->addError($this->_getHelper()->__($e->getMessage()));
              } catch (Exception $e) {
                Mage::logException($e);
              }

              // correcting paid value in case invoice adds sum to previous payments
              if ($order->getTotalPaid() > ($sessionInfo->paidAmount/100)){
                $order->setTotalPaid(($sessionInfo->paidAmount/100));
                $order->setBaseTotalPaid(($sessionInfo->paidAmount/100));
                $order->save();
              }

            }

            //Mage::log("MCPay - responseAction: order->setState " .$payment->getPaymentState());
            if ($order->getStatus() != $payment->getCancelState()) { // check if cancelled before
              $order->setState($payment->getPaymentState());
            }
            $order->addStatusHistoryComment($invoiceMailComment, $payment->getPaymentState());
            // save last Transaction ID
            $order->getPayment()->setLastTransId($trxID);
            $order->setCustomerNote($invoiceMailComment); // Kommentar auch in EMail
            $order->save();
          }
          break;

        case 'REFUND': // only done if OVERPAID
          $refundAmount = (abs($paidAmount)/100);
          $order->addStatusHistoryComment('REFUND OVERPAID: '.sprintf('%1.2f', $refundAmount).' '.$currency);
          $order->setTotalRefunded($refundAmount);
          $order->setBaseTotalRefunded($refundAmount);
          $order->setTotalPaid($order->getTotalPaid() - $refundAmount);
          $order->setBaseTotalPaid($order->getBaseTotalPaid() - $refundAmount);
          $order->save();
          $return = 'status=ok' . $crlf; // ok  | error
          break;

        case 'EXPIRE': // payment expired
          $order->setState($payment->getCancelState());
          $order->addStatusHistoryComment('Order Expired.', $payment->getCancelState());
          $order->save();
          $return = 'status=ok' . $crlf; // ok  | error
          break;

        case 'CHANGE':
          // check if paid complete
          $completePaid = FALSE;
          $invoiceMailComment = 'PAYIN TRX ID: ' . $trxID;
          try {
            $config = $payment->getBaseConfig();
            $McPay->setConfig($config);
            $McPay->switchPaymethod('prepay');
            $sessionInfo = $McPay->sessionGet($sessID);
            if ($this->debug) $McPay->log->debug(__CLASS__.'->'.__FUNCTION__ . '->sessionInfo: ' . print_r($sessionInfo, 1));
            $comment = 'Order Amount changed to: '.sprintf('%1.2f', ($sessionInfo->orderAmount/100)).' '.$currency;
            if ($sessionInfo->openAmount <= 0){
              $completePaid = TRUE;
            } else {
              $comment.= ' OPEN_AMOUNT:'.sprintf('%1.2f', ($sessionInfo->openAmount/100)).' '.$currency;
              $changeAmount = abs($orderAmount/100);
              $order->setTotalRefunded($changeAmount);
              $order->setBaseTotalRefunded($changeAmount);
              $order->setTotalPaid($order->getTotalPaid() + $changeAmount);
              $order->setBaseTotalPaid($order->getBaseTotalPaid() + $changeAmount);
            }
          } catch (Exception $e) {
            if ($this->debug) $McPay->log->debug(__CLASS__.'->'.__FUNCTION__ . '->sessionInfo->error: ' . $e->getMessage());
            Mage::logException($e);
          }

          if (!empty($comment)){
            $order->addStatusHistoryComment($comment);
            $order->save();
          }

          if ($completePaid){
            $changeAmount = abs($orderAmount/100);
            $order->setTotalRefunded($changeAmount);
            $order->setBaseTotalRefunded($changeAmount);
            $order->setTotalPaid($order->getTotalPaid() + $changeAmount);
            $order->setBaseTotalPaid($order->getBaseTotalPaid() + $changeAmount);
            $order->save();

            $INVOICEMODE = $payment->getConfigData('invoicemode');
            if ($INVOICEMODE == 'AUTOINVOICE') {
              try {
                if ($order->canInvoice()) {
                  $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                  if (!$invoice->getTotalQty()) {
                    Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
                  }
                  $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
                  $invoice->register();
                  Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder())
                    ->save();

                  $invoice->getOrder()->setIsInProcess(TRUE);
                  $invoice->getOrder()->addStatusHistoryComment('Invoice created automatically', TRUE);
                  $invoice->sendEmail(TRUE, $invoiceMailComment);
                  $invoice->setTransactionId($trxID);
                  $invoice->save();
                }
              } catch (Mage_Core_Exception $e) {
                Mage::logException($e);
                $this->getCheckout()->addError($this->_getHelper()->__($e->getMessage()));
              } catch (Exception $e) {
                Mage::logException($e);
              }

              // correcting paid value in case invoice adds sum to previous payments
              if ($order->getTotalPaid() > ($sessionInfo->paidAmount/100)){
                $order->setTotalPaid(($sessionInfo->paidAmount/100));
                $order->setBaseTotalPaid(($sessionInfo->paidAmount/100));
                $order->save();
              }

            }

            $order->setState($payment->getPaymentState());
            // save last Transaction ID
            $order->getPayment()->setLastTransId($trxID);
            $order->setCustomerNote($invoiceMailComment); // Kommentar auch in EMail
            $order->save();
          }
          $return = 'status=ok' . $crlf; // ok  | error
          break;

      }

      if ($this->debug) $McPay->log->debug(__CLASS__.'->'.__FUNCTION__ . '->return: ' . print_r($return, 1));
    }
    echo $return;
    exit();
  }

  /**
   * responseCCard3DAction
   *
   * @throws \Mage_Core_Exception
   */
  public function responseCCard3DAction($post = NULL)
  {
    $crlf = "\r\n";
    $return = '';

    // check mandatory params
    if (empty($post['action']))       $return = 'status=error'.$crlf.'message=action missing.';
    else                              $act = $post['action'];

    if (empty($post['type']))         $return = 'status=error' . $crlf . 'message=type missing.';
    else                              $type = $post['type'];

    if (empty($post['sessionId']))    $return = 'status=error' . $crlf . 'message=sessionId missing.';
    else                              $sessID = $post['sessionId'];

    if (empty($post['transactionId']))$return = 'status=error' . $crlf . 'message=transactionId missing.';
    else                              $trxID = $post['transactionId'];

    if (!isset($post['testMode']))    $return = 'status=error' . $crlf . 'message=Testmode missing.';
    else                              $testmode = $post['testMode'];

    if (empty($post['amount']))       ;//$return = 'status=error' . $crlf . 'message=Amount missing.';
    else                              $amount = $post['amount'];

    $currency = 'EUR'; // default
    if (empty($post['currency']))     ;//$return = 'status=error' . $crlf . 'message=Currency missing.';
    else                              $currency = $post['currency'];

    // return error if some is right there
    if (!empty($return)) {
      if (empty($post)){
        echo $return;
      }
      return $return;
    }
    // default error after pre-checks
    $return = 'status=error'.$crlf.'message=Unknown Error';

    require_once (MCPAY_BASE_PATH.'Classes/class.mcpay.php');
    $McPay = new mcpay();

    if ($act == 'transactionCreate'){
      if ($this->debug) $McPay->log->debug(__CLASS__.'->'.__FUNCTION__ . '->GET: ' . print_r($_GET, 1).' POST: '.print_r($_POST, 1).' $post: '.print_r($post, 1));

      $p = explode('_', $sessID);
      $orderId = $p[1];
      if (!empty($orderId)) {
        // extract Order ID and Cust ID
        if (strpos($orderId, '-') !== FALSE) {
          $parts = explode('-', $orderId);
          $orderId = $parts[0];
          $custId = $parts[1];
        }

        // Order Object
        $order = $this->getOrder();
        if (!empty($orderId)) {
          $order->loadByIncrementId($orderId);
          // get payment object from order
          if ($order->getPayment() !== FALSE) {
            $payment = $order->getPayment()->getMethodInstance();
          } else {
            $payment = $this->getMCPayPayment();
          }
          #echo '<pre>'.print_r($payment, 1).'</pre>';
        }

        $currency = $order->getOrderCurrencyCode();
      } else {
        $return = 'status=error' . $crlf . 'message=OrderID missing!';
      }

      $this->sendNotifyEMail($orderId, $type);

      switch ($type){
        // CCard callbacks
        case 'BOOKING': // normal trx
          // Response Testmode Comment
          if (!empty($testmode)) {
            $order->addStatusHistoryComment('!! TEST MODE !!', $payment->getPaymentState());
          }

          // check if success
          if ($order->getState() == $payment->getPaymentState()) {
            $url = Mage::getUrl('micropayment/payment/success/', array('_secure' => TRUE)) . '?oId=' . $orderId;
            $return = 'status=ok' . $crlf // ok  | error
              . 'url=' . $url . $crlf
              . 'target=_top' . $crlf
              . 'forward=1' . $crlf
              . 'message=OK' . $crlf;
            return $return;
          }

          $locale     = explode('_', Mage::app()->getLocale()->getLocaleCode());
          if (is_array($locale) && ! empty($locale))
            $language = $locale[0];
          else
            $language = $this->getDefaultLocale();

          // fill order
          $trxResult = $this->getSession()->getMcpayTrxResult();
          $mandat = $trxResult->mandateTextEN;
          if (strtoupper($language) == 'DE'){
            $mandat = $trxResult->mandateTextDE;
          }
          $invoiceMailComment = $mandat;
          $INVOICEMODE = $payment->getConfigData('invoicemode');
          if ($INVOICEMODE == 'AUTOINVOICE') {
            try {
              if ($order->canInvoice()) {
                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                if (!$invoice->getTotalQty()) {
                  Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
                }
                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
                $invoice->register();
                Mage::getModel('core/resource_transaction')
                  ->addObject($invoice)
                  ->addObject($invoice->getOrder())
                  ->save();

                $invoice->getOrder()->setIsInProcess(TRUE);
                $invoice->getOrder()->addStatusHistoryComment('Invoice created automatically', TRUE);
                $invoice->sendEmail(TRUE, $invoiceMailComment);
                $invoice->setTransactionId($trxID);
                $invoice->save();
              }
            } catch (Mage_Core_Exception $e) {
              Mage::logException($e);
              $this->getCheckout()->addError($this->_getHelper()->__($e->getMessage()));
            } catch (Exception $e) {
              Mage::logException($e);
            }
          }

          $order->setState($payment->getPaymentState());
          $order->addStatusHistoryComment($invoiceMailComment, $payment->getPaymentState());
          // save last Transaction ID
          $order->getPayment()->setLastTransId($trxID);
          $order->addStatusHistoryComment('TRX ID: ' . $trxID, $payment->getPaymentState());
          $order->setCustomerNote($invoiceMailComment); // Kommentar auch in EMail
          $order->save();

          $url = Mage::getUrl('micropayment/payment/success/', array('_secure' => TRUE)) . '?oId=' . $orderId;
          $return = 'status=ok' . $crlf // ok  | error
            . 'url=' . $url . $crlf
            . 'target=_top' . $crlf
            . 'forward=1' . $crlf
            . 'message=OK' . $crlf;

          break;
        case 'REFUND':
        case 'STOP':
          // detect refund source in case it is from control center it should be booked otherwise not
          if (!empty($desc) && substr($desc, 0, 2) == '##'){
            // refund from inside shop so do nothing
          } else {
            try{
              $REFUNDMODE = $payment->getConfigData('refundmode');
              if ($REFUNDMODE == 'CREDITMEMO') {
                $service = Mage::getModel('sales/service_order', $order);
                $invoices = array();
                foreach ($order->getInvoiceCollection() as $invoice) {
                  if ($invoice->canRefund()) {
                    $invoices[] = $invoice;
                  }
                }
                foreach ($invoices as $invoice) {
                  $creditmemo = $service->prepareInvoiceCreditmemo($invoice);
                  $creditmemo->setRefundRequested(TRUE)
                    ->setOfflineRequested(FALSE)// request to refund online
                    ->register();

                  Mage::getModel('core/resource_transaction')
                    ->addObject($creditmemo)
                    ->addObject($creditmemo->getOrder())
                    ->addObject($creditmemo->getInvoice())
                    ->save();
                }
              } else {
                $payment->refundCall($order, $amount, $currency);
              }
              $return = 'status=ok' . $crlf . 'message=Refund done.' . $crlf;
            } catch (Mage_Core_Exception $e) {
              $return = 'status=error' . $crlf . 'message='.$e->getMessage() . $crlf;
              Mage::logException($e);
            } catch (Exception $e) {
              Mage::logException($e);
            }
          }
          break;

        case 'REVERSAL': // chargeback
          $order->setState($payment->getCancelState());
          $order->addStatusHistoryComment('Payment Cancelled. (STORNO)', $payment->getCancelState());
          $order->save();
          $return = 'status=ok' . $crlf . 'message=REVERSAL done.' . $crlf;
          break;

        case 'REFUNDREVERSAL':
          $order->addStatusHistoryComment('Payment Refund Reversal (REFUNDREVERSAL)', $payment->getPaymentState());
          $order->save();
          $return = 'status=ok' . $crlf . 'message=REFUNDREVERSAL done.' . $crlf;
          break;

        case 'BACKPAY': // backpay the chargeback
          $order->addStatusHistoryComment('Payment Cancellation refunded (BACKPAY)', $payment->getPaymentState());
          $order->save();
          $return = 'status=ok' . $crlf . 'message=BACKPAY done.' . $crlf;
          break;

      }

      if ($this->debug) $McPay->log->debug(__CLASS__.'->'.__FUNCTION__ . '->return: ' . print_r($return, 1));
    }
    return $return;
  }

  /**
   * Load quote and order objects from session
   */
  protected function _loadCheckoutObjects()
  {
    // load quote
    $this->getCheckout()->setQuoteId($this->getCheckout()->getMCPQuoteId(false));

    // load order
    $this->_order = Mage::getModel('sales/order');
    $this->_order->loadByIncrementId($this->getCheckout()->getMCPLastRealOrderId(false));
  }

  /**
   * threedsecureAction
   *
   */
  public function threedsecureAction()
  {
    $crlf = "\r\n";

    if (!empty($_REQUEST['check3dId'])){
      $check3dId = $_REQUEST['check3dId'];
    } else {
      echo '3D Secure failed!';
      exit();
    }

    $errorMsg = '';
    try {
      //load Quote and set active
      if ($quoteId = $this->getCheckout()->getLastQuoteId()) {
        $quote = Mage::getModel('sales/quote')->load($quoteId);
        $quote->setReservedOrderId(NULL)->setIsActive(true)->save();
      }

      // load quote and order
      $this->_loadCheckoutObjects();

      if ($this->_order->getPayment() !== false){
        $payment = $this->_order->getPayment()->getMethodInstance();
      } else {
        Mage::throwException('Cant load payment object');
      }

      $res = $payment->check3D($check3dId);
      if (isset($res->error) && $res->error == 0){
        $config = $payment->getBaseConfig();
        $post = array(
          'action'        => 'transactionCreate',
          'type'          => 'BOOKING',
          'sessionId'     => $res->sessionId,
          'transactionId' => $res->transactionId,
          'testMode'      => $config['testMode'],
        );
        // Base Shop Currency
        //$post['amount']		= number_format($this->_order->getBaseGrandTotal(), 2, '.', '');
        //$post['currency']	= $this->_order->getBaseCurrencyCode();
        // Order Currency
        $post['amount']		= number_format($this->_order->getGrandTotal(), 2, '.', '');
        $post['currency']	= $this->_order->getOrderCurrencyCode();

        $tmp = $this->responseCCard3DAction($post);
        //echo '<pre>'.print_r($tmp,1).'</pre>';
        $res = explode($crlf, $tmp);
        $status = explode('=', $res[0]);
        if ($status[1] == 'ok'){
          $url = substr($res[1], 4);
          $this->_redirectUrl($url);
        } else {
          $error = explode('=', $res[1]);
          $this->getCheckout()->addError($error[1]);
          $errorMsg = $error[1];
        }
        return;
      } else {
        $this->getCheckout()->addError($res->transactionResultMessage);
        $errorMsg = $res->transactionResultMessage;
      }
    } catch(Mage_Core_Exception $e) {
      $this->getCheckout()->addError($this->_getHelper()->__($e->getMessage()));
      $errorMsg = $e->getMessage();
    } catch(Exception $e) {
      Mage::logException($e);
      $this->getCheckout()->addError($this->_getHelper()->__($e->getMessage()));
      $errorMsg = $e->getMessage();
    }

    // log error in order
    if (!empty($errorMsg)){
      $this->_order->addStatusHistoryComment($errorMsg);
      $this->_order->save();
    }

    $this->_redirect('checkout/cart', array('_secure' => true));

  }

  /**
	 * successful return from payment
   */
  public function successAction()
  {
    try {
      // load quote and order
      $this->_loadCheckoutObjects();
      
      if ($this->_order->getPayment() !== false){
      	$payment = $this->_order->getPayment()->getMethodInstance();
      } else {
      	Mage::throwException('Cant load payment object');
      }

      // if order is failed
      if ($this->_order->getStatus() == $payment->getErrorState()) {
        $this->errorAction();
        return;
      }

      $this->getCheckout()->getQuote()->setIsActive(false)->save();
      // $this->getCheckout()->clear(); // created jump to shopping cart in 1.5.x
      Mage::dispatchEvent('mcpay_payment_controller_success_action');

      $this->rememberPayData($this->_order->getCustomerId(), $payment->getMethodCode());

      $locale     = explode('_', Mage::app()->getLocale()->getLocaleCode());
      if (is_array($locale) && ! empty($locale))
        $language = $locale[0];
      else
        $language = $this->getDefaultLocale();

      // add customer note to email
      $trxResult = $this->getSession()->getMcpayTrxResult();
      if (!empty($trxResult)) {
        $mailComment = '';
        if ($payment->getCode() == 'mcpay_sepa') {
          $mailComment = $trxResult->mandateTextEN;
          if (strtoupper($language) == 'DE') {
            $mailComment = $trxResult->mandateTextDE;
          }
        } else if ($payment->getCode() == 'mcpay_prepay') {
          $mailComment = $trxResult->prepayTextEN;
          if (strtoupper($language) == 'DE') {
            $mailComment = $trxResult->prepayTextDE;
          }
        }
        if (!empty($mailComment)) {
          $this->_order->addStatusHistoryComment($mailComment, $this->_order->getState());
          $this->_order->setCustomerNote($mailComment);
          $this->_order->save();
        }
      }


      //send confirmation email to customer
      if($this->_order->getId()) $this->_order->sendNewOrderEmail();
      // payment is okay. show success page.
      $this->getCheckout()->setLastSuccessQuoteId($this->_order->getQuoteId());
      $this->_redirect('checkout/onepage/success', array('_secure' => true));
      return;
    } catch(Mage_Core_Exception $e) {
      $this->getCheckout()->addError($this->_getHelper()->__($e->getMessage()));
    } catch(Exception $e) {
      Mage::logException($e);
    }

    $this->_redirect('checkout/cart', array('_secure' => true));
  }
  
  /**
	 * failed return from payment
   */
  public function errorAction()
  {
    try {
      // load quote and order
      $this->_loadCheckoutObjects();

      if ($this->_order->getPayment() !== false){
      	$payment = $this->_order->getPayment()->getMethodInstance();
      } else {
      	Mage::throwException('Cant load payment object');
      }

      $params = Mage::app()->getRequest()->getParams();
      if (isset($params['error'])){
      	$errormsg = utf8_encode($params['error']);
      } else {
      	$errormsg = $this->_getHelper()->__('An error occured during the payment process.');
      }

      //load Quote and set active
      if ($quoteId = $this->getCheckout()->getLastQuoteId()) {
      	$quote = Mage::getModel('sales/quote')->load($quoteId);
      	$quote->setReservedOrderId(NULL)->setIsActive(true)->save();
      }

      // cancel order
      if ($this->_order->canCancel()) {
        $this->_order->cancel();
        $this->_order->addStatusToHistory($payment->getErrorState(), $errormsg );
        $this->_order->save();
      }

      // add error message
      $this->getCheckout()->addError($errormsg);

      Mage::dispatchEvent('mcpay_payment_controller_error_action');
    } catch(Mage_Core_Exception $e) {
      $this->getCheckout()->addError($this->_getHelper()->__($e->getMessage()));
    } catch(Exception $e) {
      Mage::logException($e);
    }

    // redirect customer to cart
  	$this->_redirect('checkout/cart', array('_secure' => true));
  }

  /**
   * getStyleAction
   *
   * @return bool
   */
  public function getStyleAction()
  {
    require_once (MCPAY_BASE_PATH.'Classes/class.mcpay.php');
    $McPay = new mcpay();
    $this->getResponse()->clearHeaders()->setHeader('Content-Type', 'text/css');
    header('Content-Type: text/css');
    $req = $this->getRequest()->getParams();
    if (empty($req['sn'])) {
      if ($this->debug) $McPay->log->debug(__CLASS__.'->'.__FUNCTION__ . '->Req empty: ' . print_r($req['sn'], 1));
      echo '403';
      return FALSE;
    }

    $filename = MCPAY_BASE_PATH . 'css/' . $req['sn'].'';
    if (!file_exists($filename)) {
      if ($this->debug) $McPay->log->debug(__CLASS__.'->'.__FUNCTION__ . '->File not found: ' . print_r($filename, 1));
      echo '404';
      return FALSE;
    }

    $content = file_get_contents($filename);

    if (strpos($filename, 'mcpay_formstyle_') !== FALSE){
      $baseURL = Mage::getUrl('js', array('_secure' => 1));
      /*
      $isSecure = Mage::app()->getStore()->isCurrentlySecure();
      if ($isSecure){
        $baseURL = str_replace('http://', 'https://', $baseURL);
      }
      */
      $repl = array(
        "url('../fonts/" => "url('".$baseURL."../micropayment/payment/getfont/font/",
      );
      $content = strtr($content, $repl);
    }
    echo $content;
  }

  /**
   * getScriptAction
   *
   * @return bool
   */
  public function getScriptAction ()
  {
    require_once (MCPAY_BASE_PATH.'Classes/class.mcpay.php');
    $McPay = new mcpay();
    $this->getResponse()->clearHeaders()->setHeader('Content-Type', 'application/javascript');
    header('Content-Type: application/javascript');
    $req = $this->getRequest()->getParams();
    if (empty($req['sn'])) {
      if ($this->debug) $McPay->log->debug(__CLASS__.'->'.__FUNCTION__ . '->Req empty: ' . print_r($req['sn'], 1));
      echo '403';
      return FALSE;
    }

    $filename = MCPAY_BASE_PATH . 'js/' . $req['sn'].'';
    if (!file_exists($filename)) {
      if ($this->debug) $McPay->log->debug(__CLASS__.'->'.__FUNCTION__ . '->File not found: ' . print_r($filename, 1));
      echo '404';
      return FALSE;
    }
    $baseURL = Mage::getUrl('js', array('_secure' => 1));
    /*
    $isSecure = Mage::app()->getStore()->isCurrentlySecure();
    if ($isSecure){
      $baseURL = str_replace('http://', 'https://', $baseURL);
    }
    */
    $content = file_get_contents($filename);
    if (strpos($filename, 'executer.js') !== FALSE){
      $pm = Mage::getModel('mcpay/ccard');
      $config = $pm->getBaseConfig();
      $repl = array(
        'dt_method_mcpay_ccard' => $config['ccardformid'],
        'dt_method_mcpay_sepa' => $config['sepaformid'],
        '"/micropayment/payment/' => '"'.$baseURL.'../micropayment/payment/',
      );
      $content = strtr($content, $repl);
    }
    if (strpos($filename, 'check.js') !== FALSE){
      $pm = Mage::getModel('mcpay/ccard');
      $config = $pm->getBaseConfig();
      $repl = array(
        'payformid' => $config['payformid'],
        'mcpay_card_token-form' => $config['payformid'],
      );
      $content = strtr($content, $repl);
    }

    echo $content;
  }

  /**
   * getFontAction
   *
   * @return bool
   */
  public function getFontAction ()
  {
    require_once (MCPAY_BASE_PATH.'Classes/class.mcpay.php');
    $McPay = new mcpay();
    $this->getResponse()->clearHeaders()->setHeader('Content-Type', 'font/opentype');
    header('Content-Type: font/opentype');
    $req = $this->getRequest()->getParams();
    if (empty($req['font'])) {
      if ($this->debug) $McPay->log->debug(__CLASS__.'->'.__FUNCTION__ . '->Req empty: ' . print_r($req['font'], 1));
      echo '403';
      return FALSE;
    }

    $filename = MCPAY_BASE_PATH . 'fonts/' . $req['font'].'';
    if (!file_exists($filename)) {
      if ($this->debug) $McPay->log->debug(__CLASS__.'->'.__FUNCTION__ . '->File not found: ' . print_r($filename, 1));
      echo '404';
      return FALSE;
    }
    $content = file_get_contents($filename);
    echo $content;
  }

  /**
   * rememberPayData
   *
   * @param $userId
   * @param $paymethod
   *
   * @throws \Mage_Core_Exception
   */
  private function rememberPayData($userId, $paymethod)
  {
    $rememberCCard = $this->getSession()->getMcpayCardRemember();
    $rememberSepa = $this->getSession()->getMcpaySepaRemember();
    //mail('webmaster@web-dezign.de', __CLASS__.'->'.__FUNCTION__.'->$rememberCCard', print_r($rememberCCard,1).print_r($this->getSession(),1));

    try{
      $collection = Mage::getModel('mcpayio/payio')->getCollection()->setOrder('paydata_id','asc');
      $collection->addFieldToFilter('user_id', $userId);
      $collection->addFieldToFilter('paymethod', $paymethod);

      $res = $collection->getData();
      $dbId = $res[0]['paydata_id'];
      //mail('webmaster@web-dezign.de', __CLASS__.'->'.__FUNCTION__.'->$res', print_r($res,1));
      // ccard
      if ($paymethod == 'mcpay_ccard' && $rememberCCard) {
        $payData = array(
          'holder'  => $this->getSession()->getMcpayCardHolder(),
          'month'   => sprintf('%02d', $this->getSession()->getMcpayCardMonth()),
          'year'    => $this->getSession()->getMcpayCardYear() + 2000,
          'pan'     => $this->getSession()->getMcpayCardPan(),
          'cvc'     => $this->getSession()->getMcpayCardCVC(),
          'token'   => $this->getSession()->getMcpayCardToken(),
        );
        // set brand
        $cardType = 'visa';
        if (substr($payData['pan'], 0, 1) == '5') $cardType = 'mastercard';
        $payData['brand'] = $cardType;
      }
      // sepa
      if ($paymethod == 'mcpay_sepa' && $rememberSepa) {
        $payData = array(
          'holder'  => $this->getSession()->getMcpayHolder(),
          'iban'    => $this->getSession()->getMcpayIBAN(),
          'bic'     => $this->getSession()->getMcpayBIC(),
        );
      }

      if (!empty($payData)) {
        $data = array(
          'user_id'       => $userId,
          'data'          => serialize($payData),
          'paymethod'     => $paymethod,
          'last_modified' => date('Y-m-d H:i:s')
        );
        $model = Mage::getModel('mcpayio/payio')->load($dbId)->addData($data);
        $model->setId($dbId)->save();
      }

    } catch (Exception $e) {
      //mail('webmaster@web-dezign.de', __CLASS__.'->'.__FUNCTION__.'->$e->getMessage()', print_r($e->getMessage(),1));
      Mage::throwException('Block_Form_Ccard->Construct: '.$e->getMessage());
    }

  }

  /**
   * sendNotifyEMail
   *
   * @param $orderId
   * @param $type
   */
  public function sendNotifyEMail($orderId, $type)
  {
    $sysconfig = Mage::getConfig()->getNode('default/micropayment/settings');
    //echo '<pre>'.print_r($sysconfig, 1).'</pre>';
    $field = 'email_'.strtolower($type);
    if (!empty($sysconfig->$field)){
      //echo $sysconfig->$field;
      $post = Mage::app()->getRequest()->getParams();
      //echo '<pre>POST: '.print_r($post, 1).'</pre>';

      $to = $sysconfig->$field;
      $subject = $type.' Notification received for Order '.$orderId;
      $body = print_r($post,1);
      //echo 'Sending mail to '.$to.' with subject '.$subject.' and body '.$body;
      mail($to, $subject, $body);
    }
  }

} // end of class