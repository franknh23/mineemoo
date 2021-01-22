<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Model_Payment_Method_Config extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'xonu_directdebit';
    protected $_transactionObjects = array();

    /**
     * Payment Method features
     * @var bool
     */
    protected $_canOrder = true;
    protected $_canAuthorize = false;
    protected $_canCapture = false;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canManageRecurringProfiles = false;
    protected $_isInitializeNeeded = true;


    protected $_formBlockType = 'xonu_directdebit/form';
    protected $_infoBlockType = 'xonu_directdebit/info';

    /**
     * @param $targetState
     * @param $stateObject
     * @return Xonu_Directdebit_Model_Payment_Method_Config
     */
    public function initialize($targetState, $stateObject)
    {
        $stateObject->setState(Mage_Sales_Model_Order::STATE_NEW)
                ->setStatus(true)
                ->getIsNotified(false);

        $this->_setOrderState($targetState, $stateObject);

        return $this;
    }

    /**
     * @param $targetState
     * @param $stateObject
     * @return Xonu_Directdebit_Model_Payment_Method_Config
     */
    protected function _setOrderState($targetState, $stateObject)
    {
        /* @var $order Mage_Sales_Model_Order */
        $store = Mage::app()->getStore($this->getStore());
        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();
        $amount = $store->roundPrice($order->getBaseTotalDue(), true);

        switch ($targetState) {
            case Mage_Sales_Model_Order::STATE_NEW:
            case Mage_Sales_Model_Order::STATE_HOLDED:
            case Mage_Sales_Model_Order::STATE_CANCELED:
            default:
                // No action
                $stateObject->setState($targetState);
                break;

            case Mage_Sales_Model_Order::STATE_PROCESSING:
            case Mage_Sales_Model_Order::STATE_PENDING_PAYMENT:
                // Create invoice
                $this->_invoice($order, $amount, false);
                $stateObject->setState($targetState);
                break;

            case Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW:
                // Create invoice
                $this->_invoice($order, $amount, false);
                // payment review and can_creditmemo true cause state to be complete
                // payment review and can_creditmemo false cause state to be closed
                $order->unsForcedCanCreditmemo();
                $stateObject->setState($targetState);
                break;

            case Mage_Sales_Model_Order::STATE_COMPLETE:
                // Create Invoice and Shipment
                $this->_invoice($order, $amount, true);
                $this->_ship($order);
                break;

            case Mage_Sales_Model_Order::STATE_CLOSED:
                // Create Invoice, Shipment and Creditmemo
                $invoice = $this->_invoice($order, $amount, true);
                $this->_ship($order);
                $this->_refund($invoice);
                $order->unsForcedCanCreditmemo();
                break;
        }
        $this->_saveTransaction();

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param $amount
     * @param $isPayed
     * @return Mage_Sales_Model_Order_Invoice
     */
    protected function _invoice(Mage_Sales_Model_Order $order, $amount, $isPayed)
    {
        $invoice = $order->prepareInvoice()->register();
        $order->setForcedCanCreditmemo(true);
        $this->addTransactionObject($order, 'order')
                ->addTransactionObject($invoice, 'invoice');
        return $invoice;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Order_Shipment
     */
    protected function _ship(Mage_Sales_Model_Order $order)
    {
        $shipment = $order->prepareShipment()->register();
        $order->setForcedCanCreditmemo(true);
        $this->addTransactionObject($order, 'order')
                ->addTransactionObject($shipment, 'shipment');
        return $shipment;
    }

    /**
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    protected function _refund(Mage_Sales_Model_Order_Invoice $invoice)
    {
        // Only paid invoices may be refunded
        $invoice->setTotalPaid($invoice->getGrandTotal())
                ->setBaseTotalPaid($invoice->getBaseGrandTotal());
        $invoice->getOrder()
                ->setTotalPaid($invoice->getTotalPaid())
                ->setBaseTotalPaid($invoice->getBaseTotalPaid());

        /* @var $service Mage_Sales_Model_Service_Order */
        $service = Mage::getModel('sales/service_order', $invoice->getOrder());
        $creditmemo = $service->prepareInvoiceCreditmemo($invoice);

        $backToStock = Mage::helper('cataloginventory')->isAutoReturnEnabled();
        foreach ($creditmemo->getAllItems() as $creditmemoItem) {
            $creditmemoItem->setBackToStock($backToStock);
        }

        $creditmemo->register();

        $this->addTransactionObject($creditmemo->getOrder(), 'order')
                ->addTransactionObject($creditmemo, 'creditmemo');
        if ($creditmemo->getInvoice()) {
            $this->addTransactionObject($creditmemo->getInvoice(), 'invoice');
        }
        return $creditmemo;
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @param $alias
     * @return Xonu_Directdebit_Model_Payment_Method_Config
     */
    protected function addTransactionObject(Mage_Core_Model_Abstract $object, $alias)
    {
        $this->_transactionObjects[$alias] = $object;
        return $this;
    }

    /**
     * @return Xonu_DirectDebit_Model_Payment_Method_Config
     */
    protected function _saveTransaction()
    {
        if ($this->_transactionObjects) {
            $transaction = Mage::getModel('core/resource_transaction');
            foreach ($this->_transactionObjects as $object) {
                $transaction->addObject($object);
            }
            $transaction->save();
        }
        return $this;
    }


    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();

        $iban = $this->_sanitizeInput($data->getIban());

        if($iban == '' || Mage::app()->getStore()->isAdmin()) {
            $_mandate = Mage::getModel('xonu_directdebit/mandate')->loadCustomerMandate();
            if($_mandate) {
                $holder = $_mandate->getAccountHolder();
                $iban   = $_mandate->getIban();
                $bic    = $_mandate->getBic();

                $info->setSepaHolder($holder)
                     ->setSepaIban($iban)
                     ->setSepaBic($bic)
                     ->setSepaMandateId($_mandate->getMandateIdentifier());
            } else {
                return $this;
            }
        } else {
            $holder = $this->_sanitizeInput($data->getHolder(), true);
            $bic = $this->_sanitizeInput($data->getBic());

            $info->setSepaHolder($holder)
                 ->setSepaIban($iban)
                 ->setSepaBic($bic)
                 ->setSepaMandateId(null);
        }

        return $this;
    }

    /**
     * @param $input
     * @param bool $simpleMode
     * @return string
     */
    protected function _sanitizeInput($input, $simpleMode = false) {
        if($simpleMode) {
            $output = trim($input);
            $output = preg_replace('/[ \t]+/', ' ', $output); // remove multiple spaces
        } else {
            $output = strtoupper($input);
            $output = preg_replace('/[^A-Z0-9]/', '', $output); // remove all invalid characters
        }
        return (string)$output;
    }

    /**
     * @param string $input
     * @param $mode
     * @return bool
     */
    protected function _validateInput($input, $mode) {
        switch($mode) {
            case 'IBAN':
                $iban = $input;

                // validate length
                if(!(strlen($iban) >= 18 && strlen($iban) <= 34)) return false;

                // validate format
                if(!preg_match('/[A-Z][A-Z][0-9]+/', $iban)) return false;

                // validate country
                if(Mage::getStoreConfigFlag('xonu_directdebit/iban/validation_iban_country')) {
                    $countryCodeIban = substr($iban, 0, 2);
                    $countryCodeQuote = Mage::getSingleton('checkout/session')
                                        ->getQuote()->getBillingAddress()->getCountry();
                    if($countryCodeIban != $countryCodeQuote) return false;
                }

                // validate checksum
                if(Mage::getStoreConfigFlag('xonu_directdebit/iban/validation_iban_checksum')) {
                    for(
                        $c = 0,
                        $ibanReordered = substr($iban, 4).substr($iban, 0, 4),
                        $ibanLength = strlen($ibanReordered),
                        $ibanNumeric = ''
                    ;
                        $c < $ibanLength
                    ;
                        $c++
                    )
                    {
                        $char = substr($ibanReordered, $c, 1);
                        if(!is_numeric($char)) $char = ord($char) - 55;
                        $ibanNumeric .= $char;
                    }
                    $result = bcmod($ibanNumeric, 97);
                    if($result[0] != 1) return false;
                }

                return true;

            case 'BIC':
                $bic = $input;

                // optional bic can be empty or valid according to validation settings
                $_helper = Mage::helper('xonu_directdebit');
                $_bicOptional = $_helper->isBicOptional();
                if($bic == '' && $_bicOptional) return true;

                // validate length
                if(!(strlen($bic) == 8 || strlen($bic) == 11)) return false;

                // validate country
                if(Mage::getStoreConfigFlag('xonu_directdebit/bic/validation_bic_country')) {
                    $countryCodeBic = substr($bic, 4, 2);
                    $countryCodeQuote = Mage::getSingleton('checkout/session')
                        ->getQuote()->getBillingAddress()->getCountry();
                    if($countryCodeBic != $countryCodeQuote) return false;
                }

                // validate format according to major requirements of ISO 9362
                if(Mage::getStoreConfigFlag('xonu_directdebit/bic/validation_bic_format')) {
                    if(!preg_match('/[A-Z]{4}[A-Z]{2}[A-Z2-9][A-Z1-9]([A-Z0-9]{3})?/', $bic)) return false;
                }

                return true;
        }
    }

    public function validate()
    {
        parent::validate();

        $info = $this->getInfoInstance();


        if(Mage::app()->getStore()->isAdmin()) {


            $_mandate = Mage::getModel('xonu_directdebit/mandate')->loadCustomerMandate();
            if(!$_mandate) {
                $errorMessage = $this->_getHelper()->__('SEPA Direct Debit') . ': ' .
                    $this->_getHelper()
                         ->__('This payment method requires valid SEPA Direct Debit Mandate for Recurrent Payment.');

                Mage::throwException($errorMessage);
            }

        } elseif($info->getSepaMandateId() == '') {
            // $holder = $info->getSepaHolder();
            $iban   = $info->getSepaIban();
            $bic    = $info->getSepaBic();

            $errors = array();

            if(Mage::getStoreConfigFlag('xonu_directdebit/iban/validation_iban_active')) {
                if(!$this->_validateInput($iban, 'IBAN')) {
                    $errors[] = $this->_getHelper()->__('IBAN');
                }
            }

            if(Mage::getStoreConfigFlag('xonu_directdebit/bic/validation_bic_active')) {
                if(!$this->_validateInput($bic, 'BIC')) {
                    $errors[] = $this->_getHelper()->__('BIC');
                }
            }

            if(sizeof($errors)){
                $errorMessage = $this->_getHelper()->__('Invalid or incomplete information:')."\n";
                $errorMessage .= join("\n", $errors);
            }

            if(isset($errorMessage)){
                Mage::throwException($errorMessage);
            }
        } else {
            // do not check IBAN and BIC for a valid mandate
        }

        return $this;
    }

    /**
     * Set refund transaction id to payment object for informational purposes
     * Candidate to be deprecated:
     * there can be multiple refunds per payment, thus payment.refund_transaction_id doesn't make big sense
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Payment_Model_Method_Abstract
     */
    public function processBeforeRefund($invoice, $payment)
    {
        $payment->setRefundTransactionId($invoice->getTransactionId());
        return $this;
    }
}
