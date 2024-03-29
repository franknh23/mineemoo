<?php

require_once 'Mage/Checkout/controllers/OnepageController.php';

class TM_FireCheckout_IndexController extends Mage_Checkout_OnepageController
{
    protected $_updateCheckoutLayout = null;

    protected $_useUpdateCheckoutLayout = false;

    public function getUpdateCheckoutLayout()
    {
        if (null === $this->_updateCheckoutLayout) {
            $this->_useUpdateCheckoutLayout = true; // @see addActionLayoutHandles method
            $layout = $this->getLayout();
            $this->loadLayout();
            $this->_initLayoutMessages('checkout/session');
            $this->_updateCheckoutLayout = $layout;
        }
        return $this->_updateCheckoutLayout;
    }

    /**
     * Get shipping method html
     *
     * @return string
     */
    protected function _getShippingMethodHtml()
    {
        return $this->getUpdateCheckoutLayout()->getBlock('checkout.shipping.method')->toHtml();
    }

    /**
     * Get payment method html
     *
     * @return string
     */
    protected function _getPaymentMethodHtml()
    {
        return $this->getUpdateCheckoutLayout()->getBlock('checkout.payment.method')->toHtml();
    }

    /**
     * Get coupon code html
     *
     * @return string
     */
    protected function _getCouponDiscountHtml()
    {
        $layout = $this->getUpdateCheckoutLayout();
        if (!$block = $layout->getBlock('checkout.coupon')) {
            $block = $layout->getBlock('checkout_cart_coupon_normal'); // @see layout/firecheckout/rewardpoints.xml
        }
        return $block ? $block->toHtml() : '';
    }

    /**
     * Get giftcard code html
     *
     * @return string
     */
    protected function _getGiftcardHtml()
    {
        return $this->getUpdateCheckoutLayout()->getBlock('checkout.giftcard')->toHtml();
    }

    /**
     * Get j2t Rewardpoints block html
     *
     * @return string
     */
    protected function _getRewardpointsHtml()
    {
        $block = $this->getUpdateCheckoutLayout()->getBlock('checkout_cart_coupon_normal');
        if (!$block) {
            return '';
        }
        return $block->toHtml();
    }

    /**
     * Get order review html
     *
     * @return string
     */
    protected function _getReviewHtml()
    {
        return $this->getUpdateCheckoutLayout()->getBlock('checkout.review')->toHtml();
    }

    /**
     * Get order review html
     *
     * @return string
     */
    protected function _getAgreementsHtml()
    {
        return $this->getUpdateCheckoutLayout()->getBlock('checkout.onepage.agreements')->toHtml();
    }

    /**
     * @return TM_FireCheckout_Model_Type_Standard
     */
    public function getOnepage()
    {
        return $this->getCheckout();
    }

    /**
     * @return TM_FireCheckout_Model_Type_Standard
     */
    public function getCheckout()
    {
        return Mage::getSingleton('firecheckout/type_standard');
    }

    public function indexAction()
    {
        if (!Mage::getStoreConfig('firecheckout/mobile/enabled') && $this->_isMobile()) {
            $this->_redirect('checkout/onepage');
            return;
        }

        /**
         * @var Mage_Sales_Model_Quote
         */
        $quote = $this->getCheckout()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->_redirect('checkout/cart');
            return;
        }
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message') ?
                Mage::getStoreConfig('sales/minimum_order/error_message') :
                Mage::helper('checkout')->__('Subtotal must exceed minimum order amount');

            Mage::getSingleton('checkout/session')->addError($error);
            $this->_redirect('checkout/cart');
            return;
        }
        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(
            Mage::helper('firecheckout')->getFirecheckoutUrl()
        );

        // sage server fix
