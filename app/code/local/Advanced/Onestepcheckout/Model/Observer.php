<?php

/**
 * Advanced Checkout
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Onestepcheckout.com license that is
 * available through the world-wide-web at this URL:
 * http://www.advancedcheckout.com/license-agreement
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category 	Advanced Checkout
 * @package 	Advanced_Onestepcheckout
 * @copyright 	Copyright (c) 2015 Advanced Checkout (http://www.advancedcheckout.com/)
 * @license 	http://www.advancedcheckout.com/license-agreement
 */

/**
 * Onestepcheckout Block
 * 
 * @category 	Onestepcheckout
 * @package 	Advanced_Onestepcheckout
 * @author  	Onestepcheckout Developer
 */
class Advanced_Onestepcheckout_Model_Observer {

    const SCOPE_DEFAULT = 'default';
    const SCOPE_WEBSITES = 'websites';
    const SCOPE_STORES = 'stores';

    /**
     * process controller_action_predispatch event
     *
     * @return Advanced_Onestepcheckout_Model_Observer
     */
    public function controllerActionPredispatch($observer) {
        $action = $observer->getEvent()->getControllerAction();
        return $this;
    }

    /**
     * process checkout_cart_add_product_complete event
     *
     * @return Advanced_Onestepcheckout_Model_Observer
     */
    public function initCartController($observer) {
        $storeId = Mage::app()->getStore()->getStoreId();
        if (Mage::getStoreConfig('onestepcheckout/general/enable', $storeId)) {
            if (Mage::getStoreConfig('onestepcheckout/features/redirect_to_checkout', $storeId)) {
                $message = Mage::helper('onestepcheckout')->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($observer->getProduct()->getName()));
                Mage::getSingleton('checkout/session')->addSuccess($message);
                $redirect = Mage::getUrl('onestepcheckout/index', array('_secure' => true));
                Header('Location: ' . $redirect);
                exit();
            }
        }
    }

    /**
     * process sales_order_save_after event
     *
     * @return Advanced_Onestepcheckout_Model_Observer
     */
    public function orderSaveAfter($observer) {
        if (Mage::registry('ULTIMATECHECKOUT_ORDER_SAVE_AFTER'))
            return;
        Mage::register('ULTIMATECHECKOUT_ORDER_SAVE_AFTER', true);
        $params = Mage::app()->getRequest()->getParams();
        $order = $observer->getEvent()->getOrder();


        if (Mage::getStoreConfig('onestepcheckout/features/enable_survey', Mage::app()->getStore()->getStoreId())) {
            $survey = Mage::getModel('onestepcheckout/survey');

            $surveyQuestion = Mage::getStoreConfig('onestepcheckout/features/survey_question', Mage::app()->getStore()->getStoreId());
            $surveyValues = unserialize(Mage::getStoreConfig('onestepcheckout/features/answer_values', Mage::app()->getStore()->getStoreId()));
            $surveyValue = '';

            if (isset($params['survey']))
                $surveyValue = $params['survey'];
            $surveyFreeText = '';
            if (isset($params['survey-freetext']))
                $surveyFreeText = $params['survey-freetext'];
            $surveyAnswer = '';
            if (isset($surveyValue)) {
                if ($surveyValue != 'freetext') {
                    if (isset($surveyValues[$surveyValue]['value']))
                        $surveyAnswer = $surveyValues[$surveyValue]['value'];
                } else {
                    $surveyAnswer = $surveyFreeText;
                }
            }

            if ($surveyAnswer) {
                try {
                    $survey->setData('question', $surveyQuestion)
                            ->setData('answer', $surveyAnswer)
                            ->setData('order_id', $order->getId())
                            ->save();
                } catch (Exception $e) {
                    
                }
            }
        }
    }

    /**
     * process controller_action_predispatch_adminhtml_system_config_edit event
     *
     * @return Advanced_Onestepcheckout_Model_Observer
     */
    public function adminhtml_system_config($observer) {
        if (Mage::app()->getRequest()->getParam('section') == 'onestepcheckout') {
            Mage::app()->getLayout()->getBlock('head')
                    ->addItem('skin_css', 'css/advanced/onestepcheckout/style.css')
                    ->addItem('skin_css', 'css/advanced/onestepcheckout/colpick.css')
                    ->addItem('js', 'advanced/checkout/lib/checkoutadmin.js')
                    ->addItem('js', 'advanced/checkout/lib/colorpicker/jquery-1.7.2.min.js')
                    ->addItem('js', 'advanced/checkout/lib/colorpicker/colpick.js')
                    ->addItem('js', 'advanced/checkout/lib/jquery-ui.min.js');
        }
    }

    /**
     * process checkout_type_onepage_save_order event
     *
     * @return Advanced_Onestepcheckout_Model_Observer
     */
    public function saveComment($observer) {
        $params = Mage::app()->getRequest()->getParams();
        if (Mage::getStoreConfig('onestepcheckout/features/order_comment', Mage::app()->getStore()->getStoreId())) {
            $comment = trim($params['billing']['order_comment']);
            if ($comment != '') {
                $order = $observer->getEvent()->getOrder();
                try {
                    $order->addStatusHistoryComment($comment, false);
                } catch (Exception $e) {
                    
                }
            }
        }
    }

}
