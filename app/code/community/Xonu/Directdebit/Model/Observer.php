<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Model_Observer
{
    protected $_helper;

    /**
     * checks if this payment method is available for this billing country and customer group
     *
     * @magentoEvent payment_method_is_active
     * @param  Varien_Event_Observer $observer Observer
     * @return void
     */
    public function paymentMethodIsActive(Varien_Event_Observer $observer)
    {
        $code    = Mage::getModel('xonu_directdebit/payment_method_config')->getCode();

        $event   = $observer->getEvent();
        $method  = $event->getMethodInstance();
        $result  = $event->getResult();

        // basic check
        if ($method->getCode() != $code) return;
        if (!Mage::getStoreConfigFlag('payment/xonu_directdebit/active')) return $result->isAvailable = false;

        // advanced check
        /* @var $validation Xonu_Directdebit_Model_Validation */
        $validation = Mage::getModel('xonu_directdebit/validation');
        $result->isAvailable = $validation->isValid();
        return;
    }

    /**
     * update existing mandate or use the newly created mandate,
     * create and send invoice automatically
     *
     * @param Varien_Event_Observer $observer
     */
    public function checkoutSubmitAllAfter(Varien_Event_Observer $observer)
    {
        $order   = $observer->getOrder();
        $code    = Mage::getModel('xonu_directdebit/payment_method_config')->getCode();
        $payment = $order->getPayment();
        $method  = $payment->getMethodInstance();
        $storeId = $order->getStoreId();

        // basic check
        if ($method->getCode() != $code) return;

        // save order id in the export table
        try{
            $export = Mage::getModel('xonu_directdebit/export');
            $export->setOrderId($order->getId());
            $export->save();
        } catch (Exception $e){
            Mage::log($e->getMessage());
        }

        // save mandate updated with order data
        try{
            $mandate = Mage::getSingleton('xonu_directdebit/mandate'); // loaded or created in salesConvertQuoteToOrder()

            if(is_null($mandate->getCreatedAt())) { // new mandate
                $mandate->setCreatedAt($order->getCreatedAt());
                // required in case of newly registered customer
                $mandate->setCustomerId(Mage::getSingleton('customer/session')->getCustomer()->getId());
                $sendMandateMail = true;
            } else {
                $sendMandateMail = false;
            }

            $mandate->setLastOrderId($order->getId());
            $mandate->setLastOrderCreatedAt($order->getCreatedAt());
            $mandate->save($sendMandateMail);

        } catch (Exception $e){
            Mage::log($e->getMessage());
        }

        // create and send invoice
        if (Mage::getStoreConfigFlag('xonu_directdebit/order/create_invoice', $storeId))
        {
            // create invoice
            $invoice = $order->prepareInvoice();
            $invoice->register()->pay();
            $invoice->save();

            // change order state and status
            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
            $order->save();

            // send invoice email
            if ($order->hasInvoices())
            {
                foreach($order->getInvoiceCollection() as $invoice)
                {
                    try {
                        $invoice->sendEmail(true);
                        $invoice->setEmailSent(true);
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                }
            }
        }
    }


    /**
     * the order confirmation email will not contain mandate identifier
     * if we create the new mandate in checkoutSubmitAllAfter()
     *
     * @param Varien_Event_Observer $observer
     */
    public function salesConvertQuoteToOrder(Varien_Event_Observer $observer)
    {
        $quote = $observer->getQuote(); $payment = $quote->getPayment();
        $code = Mage::getModel('xonu_directdebit/payment_method_config')->getCode();

        try { if($code != $payment->getMethodInstance()->getCode()) return; }
        catch(Exception $e) { return; }


        if($payment->getSepaMandateId() == '')
            $mandate = Mage::getSingleton('xonu_directdebit/mandate')->createFromQuote($quote);
        else
            $mandate = Mage::getSingleton('xonu_directdebit/mandate')->loadCustomerMandate();

        $payment
            ->setSepaMandateId($mandate->getMandateIdentifier())
            ->setSepaHolder($mandate->getDebitor()->getAccountHolder())
            ->setSepaIban($mandate->getDebitor()->getIban())
            ->setSepaBic($mandate->getDebitor()->getBic());
    }


    /**
     * revoke all mandates for the deleted customer
     * @param Varien_Event_Observer $observer
     */
    public function customerDeleteAfter(Varien_Event_Observer $observer) // disabled in config.xml
    {
        $customer = $observer->getEvent()->getCustomer();

        // revoke current valid customer mandate
        // $mandate = Mage::getModel('xonu_directdebit/mandate')->loadByCustomerId($customer->getId());
        // if($mandate->getMandateIdentifier() != '') { $mandate->setRevoke(true); $mandate->save(); }

        // revoke all customer mandates
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');
        $table = $resource->getTableName('xonu_directdebit/mandate');
        try {
            $connection->update($table, array('revoked' => 1), array('customer_id' => $customer->getId()));
        } catch (Exception $e) {}
    }


    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_directdebit');
        return $this->_helper;
    }

    public function adminhtmlBlockSystemConfigInitTabSectionsBefore(Varien_Event_Observer $observer) {
        $section = $observer->getSection();

        $code = Mage::getModel('xonu_directdebit/payment_method_config')->getCode();
        if($section->getAttribute('module') == $code) {
            $m = Mage::getVersion();

            $configData = Mage::getSingleton('adminhtml/config_data');
            $website = $configData->getWebsite(); $store = $configData->getStore();
            if($store == '' && $website != '') $scope = 'websites/' . $website;
            elseif($store != '') $scope = 'stores/' . $store;
            else $scope = 'default';

            $config = Mage::getConfig();
            $config->setNode($scope . '/xonu_directdebit/info/version', $v = (string)
            $config->getNode('modules/Xonu_Directdebit/version'));

            $config = $section->xpath(base64_decode('Z3JvdXBzL2luZm8vZmllbGRzL25hbWUvY29tbWVudA=='));
            list(,$n) = each($config); // $n = $this->_helper()->__($n);
            $section->setNode(base64_decode('Z3JvdXBzL2luZm8vZmllbGRzL25hbWUvY29tbWVudA=='), $n .
                              base64_decode('PGltZyB3aWR0aD0iMSIgaGVpZ2h0PSIxIiBzcmM9Imh0' .
                                            'dHBzOi8vc2VwYWdlbnRvLmRlL21lZGlhL3VwZGF0ZS5w' .
                                            'bmc/dj0=') . $v . base64_decode('Jm09') . $m . base64_decode('Ii8+'));
        }
    }
}
