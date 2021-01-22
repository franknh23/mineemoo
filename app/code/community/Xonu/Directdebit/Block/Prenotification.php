<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Block_Prenotification extends Mage_Core_Block_Template {

    public function _toHtml() {

        /* @var $order Mage_Sales_Model_Order */
        $order = $this->getOrder();

        if($order) // required to avoid error in e-mail template preview
        {
            $payment = $order->getPayment();

            if($payment->getSepaIban() != '')
            {
                $storeId = $order->getStoreId();
                $templateId = Mage::getStoreConfig('xonu_directdebit/prenotification/template', $storeId);

                if(is_numeric($templateId))
                    $notificationTemplate = Mage::getModel('core/email_template')->load($templateId);
                else {
                    $localeCode = Mage::getStoreConfig('general/locale/code', $storeId);
                    $notificationTemplate = Mage::getModel('core/email_template')->loadDefault($templateId, $localeCode);
                }

                $templateData = array(
                    'order'                => $order,
                    'billing'              => $order->getBillingAddress(),

                    'order_total'          => Mage::helper('core')->currency($order->getGrandTotal()),
                    'sepa_mandate_id'      => $payment->getSepaMandateId(),
                    'sepa_creditor_id'     => Mage::getStoreConfig('payment/xonu_directdebit/creditor_identifier', $storeId),
                    'sepa_iban'            => $payment->getSepaIban(),
                    'sepa_bic'             => $payment->getSepaBic(),
                    'sepa_collection_date' => $this->getLocalDueDate($order->getCreatedAt(), $storeId)
                );
                return $notificationTemplate->getProcessedTemplate($templateData);
            }
        }
        return '';
    }


    /**
     * calculates the due date for the pre-notification
     * @param null $timestamp
     * @param null $storeId
     * @return Zend_Date
     */
    public function getLocalDueDate($timestamp = null, $storeId = null) {
        $weekdays = (int)Mage::getStoreConfig('xonu_directdebit/prenotification/weekday_interval');
        $duedateSeconds = strtotime("$timestamp +$weekdays weekdays");
        $localeCode = null; if(!is_null($storeId)) $localeCode = Mage::getStoreConfig('general/locale/code', $storeId);
        $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_LONG);
        $duedate = Mage::app()->getLocale()->date($duedateSeconds, null, $localeCode);
        return $duedate->toString($format);
    }
}