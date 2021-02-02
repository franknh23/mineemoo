<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Helper_Invoice extends Mage_Core_Helper_Abstract {

    public function createInvoice($order) {

        $storeId = $order->getStoreId();
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

}