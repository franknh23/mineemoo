<?php

class Xonu_Sepaone_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        if(!Mage::getStoreConfigFlag('xonu_directdebit/sepaone/webhook_active')) $this->_redirect('/');

        $helper = Mage::helper('xonu_sepaone');

        // check webhook secret
        $secret = $helper->getWebhookSecret();
        if($secret !== false) {

            $urlParts = explode('?', $this->getRequest()->getRequestUri());
            if(sizeof($urlParts) == 2) {
                $requestSecret = $urlParts[1];
                if($requestSecret != $secret) {
                    $this->_redirect('/'); return;
                }
            } else {
                $this->_redirect('/'); return;
            }
        }


        // check request body
        $requestBody = file_get_contents('php://input');
        if($requestBody == '') { $this->_redirect('/'); return; }

        // prepare
        $api = Mage::getModel('xonu_sepaone/api');

        $tStart = $api->mtime();
        $log = Mage::getModel('xonu_sepaone/log');

        $log->setRequestType('events');
        $log->setRequestUri($helper->getWebhookUrl());
        $log->setRequestAt($api->timestampLocal());

        $log->setRequestBody($requestBody);
        $log->setRequestBodyLength(strlen($requestBody));

        $response = array();
        $response['remote_ip'] = $_SERVER['REMOTE_ADDR'];

        // process request
        $event = json_decode($requestBody, true);
        if(isset($event)) {

            if($event['action'] == 'transaction.updated.status') {
                $transactionId = $event['details']['id'];
                $status = $event['details']['status'];
                // $statusBefore = $event['details']['changes']['status'][0];

                /* @var $export Xonu_Sepaone_Model_Resource_Export_Collection */
                $export = Mage::getResourceModel('xonu_sepaone/export')->getCollection()
                    ->addFieldToFilter('last_transaction_id', array('eq' => $transactionId))
                    ->getFirstItem();
                $orderId = $export->getOrderId();
                $export->setLastTransactionStatus($status)->save();
                $log->setOrderId($orderId);

                if($orderId) {
                    $order = Mage::getModel('sales/order')->load($orderId);
                    $response['order_state'] = $order->getState();
                    $response['order_status'] = $order->getStatus();

                    $response['order_id'] = $orderId;
                    $response['order_increment_id'] = $order->getIncrementId();
                    $log->setOrderIncrementId($order->getIncrementId());

                    $response['transaction_status'] = $status;

                    if($status == 'funds_received') {
                        if(Mage::getStoreConfig('xonu_directdebit/sepaone/create_invoice')) {
                            $order = Mage::getModel('sales/order')->load($orderId);
                            if (!$order->hasInvoices() || true) {

                                $payment = $order->getPayment();
                                $payment->setTransactionId($transactionId)
                                    ->setCurrencyCode($order->getBaseCurrencyCode())
                                    ->setPreparedMessage('')
                                    ->setParentTransactionId()
                                    ->setShouldCloseParentTransaction(true)
                                    ->setIsTransactionClosed(false)
                                    ->registerCaptureNotification($order->getBaseGrandTotal())
                                ;
                                $order->save();
                                $response['messages'][] = "invoice and transaction created";

                                /*
                                // create invoice
                                $invoice = $order->prepareInvoice();
                                $invoice->setTransactionId($transactionId);
                                $invoice->register()->pay();
                                $invoice->save();

                                // change order state and status
                                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
                                $order->save();
                                */

                                // send invoice email
                                if ($order->hasInvoices())
                                {
                                    $response['messages'][] = "invoice sent";
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
                    } elseif(substr($status, 0, 10) == 'chargeback') {

                        if(Mage::getStoreConfig('xonu_directdebit/sepaone/create_invoice')) {

                            if($order->canHold()) {
                                $order->hold()->save();
                                $response['messages'][] = "set order on hold";
                            } else {
                                $response['messages'][] = "cannot set order on hold";
                            }

                        }

                    }
                } else {
                    $response['messages'][] = "order not found";
                }


            } elseif($event['action'] == 'mandate.updated.status') {

                $mandateId = $event['details']['reference'];
                $response['mandate_id'] = $mandateId;
                $log->setMandateId($mandateId);
                $status = $event['details']['status'];
                $mandate = Mage::getModel('xonu_directdebit/mandate')->load($mandateId, 'mandate_identifier');
                if($mandate->getId()) {
                    $response['mandate_remote_status'] = $status;

                    if($status == 'canceled' || $status == 'obsolete' || $status = 'expired') {
                        $mandate->setRevoked(true);
                        $mandate->save();
                        $response['messages'][] = "mandate revoked";
                    } else {
                        // ignore other status codes
                        $response['messages'][] = "mandate not revoked";
                    }
                } else {
                    $response['messages'][] = "mandate not found";
                }
            }

            $tFinish = $api->mtime();
            $log->setResponseTime($tFinish - $tStart);
            $log->setResponseAt($api->timestampLocal());

            $responseJson = json_encode($response);
            $log->setResponseBody($responseJson);
            $log->setResponseBodyLength(strlen($responseJson));

            $log->save();
        }
    }
}