//        $sagepayModel = Mage::getModel('sagepayserver2/sagePayServer_session');
//        if ($sagepayModel) {
//            $sessId = Mage::getModel('core/session')->getSessionId();
//            $_s = $sagepayModel->loadBySessionId($sessId);
//            if ($_s->getId()) {
//                $_s->delete();
//            }
//        }
        // sage server fix

        // DHLParcel_Shipping
        $quote->setData('dhlparcel_shipping_options', '');

        $this->getCheckout()->applyDefaults()->initCheckout();
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');

        $metaTitle = Mage::getStoreConfig('firecheckout/general/title');
        if (Mage::getStoreConfig('firecheckout/general/use_different_meta_title')) {
            $metaTitle = Mage::getStoreConfig('firecheckout/general/meta_title');
        }
        $this->getLayout()->getBlock('head')->setTitle($metaTitle);
        $this->_addBodyClasses();
        $this->renderLayout();
    }

    protected function _addBodyClasses()
    {
        $root = $this->getLayout()->getBlock('root');
        if ($root) {
            $root->addBodyClass(
                'firecheckout-' . Mage::helper('firecheckout/layout')->getLayout()
            );
            if (Mage::getStoreConfig('firecheckout/general/empty_layout')) {
                $root->addBodyClass('firecheckout-empty-layout');
            }

            // form design
            $mode = Mage::getStoreConfig('firecheckout/design/form_fields_mode');
            if (in_array($mode, array('default', 'wide')) &&
                Mage::getStoreConfigFlag('firecheckout/design/hide_field_labels')) {

                $root->addBodyClass('fc-form-compact');
            }
            $root->addBodyClass(
                'fc-form-' . Mage::getStoreConfig('firecheckout/design/form_fields_mode')
            );

            // additional classes
            $customer = $this->getCheckout()->getCustomerSession()->getCustomer();
            if ($customer) {
                $root->addBodyClass('fc-customer');
                if ($customer->getAddresses()) {
                    $root->addBodyClass('fc-has-address');
                }
            } else {
                $root->addBodyClass('fc-guest');
            }

            // rtl
            if (Mage::helper('firecheckout')->isRtl()) {
                $root->addBodyClass('fc-rtl');
            }
        }
    }

    public function addActionLayoutHandles()
    {
        parent::addActionLayoutHandles();
        $update = $this->getLayout()->getUpdate();
        $update->addHandle(
            'firecheckout_' . Mage::helper('firecheckout/layout')->getLayout()
        );
        if (Mage::getStoreConfig('firecheckout/general/empty_layout')) {
            $update->addHandle(
                'firecheckout_empty_layout'
            );
        }

        // Third-party module compatibility.
        // Modules that are added prior to firecheckout layout, can't use
        // firecheckout_index_index to reference our blocks, so we add
        // additional firecheckout_index_index_custom handle for them
        $update->addHandle(strtolower($this->getFullActionName()) . '_custom');
        if ($this->_useUpdateCheckoutLayout) {
            $update->addHandle('firecheckout_index_updatecheckout');
            $update->addHandle('firecheckout_index_updatecheckout_custom');
        }

        return $this;
    }

    public function saveRewardpointsAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if (!$this->getRequest()->isPost()) {
            return;
        }

        /**
         * @var Mage_Sales_Model_Quote
         */
        $quote    = $this->getCheckout()->getQuote();
        $oldTotal = $quote->getBaseGrandTotal();
        $sections = array();
        $ajaxHelper = Mage::helper('firecheckout/ajax');
        $cancelRewardPoints = $this->getRequest()->getPost('cancel_rewardpoints', false);
        if (!$cancelRewardPoints) {
            $session = Mage::getSingleton('core/session');
            $points_value = $this->getRequest()->getPost('points_to_be_used', 0);
            if (Mage::getStoreConfig('rewardpoints/default/max_point_used_order', Mage::app()->getStore()->getId())){
                if ((int)Mage::getStoreConfig('rewardpoints/default/max_point_used_order', Mage::app()->getStore()->getId()) < $points_value){
                    $points_max = (int)Mage::getStoreConfig('rewardpoints/default/max_point_used_order', Mage::app()->getStore()->getId());
                    $session->addError($this->__('You tried to use %s loyalty points, but you can use a maximum of %s points per shopping cart.', $points_value, $points_max));
                    $points_value = $points_max;
                }
            }
            $quote_id = Mage::helper('checkout/cart')->getCart()->getQuote()->getId();

            Mage::getSingleton('rewardpoints/session')->setProductChecked(0);
            Mage::getSingleton('rewardpoints/session')->setShippingChecked(0);
            Mage::helper('rewardpoints/event')->setCreditPoints($points_value);
            Mage::helper('checkout/cart')->getCart()->getQuote()
                ->setRewardpointsQuantity($points_value)
                // ->save()
                ;
        } else {
            Mage::getSingleton('rewardpoints/session')->setProductChecked(0);
            Mage::helper('rewardpoints/event')->setCreditPoints(0);
            Mage::helper('checkout/cart')->getCart()->getQuote()
                ->setRewardpointsQuantity(NULL)
                ->setRewardpointsDescription(NULL)
                ->setBaseRewardpoints(NULL)
                ->setRewardpoints(NULL)
                // ->save()
                ;
        }

        $quote->collectTotals();
        $sections[] = 'review';
        $sections[] = 'coupon-discount';

        if ($ajaxHelper->getIsShippingMethodDependsOn('total')) {
            $sections[] = 'shipping-method';
            $this->recollectShippingRatesAndTotals(false);
        }

        if ($ajaxHelper->getIsPaymentMethodDependsOn('total')
            || $quote->getBaseGrandTotal() <= 0 || $oldTotal <= 0) {

            $sections[] = 'payment-method';
            $this->applyPaymentMethodAndCollectTotals($ajaxHelper->getIsTotalDependsOn('payment-method'));
        }

        $quote->save();
        $result['update_section'] = $this->_renderSections($sections);
        $this->sendJsonResponse($result);
    }

    public function awpointsAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if (!$this->getRequest()->isPost()) {
            return;
        }

        /**
         * @var Mage_Sales_Model_Quote
         */
        $quote    = $this->getCheckout()->getQuote();
        $oldTotal = $quote->getBaseGrandTotal();
        $sections = array();
        $ajaxHelper = Mage::helper('firecheckout/ajax');
        $session = Mage::getSingleton('checkout/session');
        $payment = $this->getRequest()->getPost('payment');
        $session->setData('use_points', isset($payment['use_points']));
        $session->setData('points_amount', isset($payment['points_amount']) ? $payment['points_amount'] : '');

        $quote->collectTotals();
        $sections[] = 'review';
        if ($ajaxHelper->getIsShippingMethodDependsOn('total')) {
            $sections[] = 'shipping-method';
            $this->recollectShippingRatesAndTotals(false);
        }

        if ($ajaxHelper->getIsPaymentMethodDependsOn('total')
            || $quote->getBaseGrandTotal() <= 0 || $oldTotal <= 0) {

            $sections[] = 'payment-method';
            $this->applyPaymentMethodAndCollectTotals($ajaxHelper->getIsTotalDependsOn('payment-method'));
        }

        $quote->save();
        $result['update_section'] = $this->_renderSections($sections);
        $this->sendJsonResponse($result);
    }

    public function saveGiftcardAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if (!$this->getRequest()->isPost()) {
            return;
        }

        $quote          = $this->getOnepage()->getQuote();
        $oldTotal       = $quote->getBaseGrandTotal();
        $sections       = array();
        $ajaxHelper     = Mage::helper('firecheckout/ajax');
        $removeGiftcard = $this->getRequest()->getPost('remove_giftcard', false);
        $giftcardCode   = $this->getRequest()->getPost('giftcard_code');
        if (!$giftcardCode) {
            return;
        }

        $sections[] = 'giftcard';
        if (!$removeGiftcard) {
            try {
                Mage::getModel('enterprise_giftcardaccount/giftcardaccount')
                    ->loadByCode($giftcardCode)
                    ->addToCart();
                Mage::getSingleton('checkout/session')->addSuccess(
                    $this->__('Gift Card "%s" was added.', Mage::helper('core')->htmlEscape($giftcardCode))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::dispatchEvent('enterprise_giftcardaccount_add', array('status' => 'fail', 'code' => $giftcardCode));
                Mage::getSingleton('checkout/session')->addError(
                    $e->getMessage()
                );
            } catch (Exception $e) {
                Mage::getSingleton('checkout/session')->addException($e, $this->__('Cannot apply gift card.'));
            }
        } else {
            try {
                Mage::getModel('enterprise_giftcardaccount/giftcardaccount')
                    ->loadByCode($giftcardCode)
                    ->removeFromCart();
            } catch (Mage_Core_Exception $e) {
            } catch (Exception $e) {
            }
        }

        $quote->collectTotals();
        $sections[] = 'review';

        if ($ajaxHelper->getIsShippingMethodDependsOn('total')) {
            $sections[] = 'shipping-method';
            $this->recollectShippingRatesAndTotals(false);
        }

        if ($ajaxHelper->getIsPaymentMethodDependsOn('total')
            || $quote->getBaseGrandTotal() <= 0 || $oldTotal <= 0) {

            $sections[] = 'payment-method';
            $this->applyPaymentMethodAndCollectTotals($ajaxHelper->getIsTotalDependsOn('payment-method'));
        }

        $quote->save();
        $result['update_section'] = $this->_renderSections($sections);
        $this->sendJsonResponse($result);
    }

    public function savePaymentDataAction()
    {
        $sections    = array();
        $paymentData = $this->getRequest()->getPost('payment', array());
        $quote       = $this->getOnepage()->getQuote();

        if ($this->getRequest()->getPost('remove_storecredit', false)) {
            if ($quote->getUseCustomerBalance()) {
                $quote->setUseCustomerBalance(false);
            }
        } elseif ($this->getRequest()->getPost('remove_rewardpoints', false)) {
            if ($quote->getUseRewardPoints()) {
                $quote->setUseRewardPoints(false);
            }
        } elseif (!empty($paymentData['use_customer_balance'])
            || !empty($paymentData['use_reward_points'])) {

            try {
                $this->getCheckout()->savePayment($paymentData);
            } catch (Exception $e) {
                // skip this message. form can be filled with invalid data at this step
            }
        }

        $quote->collectTotals()->save();
        $sections['review']         = 'review';
        $sections['payment-method'] = 'payment-method';
        $result['update_section']   = $this->_renderSections($sections);
        $this->sendJsonResponse($result);
    }

    /**
     * Safely Validate Form Key:
     *  - Checks for method_exists before run
     *  - Fills response data by default to show error message
     *
     * @return bool
     */
    protected function _ajaxValidateFormKeySafely($checkSettings = true, $updateResponse = true)
    {
        if (version_compare(Mage::helper('firecheckout')->getMagentoVersion(), '1.8.0.0') < 0) {
            return true;
        }
        if ($checkSettings) {
            if (!method_exists($this, 'isFormkeyValidationOnCheckoutEnabled')
                || !$this->isFormkeyValidationOnCheckoutEnabled()) {

                return true;
            }
        }

        $validated = true;
        if (method_exists($this, '_validateFormKey')) {
            $validated = $this->_validateFormKey();
        }
        if (!$validated && $updateResponse) {
            $this->sendJsonResponse(array(
                'success' => false,
                'error'   => true,
                'error_messages' => $this->__('Invalid Form Key. Please refresh the page.')
            ));
        }
        return $validated;
    }

    public function saveBillingAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if (!$this->getRequest()->isPost()) {
            return;
        }
        if (!$this->_ajaxValidateFormKeySafely()) {
            return;
        }

        $data = $this->getRequest()->getPost('billing', array());
        $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

        /* Wyomind_Pickupatstore */
        Mage::getSingleton('core/session')->setPickupatstore(false);
        if (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 2) {
            Mage::getSingleton('core/session')->setPickupatstore(true);
            $data['use_for_shipping'] = 1;
        }
        /* Wyomind_Pickupatstore */

        $result = $this->getOnepage()->saveBilling(
            $data, $customerAddressId, $this->getRequest()->getPost('force_validation', false)
        );

        if (isset($result['error'])) {
            return $this->sendJsonResponse($result);
        }

        /**
         * @var Mage_Sales_Model_Quote
         */
        $quote           = $this->getOnepage()->getQuote();
        $shippingAddress = $quote->getShippingAddress();
        $sections        = array();
        $ajaxHelper      = Mage::helper('firecheckout/ajax');
        if (!$quote->isVirtual()
            && isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1
            && $ajaxHelper->getIsShippingMethodDependsOn('shipping')) {

            $sections['shipping-method'] = 'shipping-method';

            // recollect avaialable shipping methods
            $oldMethod = $shippingAddress->getShippingMethod();
            $shippingAddress->collectTotals()->collectShippingRates()->save();
            // apply or cancel shipping method
            $this->getOnepage()->applyShippingMethod();

            if (($ajaxHelper->getIsTotalDependsOn('shipping-method')
                    && $oldMethod != $shippingAddress->getShippingMethod())
                || $ajaxHelper->getIsTotalDependsOn(array('shipping', 'billing'))) {

                $sections['review'] = 'review';
                // shipping method may affect the total in both sides (discount on using shipping address)
                $quote->collectTotals();

                if ($ajaxHelper->getIsShippingMethodDependsOn('total')) {
                    $this->recollectShippingRatesAndTotals(false, false);
                }

                if ($ajaxHelper->getIsPaymentMethodDependsOn('total')) {
                    $sections['payment-method'] = 'payment-method';
                    $this->applyPaymentMethodAndCollectTotals($ajaxHelper->getIsTotalDependsOn('payment-method'));
                }
            }
        } elseif ($ajaxHelper->getIsTotalDependsOn(array('shipping', 'billing'))) {
            $sections['review'] = 'review';

            // shipping method may affect the total in both sides (discount on using shipping address)
            $quote->collectTotals();

            if (!$quote->isVirtual() && $ajaxHelper->getIsShippingMethodDependsOn('total')) {
                $sections[] = 'shipping-method';
                $this->recollectShippingRatesAndTotals(false);
            }

            if ($ajaxHelper->getIsPaymentMethodDependsOn('total')) {
                $sections[] = 'payment-method';
                $this->applyPaymentMethodAndCollectTotals($ajaxHelper->getIsTotalDependsOn('payment-method'));
            }
        }

        if (!isset($sections['payment-method']) && $ajaxHelper->getIsPaymentMethodDependsOn('billing')) {
            $sections['payment-method'] = 'payment-method';
            $collectTotals = $ajaxHelper->getIsTotalDependsOn('payment-method');
            if ($collectTotals) {
                $sections['review'] = 'review';
            }
            $this->applyPaymentMethodAndCollectTotals($collectTotals);
        }

        if ($ajaxHelper->getIsDiscountDependsOn(array('billing', 'shipping'))) {
            $sections[] = 'coupon-discount';
        }

        $quote->save();

        if ($ajaxHelper->getIsAgreementsDependsOn(array('billing', 'shipping'))) {
            $sections['agreements'] = 'agreements';
        }

        $result['update_section'] = $this->_renderSections($sections);
        $this->sendJsonResponse($result);
    }

    public function saveShippingAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if (!$this->getRequest()->isPost()) {
            return;
        }
        if (!$this->_ajaxValidateFormKeySafely()) {
            return;
        }

        $sections = array();
        $data = $this->getRequest()->getPost('shipping', array());
        $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);

        /* Wyomind_Pickupatstore */
        Mage::getSingleton('core/session')->setPickupatstore(false);
        /* Wyomind_Pickupatstore */

        $result = $this->getOnepage()->saveShipping(
            $data, $customerAddressId, $this->getRequest()->getPost('force_validation', false)
        );

        if (isset($result['error'])) {
            return $this->sendJsonResponse($result);
        }
        /**
         * @var Mage_Sales_Model_Quote
         */
        $quote = $this->getOnepage()->getQuote();
        $ajaxHelper = Mage::helper('firecheckout/ajax');
        if ($ajaxHelper->getIsShippingMethodDependsOn('shipping')) {
            $sections[] = 'shipping-method';

            // recollect avaialable shipping methods
            $quote->getShippingAddress()->collectTotals()->collectShippingRates()->save();
            // apply or cancel shipping method
            $this->getOnepage()->applyShippingMethod();

            if ($ajaxHelper->getIsTotalDependsOn('shipping-method') // @todo: && method was changed
                || $ajaxHelper->getIsTotalDependsOn('shipping')) {

                $sections[] = 'review';

                // shipping method may affect the total in both sides (discount on using shipping address)
                $quote->collectTotals();

                if ($ajaxHelper->getIsShippingMethodDependsOn('total')) {
                    $this->recollectShippingRatesAndTotals(false);
                }

                if ($ajaxHelper->getIsPaymentMethodDependsOn('total')) {
                    $sections[] = 'payment-method';
                    $this->applyPaymentMethodAndCollectTotals($ajaxHelper->getIsTotalDependsOn('payment-method'));
                }
            }
            if ($ajaxHelper->getIsDiscountDependsOn('shipping')) {
                $sections[] = 'coupon-discount';
            }
            $quote->save();
        } else if ($ajaxHelper->getIsTotalDependsOn('shipping')) {
            $sections[] = 'review';

            // shipping method may affect the total in both sides (discount on using shipping address)
            $quote->collectTotals();

            if ($ajaxHelper->getIsShippingMethodDependsOn('total')) {
                $sections[] = 'shipping-method';
                $this->recollectShippingRatesAndTotals(false);
            }

            if ($ajaxHelper->getIsPaymentMethodDependsOn('total')) {
                $sections[] = 'payment-method';
                $this->applyPaymentMethodAndCollectTotals($ajaxHelper->getIsTotalDependsOn('payment-method'));
            }
            if ($ajaxHelper->getIsDiscountDependsOn('shipping')) {
                $sections[] = 'coupon-discount';
            }
            $quote->save();
        }

        if ($ajaxHelper->getIsAgreementsDependsOn('shipping')) {
            $sections['agreements'] = 'agreements';
        }

        $result['update_section'] = $this->_renderSections($sections);
        $this->sendJsonResponse($result);
    }

    public function saveShippingMethodAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if (!$this->_ajaxValidateFormKeySafely()) {
            return;
        }

        $this->_saveAdvoxInpost();

        $method = $this->getRequest()->getPost('shipping_method', false);
        if ($this->getRequest()->getPost('remove-shipping', false)) {
            $method = false;
        }
        $quote           = $this->getOnepage()->getQuote();
        $shippingAddress = $quote->getShippingAddress();
        $oldMethod       = $shippingAddress->getShippingMethod();

        $this->getCheckout()->applyShippingMethod($method);
        // EE giftwrap integration
        Mage::dispatchEvent(
            'checkout_controller_onepage_save_shipping_method',
            array(
                'request' => $this->getRequest(),
                'quote'   => $this->getOnepage()->getQuote()
            )
        );
        if (Mage::helper('firecheckout')->canUseMageWorxMultifees()) {
            $shippingAddress->save();
        }
        $newMethod = $shippingAddress->getShippingMethod();

        $sections = array();
        $ajaxHelper = Mage::helper('firecheckout/ajax');
        if ($ajaxHelper->getIsTotalDependsOn('shipping-method')) {
            $sections[] = 'review';
            /**
             * @var Mage_Sales_Model_Quote
             */
            $quote->collectTotals();

            if ($ajaxHelper->getIsShippingMethodDependsOn('total')
                || (!$newMethod && $oldMethod != $newMethod)) { // reset method fix

                $sections[] = 'shipping-method';
                $this->recollectShippingRatesAndTotals(false);
            }

            if ($ajaxHelper->getIsPaymentMethodDependsOn('total')) {
                $sections[] = 'payment-method';
                $this->applyPaymentMethodAndCollectTotals($ajaxHelper->getIsTotalDependsOn('payment-method'));
            }

            if ($ajaxHelper->getIsDiscountDependsOn('shipping-method')) {
                $sections[] = 'coupon-discount';
            }

            $quote->save();
        }

        if ($ajaxHelper->getIsAgreementsDependsOn('shipping-method')) {
            $sections['agreements'] = 'agreements';
        }

        $result['update_section'] = $this->_renderSections($sections);
        $this->sendJsonResponse($result);
    }

    public function savePaymentAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if (!$this->getRequest()->isPost()) {
            $this->_ajaxRedirectResponse();
            return;
        }
        if (!$this->_ajaxValidateFormKeySafely()) {
            return;
        }

        $data = $this->getRequest()->getPost('payment', array());
        if (isset($data['remove'])) {
            $data['method'] = false;
        }

        if (Mage::helper('core')->isModuleOutputEnabled('AW_Storecredit')
            || Mage::helper('core')->isModuleOutputEnabled('Amasty_StoreCredit')
            || Mage::helper('core')->isModuleOutputEnabled('Klarna_Payments')) {

            $dataObject = new Varien_Object($data);
            Mage::dispatchEvent(
                'sales_quote_payment_import_data_before',
                array(
                    'payment' => $this->getOnepage()->getQuote()->getPayment(),
                    'input'   => $dataObject,
                )
            );
            $data['method'] = $dataObject->getMethod();
        }

        if ($this->getRequest()->getPost('force_validation', false)) {
            try {
                $result = $this->getCheckout()->savePayment($data);
            } catch (Exception $e) {
                $result = array();
                $result['error'] = true;
                $result['message'] = $e->getMessage();
                return $this->sendJsonResponse($result);
            }
        } else {
            $this->getCheckout()->applyPaymentMethod(isset($data['method']) ? $data['method'] : null);
        }

        $sections = array();
        $ajaxHelper = Mage::helper('firecheckout/ajax');
        if ($ajaxHelper->getIsTotalDependsOn('payment-method')
            || Mage::helper('firecheckout')->canUseMageWorxCustomerCredit()) {

            $sections[] = 'review';

            if (!empty($data['use_internal_credit']) ||
                (isset($data['method']) && $data['method'] === 'customercredit')
            ) {
                Mage::getSingleton('checkout/session')->setUseInternalCredit(true);
            } else {
                Mage::getModel('checkout/session')->setUseInternalCredit(false);
            }

            /**
             * @var Mage_Sales_Model_Quote
             */
            $quote = $this->getOnepage()->getQuote();
            $quote->collectTotals();

            if ($ajaxHelper->getIsShippingMethodDependsOn('total')) {
                $sections[] = 'shipping-method';
                $this->recollectShippingRatesAndTotals(false);
            }

            if ($ajaxHelper->getIsPaymentMethodDependsOn('total')) {
                $sections[] = 'payment-method';
                if (!isset($data['remove'])) { // if not canceled method
                    $this->applyPaymentMethodAndCollectTotals(
                        $ajaxHelper->getIsTotalDependsOn('payment-method'),
                        isset($data['method']) ? $data['method'] : null
                    );
                }
            }

            if ($ajaxHelper->getIsDiscountDependsOn('payment-method')) {
                $sections[] = 'coupon-discount';
            }

            $quote->save();
        }

        if ($ajaxHelper->getIsAgreementsDependsOn('payment-method')) {
            $sections['agreements'] = 'agreements';
        }

        $result['update_section'] = $this->_renderSections($sections);
        $this->sendJsonResponse($result);
    }

    public function saveCouponAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if (!$this->getRequest()->isPost()) {
            $this->_ajaxRedirectResponse();
            return;
        }

        /**
         * @var Mage_Sales_Model_Quote
         */
        $quote         = $this->getCheckout()->getQuote();
        $oldTotal      = $quote->getBaseGrandTotal();
        $sections      = array();
        $data          = $this->getRequest()->getPost('coupon', array());
        $couponChanged = false;

        if ($this->getRequest()->getPost('amcoupon_code_cancel', false)) {
            $codeToCancel = $this->getRequest()->getParam('amcoupon_code_cancel');
            if (method_exists($quote, '_getAppliedCoupons')) {
                $appliedCoupons = $quote->_getAppliedCoupons();
            } else {
                $appliedCoupons = $quote->getAppliedCoupons();
            }
            $session = Mage::getSingleton('checkout/session');
            foreach ($appliedCoupons as $i => $coupon) {
                if ($coupon == $codeToCancel) {
                    unset($appliedCoupons[$i]);
                    try {
                        if ($quote->setCouponCode(implode(',', $appliedCoupons))->save()) {
                            $session->addSuccess($this->__('Coupon code %s was canceled.', $codeToCancel));
                        }
                    } catch (Mage_Core_Exception $e) {
                        $session->addError($e->getMessage());
                    } catch (Exception $e) {
                        $session->addError($this->__('Cannot canel the coupon code.'));
                    }
                }
            }
            $sections[] = 'coupon-discount';
        } elseif ($this->getRequest()->getPost('remove_ugiftcert', false)) {
            $gc  = $this->getRequest()->getPost('gc');
            $gcs = $quote->getGiftcertCode();
            if ($gc && $gcs && strpos($gcs, $gc) !== false) {
                $gcsArr = array();
                foreach (explode(',', $gcs) as $gc1) {
                    if (trim($gc1) !== $gc) {
                        $gcsArr[] = $gc1;
                    }
                }
                $quote->setGiftcertCode(join(',', $gcsArr));
                $sections[] = 'giftcard';
            }
        } elseif ($code = trim($this->getRequest()->getParam('cert_code'))) {
            $session = Mage::getSingleton('checkout/session');
            $hlp = Mage::helper('ugiftcert');
            try {
                if ($hlp->addCertificate($code, $quote)) {
                    $session->addSuccess(
                        Mage::helper('ugiftcert')->__("Gift certificate '%s' was applied to your order.", $code)
                    );
                } else {
                    $session->addError($hlp->__("'%s' is not valid certificate code.", $code));
                }
            } catch (Unirgy_Giftcert_Exception_Coupon $gce) {
                $session->addError($gce->getMessage());
            } catch (Exception $e) {
                $session->addError($hlp->__("Gift certificate '%s' could not be applied to your order.", $code));
                $session->addError($e->getMessage());
            }
            $sections[] = 'giftcard';
        } else {
            if (!empty($data['remove'])) {
                $data['code'] = '';
            }
            $oldCouponCode = $quote->getCouponCode();
            if ($oldCouponCode != $data['code']) {
                try {
                    $quote->setCouponCode(
                        strlen($data['code']) ? $data['code'] : ''
                    );
                    if ($data['code']) {
                        $couponChanged = true;
                    } else {
                        Mage::getSingleton('checkout/session')->addSuccess($this->__('Coupon code was canceled.'));
                    }
                } catch (Mage_Core_Exception $e) {
                    Mage::getSingleton('checkout/session')->addError($e->getMessage());
                } catch (Exception $e) {
                    Mage::getSingleton('checkout/session')->addError($this->__('Cannot apply the coupon code.'));
                }
            }
            $sections[] = 'coupon-discount';
        }

        // coupon may affect the total in both sides (apply or cancel)
        $quote->collectTotals(); // coupon validation is inside collectTotals method

        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message') ?
                Mage::getStoreConfig('sales/minimum_order/error_message') :
                Mage::helper('checkout')->__('Subtotal must exceed minimum order amount');

            Mage::getSingleton('checkout/session')->addError($error);

            $minimumAmount = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())
                ->toCurrency(Mage::getStoreConfig('sales/minimum_order/amount'));
            $warning = Mage::getStoreConfig('sales/minimum_order/description')
                ? Mage::getStoreConfig('sales/minimum_order/description')
                : Mage::helper('checkout')->__('Minimum order amount is %s', $minimumAmount);

            Mage::getSingleton('checkout/session')->addNotice($warning);

            $couponChanged = false;
            $quote->setCouponCode('');
            $quote->setTotalsCollectedFlag(false)->collectTotals();
        }

        $sections[] = 'review';
        $ajaxHelper = Mage::helper('firecheckout/ajax');

        if ($ajaxHelper->getIsShippingMethodDependsOn(array('total', 'coupon'))) {
            $sections[] = 'shipping-method';
            $this->recollectShippingRatesAndTotals(true, true);
        }

        if ($ajaxHelper->getIsPaymentMethodDependsOn('total')
            || $quote->getBaseGrandTotal() <= 0 || $oldTotal <= 0) { // hide and show payment methods

            $sections[] = 'payment-method';
            $this->applyPaymentMethodAndCollectTotals($ajaxHelper->getIsTotalDependsOn('payment-method'));
        }
        $quote->save();

        if ($couponChanged) {
            $coupons = $quote->getCouponCode();
            if ($quote instanceof Amasty_Coupons_Model_Sales_Quote) {
                $coupons = explode(',', $coupons);
            } else {
                $coupons = array($coupons);
            }
            if (in_array($data['code'], $coupons)) {
                Mage::getSingleton('checkout/session')->addSuccess(
                    $this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($data['code']))
                );
            } else {
                Mage::getSingleton('checkout/session')->addError(
                    $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($data['code']))
                );
            }
        }

        $result['update_section'] = $this->_renderSections($sections);
        $this->sendJsonResponse($result);
    }

    protected function _validateUnirgyGiftCertificateConditions(Unirgy_Giftcert_Model_Cert $cert, $quote)
    {
        if($cert->getConditions()) {
            if($quote->isVirtual()) {
                $address = $quote->getBillingAddress();
            } else {
                $address = $quote->getShippingAddress();
            }
            return $cert->getConditions()->validate($address);
        }
        return false;
    }

    // Copy of the updatePost action of Magento CartController
    protected function _updateCart($cartData)
    {
        try {
            /**
             * @var Mage_Checkout_Model_Session
             */
            $session = $this->getCheckout()->getCheckout();
            if (is_array($cartData)) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                    }
                }
                /**
                 * @var Mage_Checkout_Model_Cart
                 */
                $cart = Mage::getSingleton('checkout/cart');
                if (!$cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                    $cart->getQuote()->setCustomerId(null);
                }

                $oldItems = $cart->getItems();
                $oldCartData = array();
                foreach ($oldItems as $item) {
                    $oldCartData[$item->getId()]['qty'] = $item->getQty();
                }

                $cartData = $cart->suggestItemsQty($cartData);
                $cart->updateItems($cartData)
                    ->save();

                if (!$this->getCheckout()->getQuote()->validateMinimumAmount()) {
                    $this->getCheckout()->getQuote()->setTotalsCollectedFlag(false);

                    // $oldCartData = $cart->suggestItemsQty($oldCartData);
                    $cart->updateItems($oldCartData)
                        ->save();

                    $error = Mage::getStoreConfig('sales/minimum_order/error_message') ?
                        Mage::getStoreConfig('sales/minimum_order/error_message') :
                        Mage::helper('checkout')->__('Subtotal must exceed minimum order amount');
                    $session->addError($error);

                    $minimumAmount = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())
                        ->toCurrency(Mage::getStoreConfig('sales/minimum_order/amount'));

                    $warning = Mage::getStoreConfig('sales/minimum_order/description')
                        ? Mage::getStoreConfig('sales/minimum_order/description')
                        : Mage::helper('checkout')->__('Minimum order amount is %s', $minimumAmount);

                    $session->addNotice($warning);
                }
            }
            // false to prevent _expireAjax to return redirect
            $session->setCartWasUpdated(false);
            return true;
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
        } catch (Exception $e) {
            $session->addException($e, $this->__('Cannot update shopping cart.'));
            Mage::logException($e);
        }
        return false;
    }

    public function saveCartAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if (!$this->getRequest()->isPost()) {
            $this->_ajaxRedirectResponse();
            return;
        }
        if (!$this->_ajaxValidateFormKeySafely()) {
            return;
        }

        /**
         * @var Mage_Sales_Model_Quote
         */
        $quote    = $this->getOnepage()->getQuote();
        $sections = array('review');
        $sections[] = 'coupon-discount';
        $ajaxHelper = Mage::helper('firecheckout/ajax');

        if (!$this->_updateCart($this->getRequest()->getParam('updated_cart'))
            || $quote->getHasError()) {

            if ($quote->getHasError()) {
                foreach ($quote->getErrors() as $message) {
                    $this->getCheckout()->getCheckout()->addError($message->getText());
                }
            }

            // if unavailable product qty was received - revert to the original values
            $this->_updateCart($this->getRequest()->getParam('updated_cart_safe'));
        }

        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message') ?
                Mage::getStoreConfig('sales/minimum_order/error_message') :
                Mage::helper('checkout')->__('Subtotal must exceed minimum order amount');

            Mage::getSingleton('checkout/session')->addError($error);
            return $this->sendJsonResponse(array(
                'redirect' => Mage::getUrl('checkout/cart', array('_secure'=>true)),
                'success'  => true
            ));
        }

        if ($this->_expireAjax()) { // if all products were removed from the cart
            return;
        }

        $quote->collectTotals();

        // discount depends on cart weight fix
        // to recollect discount rules need to clear previous discount
        // descriptions and mark address as modified
        // see _canProcessRule in Mage_SalesRule_Model_Validator
        $quote->getShippingAddress()->setDiscountDescriptionArray(array())->isObjectNew(true);

        // @todo on cart contents?
        if ($ajaxHelper->getIsShippingMethodDependsOn(array('cart', 'total'))) {
            $sections[] = 'shipping-method';
            $this->recollectShippingRatesAndTotals(true);
        }

        if ($ajaxHelper->getIsPaymentMethodDependsOn(array('cart', 'total'))) {
            $sections[] = 'payment-method';
            $this->applyPaymentMethodAndCollectTotals($ajaxHelper->getIsTotalDependsOn('payment-method'));
        }
        $quote->save();

        $result['update_section'] = $this->_renderSections($sections);
        $this->sendJsonResponse($result);
    }

    public function saveOrderAction()
    {
        if (!$this->_ajaxValidateFormKeySafely()) {
            return;
        }

        if ($this->_expireAjax()) {
            return;
        }

        // sage server fix
//        $sagepayModel = Mage::getModel('sagepayserver2/sagePayServer_session');
//        if ($sagepayModel) {
//            $sessId = Mage::getModel('core/session')->getSessionId();
//            $_s = $sagepayModel->loadBySessionId($sessId);
//            if ($_s->getId()) {
//                $_s->delete();
//            }
//        }
        // sage server fix

        $result = array();
        /* @var TM_FireCheckout_Model_Type_Standard */
        $checkout = $this->getCheckout();
        /* @var Mage_Sales_Model_Quote */
        $quote = $checkout->getQuote();

        try {
            $this->_saveAdvoxInpost();
            $checkout->applyShippingMethod($this->getRequest()->getPost('shipping_method', false));

            $deliveryDate = $this->getRequest()->getPost('delivery_date');
            if ($deliveryDate) {
                $result = $checkout->saveDeliveryDate($deliveryDate);
                if (is_array($result)) {
                    $result['success'] = false;
                    $result['error']   = true;
                    $result['error_messages'] = $result['message'];
                    $result['onecolumn_step'] = 'step-shipping-payment-method';
                    $this->sendJsonResponse($result);
                    return;
                }
            }

            $quote->setFirecheckoutCustomerComment($this->getRequest()->getPost('order-comment'));

            $billing = $this->getRequest()->getPost('billing', array());
            $result = $checkout->saveBilling(
                $billing,
                $this->getRequest()->getPost('billing_address_id', false)
            );
            if ($result) {
                $result['success'] = false;
                $result['error']   = true;
                if ($result['message'] === $checkout->getCustomerEmailExistsMessage()) {
                    unset($result['message']);
                    $result['body'] = array(
                        'id'      => 'emailexists',
                        'modal'   => 1,
                        'window'  => array(
                            'triggers' => array(),
                            'destroy'  => 1,
                            'size'     => array(
                                'maxWidth' => 400
                            )
                        ),
                        'content' => $this->getLayout()->createBlock('core/template')
                            ->setTemplate('tm/firecheckout/emailexists.phtml')
                            ->toHtml()
                    );
                } else {
                    $result['error_messages'] = $result['message'];
                    $result['onecolumn_step'] = 'step-address';
                }

                $this->sendJsonResponse($result);
                return;
            }

            if ((!isset($billing['use_for_shipping']) || !$billing['use_for_shipping'])
                && !$quote->isVirtual()) {

                $result = $checkout->saveShipping(
                    $this->getRequest()->getPost('shipping', array()),
                    $this->getRequest()->getPost('shipping_address_id', false)
                );
                if ($result) {
                    $result['success'] = false;
                    $result['error']   = true;
                    $result['error_messages'] = $result['message'];
                    $result['onecolumn_step'] = 'step-address';
                    $this->sendJsonResponse($result);
                    return;
                }
            }

            if ('relaypoint_relaypoint' == $this->getRequest()->getPost('shipping_method', false)) {
                $this->relaypointChangeAddress();
            } elseif ('storepickup_storepickup' == $this->getRequest()->getPost('shipping_method', false)) {
                // Magestore_Storepickup
                $storepickup = Mage::getSingleton('checkout/session')->getData('storepickup_session');
                if ($storepickup && isset($storepickup['store_id']) && $storepickup['store_id']) {
                    $this->storepickupChangeAddress();
                }
            }

            $paymentData = $this->getRequest()->getPost('payment', array());
            $checkoutHelper = Mage::helper('checkout');
            $checkoutHelper->getCheckout()->unsFirecheckoutApprovedAgreementIds();
            $requiredAgreements = $checkoutHelper->getRequiredAgreementIds();
            if ($requiredAgreements) {
                if ($paymentData['method'] !== 'xonu_directdebit' && Mage::getStoreConfigFlag('xonu_directdebit/mandate/mandate_terms_active')) {
                    $sepaAgreementId = (int)Mage::getStoreConfig('xonu_directdebit/mandate/mandate_terms');
                    $pos = array_search($sepaAgreementId, $requiredAgreements);
                    unset($requiredAgreements[$pos]);
                }

                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                $diff = array_diff($requiredAgreements, $postedAgreements);
                if ($diff) {
                    $result['success'] = false;
                    $result['error']   = true;
                    $result['error_messages'] = $checkoutHelper->__('Please agree to all the terms and conditions before placing the order.');
                    $this->sendJsonResponse($result);
                    return;
                }
                $checkoutHelper->getCheckout()->setFirecheckoutApprovedAgreementIds($postedAgreements);
            }

            $result = $this->_savePayment();
            if ($result && !isset($result['redirect'])) {
                $result['error_messages'] = $result['error'];
                $result['onecolumn_step'] = 'step-shipping-payment-method';
            }

            $quote->collectTotals();

            if (!isset($result['error'])) {
                Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method', array('request'=>$this->getRequest(), 'quote'=>$quote));
                $this->_subscribeToNewsletter();
            }

            // Sales representative integration
            if (Mage::getStoreConfig('salesrep/setup/enabled')
                && $salesRep = $this->getRequest()->getPost('getvoice')) {

                Mage::getSingleton('core/session')->setSalesrep($salesRep);
            }
            // End of Sales representative integration

            // 3D Secure
            $method = $quote->getPayment()->getMethodInstance();
            if ($method->getIsCentinelValidationEnabled()) {
                $centinel = $method->getCentinelValidator();
                if ($centinel && $centinel->shouldAuthenticate()) {
                    $layout = $this->getLayout();
                    $update = $layout->getUpdate();
                    $update->load('firecheckout_index_saveorder');
                    $this->_initLayoutMessages('checkout/session');
                    $layout->generateXml();
                    $layout->generateBlocks();
                    return $this->sendJsonResponse(array(
                        'method'            => 'centinel',
                        'update_section'    => array(
                            'centinel-iframe' => $layout->getBlock('centinel.frame')->toHtml()
                        )
                    ));
                }
            }
            // 3D Secure

            $paymentData = $this->getRequest()->getPost('payment', array());
            if ($paymentData && @defined('Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT')) {
                $paymentData['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT
                    | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY
                    | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY
                    | Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
                    | Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
            }

            // SagePay Server
//            $sagePaySuiteMethods = array(
//                'sagepayserver',
//                'sagepayform',
//                'sagepaydirectpro'
//            );
//            if (in_array($paymentData['method'], $sagePaySuiteMethods)) {
//                return $this->sendJsonResponse(array(
//                    'method' => 'sagepayserver',
//                    'update_section' => array(
//                        'sagepay-iframe' => $this->getLayout()
//                            ->createBlock('sagepayserver/checkout_onepage_review_info')
//                            ->setTemplate('tm/firecheckout/sagepay/iframe.phtml')
//                            ->toHtml()
//                    )
//                ));
//            }
            // SagePay Server

            $doNotSaveOrderMethods = array(
                'sagepayserver',                // Ebizmarts_SagePaySuite
                'sagepayform',                  // Ebizmarts_SagePaySuite
                'sagepaypaypal',                // Ebizmarts_SagePaySuite
                'sagepaydirectpro',             // Ebizmarts_SagePaySuite
                'molpayseamless',               // Mage_MOLPaySeamless
                'iways_paypalplus_payment',     // Iways_PayPalPlus
                'zipmoneypayment',              // Zipmoney_ZipmoneyPayment
            );
            if (in_array($paymentData['method'], $doNotSaveOrderMethods)
                || $this->getRequest()->getPost('fc-dry-run')) {

                $quote->save();
                return $this->sendJsonResponse(array(
                        'method' => $paymentData['method']
                    ));
            }


            // Authorize.Net
            if (!$this->getRequest()->getBeforeForwardInfo() // if forwarded, then we already did the translaction request to authorize.net
                && 'authorizenet_directpost' === $paymentData['method']) {

                $quote->save();
                $layout = $this->getLayout();
                $update = $layout->getUpdate();
                $update->load('firecheckout_index_saveorder');
                $this->_initLayoutMessages('checkout/session');
                $layout->generateXml();
                $layout->generateBlocks();
                return $this->sendJsonResponse(array(
                        'method' => $paymentData['method'],
                        'popup' => array(
                            'id'      => $paymentData['method'],
                            'content' => $layout->getBlock('payment.form.directpost')->toHtml()
                        )
                    ));
            }
            // Authorize.Net

            if (!isset($result['redirect']) && !isset($result['error'])) {
                if ($paymentData) {
                    $quote->getPayment()->importData($paymentData);
                }

                $checkout->saveOrder();

                // code for magento 1.6 and older
                try {
                    $paymentHelper = Mage::helper("payment");
                    if (method_exists($paymentHelper, 'getZeroSubTotalPaymentAutomaticInvoice')) {
                        $storeId = Mage::app()->getStore()->getId();
                        $zeroSubTotalPaymentAction = $paymentHelper->getZeroSubTotalPaymentAutomaticInvoice($storeId);
                        if ($paymentHelper->isZeroSubTotal($storeId)
                                && $this->_getOrder()->getGrandTotal() == 0
                                && $zeroSubTotalPaymentAction == Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE
                                && $paymentHelper->getZeroSubTotalOrderStatus($storeId) == 'pending') {
                            $invoice = $this->_initInvoice();
                            $invoice->getOrder()->setIsInProcess(true);
                            $invoice->save();
                        }
                    }
                } catch (Exception $e) {
                    // IWD_OrderManager fix
                }

                $redirectUrl = $checkout->getCheckout()->getRedirectUrl();
                $result['success'] = true;
                // $result['order_created'] = true;
                $result['error']   = false;
            } elseif (isset($result['redirect'])) {
                // paypal express register customer fix
                if ('paypal_express' == $paymentData['method']
                    && version_compare(Mage::helper('firecheckout')->getMagentoVersion(), '1.6.1.0') < 0 // 1.6.1 can register customer during express checkout
                    && Mage::getStoreConfig('firecheckout/general/paypalexpress_register')) {

                    $checkout->registerCustomerIfRequested();
                }
            }
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($quote, $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $e->getMessage();

            if ($gotoSection = $checkout->getCheckout()->getGotoSection()) {
                $result['goto_section'] = $gotoSection;
                $checkout->getCheckout()->setGotoSection(null);
            }

            if ($updateSection = $checkout->getCheckout()->getUpdateSection()) {
                if (isset($this->_sectionUpdateFunctions[$updateSection])) {

                    $layout = $this->getUpdateCheckoutLayout();

                    $updateSectionFunction = $this->_sectionUpdateFunctions[$updateSection];
                    $result['update_section'] = array(
                        'name' => $updateSection,
                        'html' => $this->$updateSectionFunction()
                    );
                }
                $checkout->getCheckout()->setUpdateSection(null);
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($quote, $e->getMessage());
            $result['success']  = false;
            $result['error']    = true;
            $result['error_messages'] = Mage::helper('checkout')->__('There was an error processing your order. Please contact us or try again later.');
        }
        $quote->save();
        /**
         * when there is redirect to third party, we don't want to save order yet.
         * we will save the order in return action.
         */
        if (isset($redirectUrl)) {
            $result['redirect'] = $redirectUrl;
        }

        // paypal hss
        if (empty($result['error']) && file_exists(BP . DS . 'app/code/core/Mage/Paypal/Helper/Hss.php')) {
            $payment = $quote->getPayment();
            if ($payment && in_array($payment->getMethod(), Mage::helper('paypal/hss')->getHssMethods())) {
                $layout = $this->getLayout();
                $update = $layout->getUpdate();
                $update->load('firecheckout_index_saveorder');
                $this->_initLayoutMessages('checkout/session');
                $layout->generateXml();
                $layout->generateBlocks();
                $result = array(
                    'method' => 'paypalhss',
                    'afterform' => array(
                        'id'      => $payment->getMethod(),
                        'modal'   => 1,
                        'content' => $layout->getBlock('paypal.iframe')->toHtml()
                    )
                );
                $result['redirect'] = false;
                $result['success'] = false;
            }
        }
        // paypal hss

        $this->sendJsonResponse($result);
    }

    /**
     * Save payment with validation of all fields
     */
    protected function _savePayment()
    {
        // controller_action_predispatch_checkout_onepage_savePayment
        if (Mage::getStoreConfig('payment/ops_alias/active')) {
            Mage::getModel('ops/observer')->checkoutTypeOnepageSavePaymentAfter();
        }

        try {
            $result = array();
            $data = $this->getRequest()->getPost('payment', array());
            $result = $this->getCheckout()->savePayment($data);

            $redirectUrl = $this->getCheckout()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
            }
        } catch (Mage_Payment_Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = $e->getMessage();
        } catch (Mage_Core_Exception $e) {
            $result['error'] = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = Mage::helper('checkout')->__('Unable to set Payment Method.');
        }
        return $result;
    }

    protected function _renderSections($sections)
    {
        $result = array();
        foreach ($sections as $id) {
            $method = str_replace(' ', '', ucwords(str_replace('-', ' ', $id)));
            $method = '_get' . $method . 'Html';
            if (method_exists($this, $method)) {
                $result[$id] = $this->{$method}();
            }
        }
        return $result;
    }

    // https://github.com/mrlynn/MobileBrowserDetectionExample
    private function _isMobile()
    {
        $isMobile = false;
        if(isset($_SERVER['HTTP_USER_AGENT'])
            && preg_match('/(android|up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {

            $isMobile = true;
        }

        $strpos = 0;
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            $strpos = strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml');
        }
        if ($strpos > 0
            || ((isset($_SERVER['HTTP_X_WAP_PROFILE'])
                || isset($_SERVER['HTTP_PROFILE'])))) {

            $isMobile = true;
        }

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $mobileUserAgent = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
            $mobileAgents = array(
                'w3c ','acs-','alav','alca','amoi','andr','audi','avan','benq',
                'bird','blac','blaz','brew','cell','cldc','cmd-','dang','doco',
                'eric','hipt','inno','ipaq','java','jigs','kddi','keji','leno',
                'lg-c','lg-d','lg-g','lge-','maui','maxo','midp','mits','mmef',
                'mobi','mot-','moto','mwbp','nec-','newt','noki','oper','palm',
                'pana','pant','phil','play','port','prox','qwap','sage','sams',
                'sany','sch-','sec-','send','seri','sgh-','shar','sie-','siem',
                'smal','smar','sony','sph-','symb','t-mo','teli','tim-','tosh',
                'tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
                'wapr','webc','winw','winw','xda','xda-'
            );
            if(in_array($mobileUserAgent, $mobileAgents)) {
                $isMobile = true;
            }
        }

        if (isset($_SERVER['ALL_HTTP'])) {
            $strpos = strpos(strtolower($_SERVER['ALL_HTTP']), 'OperaMini');
            if ($strpos > 0) {
                $isMobile = true;
            }
        }
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $strpos = strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows');
            if ($strpos > 0) {
                $isMobile = false;
            }
        }
        return $isMobile;
    }

    /**
     * Subsribe payer to newsletterr.
     * All notices and error messages are not shown,
     * to not confuse payer during checkout (Only checkout messages can be showed).
     *
     * @return void
     */
    protected function _subscribeToNewsletter()
    {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('newsletter')) {
            //$session       = Mage::getSingleton('core/session');
            $customerSession = Mage::getSingleton('customer/session');
            $billingData     = $this->getRequest()->getPost('billing');
            $email           = $customerSession->isLoggedIn() ?
                $customerSession->getCustomer()->getEmail() : $billingData['email'];

            try {
                if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 &&
                    !$customerSession->isLoggedIn()) {
                    Mage::throwException(Mage::helper('newsletter')->__('Sorry, but administrator denied subscription for guests. Please <a href="%s">register</a>.', Mage::getUrl('customer/account/create/')));
                }

                $ownerId = Mage::getModel('customer/customer')
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                        ->loadByEmail($email)
                        ->getId();
                if ($ownerId !== null && $ownerId != $customerSession->getId()) {
                    Mage::throwException(Mage::helper('newsletter')->__('Sorry, but your can not subscribe email adress assigned to another user.'));
                }

                $status = Mage::getModel('newsletter/subscriber')->subscribe($email);
            } catch (Exception $e) {
            }

            $quote = $this->getCheckout()->getQuote();
            if ($quote->getCheckoutMethod() != TM_FireCheckout_Model_Type_Standard::METHOD_GUEST) {
                $quote->getCustomer()->setIsSubscribed(1);
            }
        }
    }

    public function forgotpasswordAction()
    {
        $session = Mage::getSingleton('customer/session');

        if ($this->_expireAjax() || $session->isLoggedIn()) {
            return;
        }

        $email = $this->getRequest()->getPost('email');
        $result = array(
            'success' => false
        );
        if ($email) {
            if (class_exists('Mage_Customer_Model_Flowpassword')) {
                /**
                 * @var $flowPassword Mage_Customer_Model_Flowpassword
                 */
                $flowPassword = Mage::getModel('customer/flowpassword');
                $flowPassword->setEmail($email)->save();

                if (!$flowPassword->checkCustomerForgotPasswordFlowEmail($email)) {
                    $result['error'] = Mage::helper('customer')->__(
                        'You have exceeded requests to times per 24 hours from 1 e-mail.'
                    );
                    return $this->sendJsonResponse($result);
                }

                if (!$flowPassword->checkCustomerForgotPasswordFlowIp()) {
                    $result['error'] = Mage::helper('customer')->__(
                        'You have exceeded requests to times per hour from 1 IP.'
                    );
                    return $this->sendJsonResponse($result);
                }
            }

            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $session->setForgottenEmail($email);
                $result['error'] = Mage::helper('checkout')->__('Invalid email address.');
            } else {
                $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                    ->loadByEmail($email);

                if ($customer->getId()) {
                    try {
                        $customerHelper = Mage::helper('customer');
                        if (method_exists($customerHelper, 'generateResetPasswordLinkToken')) {
                            $newResetPasswordLinkToken = $customerHelper->generateResetPasswordLinkToken();
                            if (method_exists($customerHelper, 'generateResetPasswordLinkCustomerId')) {
                                $newResetPasswordLinkCustomerId = $customerHelper
                                    ->generateResetPasswordLinkCustomerId($customer->getId());
                                $customer->changeResetPasswordLinkCustomerId($newResetPasswordLinkCustomerId);
                            }
                            $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                            $customer->sendPasswordResetConfirmationEmail();
                        } else {
                            // 1.6.0.x and earlier
                            $newPassword = $customer->generatePassword();
                            $customer->changePassword($newPassword, false);
                            $customer->sendPasswordReminderEmail();
                            $result['message'] = Mage::helper('customer')->__('A new password has been sent.');
                        }
                        $result['success'] = true;
                    } catch (Exception $e) {
                        $result['error'] = $e->getMessage();
                    }
                }
                if (!isset($result['message']) && ($result['success'] || !$customer->getId())) {
                    $result['message'] = Mage::helper('customer')->__('If there is an account associated with %s you will receive an email with a link to reset your password.', Mage::helper('customer')->htmlEscape($email));
                }
            }
        } else {
            $result['error'] = Mage::helper('customer')->__('Please enter your email.');
        }

        $this->sendJsonResponse($result);
    }

    public function loginAction()
    {
        // false to prevent _expireAjax to return redirect
        $this->getCheckout()->getCheckout()->setCartWasUpdated(false);

        $session = Mage::getSingleton('customer/session');

        if ($this->_expireAjax() || $session->isLoggedIn()) {
            return;
        }

        $result = array(
            'success' => false
        );

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $session->login($login['username'], $login['password']);
                    $result['redirect'] = Mage::helper('firecheckout')->getFirecheckoutUrl();
                    $result['success'] = true;
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', Mage::helper('customer')->getEmailConfirmationUrl($login['username']));
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    $result['error'] = $message;
                    $session->setUsername($login['username']);
                } catch (Exception $e) {
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                }
            } else {
                $result['error'] = Mage::helper('customer')->__('Login and password are required.');
            }
        }

        $this->sendJsonResponse($result);
    }

    public function verifyEmailAction()
    {
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->loadByEmail($this->getRequest()->getPost('email'));

        $this->sendJsonResponse(array(
            'exists' => (bool)$customer->getId()
        ));
    }

    public function storepickupChangeAddress()
    {
        $data = Mage::getSingleton('checkout/session')->getData('storepickup_session');
        if (isset($data['store_id']) && $data['store_id']) {
            $store = Mage::getModel('storepickup/store')->load($data['store_id']);

            $data['firstname'] = Mage::helper('storepickup')->__('Store');
            $data['lastname'] = $store->getData('store_name');
            $data['street'][0] = $store->getData('address');
            $data['city'] = $store->getCity();
            $data['region'] = $store->getState();
            $data['region_id'] = $store->getData('state_id');
            $data['postcode'] = $store->getData('zipcode');
            $data['country_id'] = $store->getData('country');

            $data['company'] = '';
            if($store->getStoreFax())
                $data['fax'] = $store->getStoreFax();
            else
                unset($data['fax']);
            if($store->getStorePhone())
                $data['telephone'] = $store->getStorePhone();
            else
                unset($data['telephone']);

            $data['save_in_address_book'] = 1;
        }

        try {
            $address = $this->getCheckout()->getQuote()->getShippingAddress();
            unset($data['address_id']);
            $address->addData($data);
            $address->implodeStreetAddress();
            $address->setCollectShippingRates(true);
        } catch(Exception $e) {
            //
        }
    }

    public function relaypointChangeAddress()
    {
        if ($relaypoint = $this->getRequest()->getParam('relay-point')) {
            list($street, $description, $postcode, $city) = explode("&&&", $relaypoint);
            $shipping = array(
                'street'      => $street,
                'description' => $description,
                'postcode'    => $postcode,
                'city'        => $city
            );

            $current = $this->getCheckout()->getQuote();
            Mage::register ( 'current_quote', $current );
            $address = $current->getShippingAddress ();

            ( string ) $postcode = $shipping ['postcode'];
            if (substr ( $postcode, 0, 2 ) == 20) {
                $regioncode = substr ( $postcode, 0, 3 );
                switch ($regioncode) {
                    case 201 :
                        $regioncode = '2A';
                        break;
                    case 202 :
                        $regioncode = '2B';
                        break;
                }
            } else {
                $regioncode = substr ( $postcode, 0, 2 );
            }
            Mage::app ()->getLocale ()->setLocaleCode ( 'en_US' );
            $region = Mage::getModel ( 'directory/region' )->loadByCode ( $regioncode, $address->getCountryId () );
            $regionname = $region->getDefaultName ();
            $regionid = $region->getRegionId ();
            $address->setRegion ( $regionname );
            $address->setRegionId ( $regionid );
            $address->setPostcode ( $postcode );
            $address->setStreet ( urldecode ( $shipping ['street'] ) );
            $address->setCity ( urldecode ( $shipping ['city'] ) );
            $address->setCompany ( urldecode ( $shipping ['description'] ) );
            $address->save ();
            $current->setShippingAddress ( $address );
//            $current->save ();
        }
    }

    /**
     * Check can page show for unregistered users
     *
     * @return boolean
     */
    protected function _canShowForUnregisteredUsers()
    {
        return true;
    }

    public function buyerprotectAction()
    {
        // false to prevent _expireAjax to return redirect
        $this->getCheckout()->getCheckout()->setCartWasUpdated(false);

        if ($this->_expireAjax()) {
            return;
        }
        if (!$this->getRequest()->isPost()) {
            $this->_ajaxRedirectResponse();
            return;
        }

        /**
         * @var Mage_Sales_Model_Quote
         */
        $quote    = $this->getOnepage()->getQuote();
        $sections = array('review');
        $ajaxHelper = Mage::helper('firecheckout/ajax');
        $quote->collectTotals();

        if ($ajaxHelper->getIsShippingMethodDependsOn(array('cart', 'total'))) {
            $sections[] = 'shipping-method';
            $this->recollectShippingRatesAndTotals(true);
        }

        if ($ajaxHelper->getIsPaymentMethodDependsOn(array('cart', 'total'))) {
            $sections[] = 'payment-method';
            $this->applyPaymentMethodAndCollectTotals($ajaxHelper->getIsTotalDependsOn('payment-method'));
        }
        $quote->save();

        $result['update_section'] = $this->_renderSections($sections);
        $this->sendJsonResponse($result);
    }

    /**
     * GiveChangeMakeChange integration
     *
     * Modified version of GCMC_GiveChange_CartController::addAction
     */
    public function givechangeaddAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if (!$this->getRequest()->isPost()) {
            $this->_ajaxRedirectResponse();
            return;
        }

        /**
         * @var Mage_Sales_Model_Quote
         */
        $quote    = $this->getOnepage()->getQuote();
        $cart     = Mage::getSingleton('checkout/cart');
        $session  = $this->getCheckout()->getCheckout();
        $sections = array('review');
        $ajaxHelper = Mage::helper('firecheckout/ajax');

        try {
            foreach ($quote->getAllVisibleItems() as $item) {
                if ($item->getProductType() == GCMC_GiveChange_Model_Product_Type_Donation::TYPE_CODE) {
                    $cart->removeItem($item->getId());
                    $quote->removeItem($item->getId());
                }
            }

            // Recollect the totals after deleting any items
            // $quote->collectTotals();
            $product = $this->_givechnageloadProduct();

            if (!$product) {
                throw new Mage_Core_Exception($this->__('Cannot add the Give Change donation to your shopping cart.'));
            }

            // Set donation value only if custom selected
            if ($this->getRequest()->getParam('roundup', null) == GCMC_GiveChange_Helper_Data::DONATION_TYPE_CUSTOM) {
                $product->setDonationValue($this->getRequest()->getParam('custom', null));
            }

            $params = array(
                'qty'     => 1,
                'giftaid' => $this->getRequest()->getParam('giftaid', false),
                'roundup' => $this->getRequest()->getParam('roundup', GCMC_GiveChange_Helper_Data::DONATION_TYPE_CUSTOM),
                'value'   => $product->getDonationValue()
            );

            $cart->addProduct($product, $params);
            $cart->save();

            // false to prevent _expireAjax to return redirect
            $session->setCartWasUpdated(false);

            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );
        } catch (Mage_Core_Exception $e) {
            $result['error'] = $e->getMessage();
        } catch(Exception $e) {
            $result['error'] = $this->__('Cannot add the Give Change donation to your shopping cart.');
        }

        $quote->collectTotals();
        if ($ajaxHelper->getIsShippingMethodDependsOn(array('cart', 'total'))) {
            $sections[] = 'shipping-method';
            $this->recollectShippingRatesAndTotals(true);
        }

        if ($ajaxHelper->getIsPaymentMethodDependsOn(array('cart', 'total'))) {
            $sections[] = 'payment-method';
            $this->applyPaymentMethodAndCollectTotals($ajaxHelper->getIsTotalDependsOn('payment-method'));
        }
        $quote->save();

        $result['update_section'] = $this->_renderSections($sections);
        $this->sendJsonResponse($result);
    }

    protected function _givechnageloadProduct()
    {
        $product = Mage::getModel('catalog/product');
        $id      = $product->getIdBySku(Mage::helper('givechange')->getProductSku());
        if ($id) {
            $product->setStoreId(Mage::app()->getStore()->getId())->load($id);
            if ($product->getId()) {
                return $product;
            }
        }
        return $this->_givechnagecreateProduct();
    }

    protected function _givechnagecreateProduct()
    {
        $product = Mage::getModel('catalog/product');
        Mage::getSingleton('givechange/product_type_donation')->addDefaultData($product);
        $product->save()->load();
        return $product;
    }

    protected function _saveAdvoxInpost()
    {
        $postData = $this->getRequest()->getPost();
        if (isset($postData['shipping_inpost_machine_id'])) {
            Mage::getSingleton('checkout/session')->setInpostMachine($postData['shipping_inpost_machine_id']);
            $quoteModel = Mage::getSingleton('checkout/session')->getQuote();
            $quoteModel->setData('inpost_machine', $postData['shipping_inpost_machine_id']);
            $quoteModel->save();
        }
    }

    /**
     * Ability to update saved address direclty at checkout page.
     *
     * If address still not valid after this method - redirect to magento's
     * address edit form.
     */
    public function updateSavedAddressAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if (!$this->getRequest()->isPost()) {
            return;
        }

        $addressType = $this->getRequest()->getParam('address_type', 'billing');
        $rawData = $this->getRequest()->getPost($addressType, array());
        $customerAddressId = $this->getRequest()->getPost($addressType . '_address_id', false);

        $customerSession = Mage::getSingleton('customer/session');
        $customer = $customerSession->getCustomer();
        $address  = Mage::getModel('customer/address')->load($customerAddressId);

        if (!$address->getId() || $address->getCustomerId() != $customer->getId()) {
            return $this->sendJsonResponse(array(
                'redirect' => Mage::getUrl('customer/address/edit', array('id' => $address->getId()))
            ));
        }
        $errors = array();

        // Allow to update missing address fields only, as all other fields are
        // hidden in address popup, so this will prevent accident update and
        // possible hacks
        $data = array();
        foreach ($this->getCheckout()->getInvalidAddressFields($address) as $field) {
            if (array_key_exists($field, $rawData)) {
                $data[$field] = $rawData[$field];
            }
        }

        /* @var $addressForm Mage_Customer_Model_Form */
        $addressForm = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_address_edit')->setEntity($address);
        $addressData = $addressForm->extractData($addressForm->prepareRequest($data));
        // merge existing address with request
        foreach ($addressData as $key => $value){
            if (false === $value) {
                $addressData[$key] = $address->getData($key);
            }
        }
        $addressErrors = $addressForm->validateData($addressData);
        if ($addressErrors !== true) {
            $errors = $addressErrors;
        }

        try {
            $addressForm->compactData($addressData);
            $address->setCustomerId($customer->getId());

            if (count($errors) === 0) {
                $address->save();
                return $this->sendJsonResponse(array());
            } else {
                $customerSession->setAddressFormData($data);
                foreach ($errors as $errorMessage) {
                    $customerSession->addError($errorMessage);
                }
            }
        } catch (Mage_Core_Exception $e) {
            $customerSession->setAddressFormData($data)
                ->addException($e, $e->getMessage());
        } catch (Exception $e) {
            $customerSession->setAddressFormData($data)
                ->addException($e, $this->__('Cannot save address.'));
        }

        return $this->sendJsonResponse(array(
            'redirect' => Mage::getUrl('customer/address/edit', array('id' => $address->getId()))
        ));
    }

    public function updateSectionsAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if (!$this->getRequest()->isPost()) {
            $this->_ajaxRedirectResponse();
            return;
        }

        $this->getOnepage()->getQuote()->collectTotals()->save();

        $this->sendJsonResponse(array(
            'update_section' => $this->_renderSections($this->getRequest()->getParam('sections', array()))
        ));
    }

    private function sendJsonResponse($data)
    {
        if (is_array($data)) {
            $data['responseUrl'] = Mage::getUrl('*/*/*');
            $data = Mage::helper('core')->jsonEncode($data);
        }
        $this->getResponse()->setBody($data);
    }

    private function recollectShippingRatesAndTotals($reapplyMethod = true, $saveAddress = false)
    {
        $quote = $this->getCheckout()->getQuote();

        $quote->getShippingAddress()
            ->setCollectShippingRates(true)
            ->collectShippingRates();

        if ($saveAddress) {
            $quote->getShippingAddress()->save();
        }

        if ($reapplyMethod) {
            $this->getCheckout()->applyShippingMethod();
        }

        $quote->setTotalsCollectedFlag(false)->collectTotals();
    }

    private function applyPaymentMethodAndCollectTotals($collectTotals = true, $paymentMethod = null)
    {
        $quote = $this->getCheckout()->getQuote();

        if (null === $paymentMethod) {
            $this->getCheckout()->applyPaymentMethod();
        } else {
            $this->getCheckout()->applyPaymentMethod($paymentMethod);
        }

        if ($collectTotals) {
            // to recollect discount rules need to clear previous discount
            // descriptions and mark address as modified
            // see _canProcessRule in Mage_SalesRule_Model_Validator
            $quote->getShippingAddress()
                ->setDiscountDescriptionArray(array())
                ->isObjectNew(true);

            $quote->setTotalsCollectedFlag(false)->collectTotals();
        }
    }
}
