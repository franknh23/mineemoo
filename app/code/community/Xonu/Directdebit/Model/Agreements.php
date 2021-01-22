<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Model_Agreements {

    protected $agreementActive;
    protected $agreementClass;
    protected $agreementCollectionClass;

    public function __construct() {
        if(Mage::getStoreConfigFlag('xonu_directdebit/mandate/mandate_terms_active')
            && Mage::getStoreConfigFlag('checkout/options/enable_agreements'))
        {
            $this->agreementActive = Mage::getStoreConfigFlag('payment/xonu_directdebit/active');

            $this->agreementClass = get_class(Mage::getModel('checkout/agreement'));
            $this->agreementCollectionClass = get_class(Mage::getModel('checkout/agreement')->getCollection());
            $this->mandateAgreementId = (int)Mage::getStoreConfig('xonu_directdebit/mandate/mandate_terms');
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function controllerActionPredispatchCheckout(Varien_Event_Observer $observer) {
        // if($this->agreementActive) {
            try {
                $controller = $observer->getControllerAction();
                $actionName = $controller->getRequest()->getActionName();

                if($actionName == 'saveOrder' || $actionName == 'placeOrder') {
                    if(Mage::getStoreConfigFlag('xonu_directdebit/mandate/mandate_terms_active')) {

                        $code = Mage::getModel('xonu_directdebit/payment_method_config')->getCode();
                        $payment = Mage::getSingleton('checkout/session')->getQuote()->getPayment();
                        if($code != $payment->getMethodInstance()->getCode() || $payment->getSepaMandateId() != '') {
                            $agreementsItemKey = (int)Mage::getStoreConfig('xonu_directdebit/mandate/mandate_terms');
                            $_POST['agreement'][$agreementsItemKey] = 1;
                        }
                    }
                }
            } catch(Exception $e) {
                return;
            }
        // }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function coreAbstractLoadAfter(Varien_Event_Observer $observer) {
        if($this->agreementClass == get_class($agreement = $observer->getEvent()->getDataObject())) {
            if($this->agreementActive) {
                if($agreement->getAgreementId() == $this->mandateAgreementId) {
                    $this->templateFilter($agreement);
                }
            }
        }
    }


    /**
     * @param Varien_Event_Observer $observer
     */
    public function coreCollectionAbstractLoadAfter(Varien_Event_Observer $observer) {
        if($this->agreementCollectionClass == get_class($agreementCollection = $observer->getCollection())) {
            if($this->agreementActive) {
                $agreementItemKey = (int)Mage::getStoreConfig('xonu_directdebit/mandate/mandate_terms');

                $code = Mage::getModel('xonu_directdebit/payment_method_config')->getCode();
                $quote = Mage::getSingleton('checkout/session')->getQuote();
                $payment = $quote->getPayment();

                try{ $selectedPaymentCode = $payment->getMethodInstance()->getCode(); } catch(Exception $e) {}

                if(($code != $selectedPaymentCode && $selectedPaymentCode != '') || $payment->getSepaMandateId() != ''){
                    $agreementCollection->removeItemByKey($agreementItemKey);
                } else {
                    $agreement = $agreementCollection->getItemById($agreementItemKey);
                    $this->templateFilter($agreement);
                }

            } else {
                $agreementItemKey = (int)Mage::getStoreConfig('xonu_directdebit/mandate/mandate_terms');
                $agreementCollection->removeItemByKey($agreementItemKey);

            }
        }
    }

    /**
     * @param $agreement
     * @return void
     */
    protected function templateFilter($agreement) {
        try {
            if($agreement) {
                $session = Mage::getSingleton('checkout/session');
                $quote = $session->getQuote();

                $mandate = Mage::getModel('xonu_directdebit/mandate')
                    ->setPreview(true)
                    ->createFromOrder($quote);

                $templateData = array(
                    'mandate' => $mandate,
                    'mandate_checkbox' => $mandate->getCheckboxText(),
                    'mandate_content'  => $mandate->getDocumentHtml(),

                    'creditor_html' => $mandate->getCreditorHtml(),
                    'debitor_html'  => $mandate->getDebitorHtml(),

                    'creditor' => $mandate->getCreditor(),
                    'debitor'  => $mandate->getDebitor(),

                    'grant_date' => $mandate->getLocalCreatedAt(),
                );

                $templateFilter = Mage::getModel('cms/template_filter');
                $templateFilter->setVariables($templateData);
                $agreement->setContent($templateFilter->filter($agreement->getContent()));
                $agreement->setCheckboxText($templateFilter->filter($agreement->getCheckboxText()));
            }
        } catch(Exception $e) {}

        return;
    }

}