<?php

class SendCloud_Plugin_Model_Order_ServicePoint
{
    const POST_SERVICE_POINT_SELECTED = 'sendcloudshipping_service_point_selected';
    const POST_SERVICE_POINT_EXTRA = 'sendcloudshipping_service_point_extra';
    const SESSION_SERVICE_POINT = 'sendcloud_service_point';
    const SESSION_SERVICE_POINT_EXTRA = 'sendcloud_service_point_extra';
    const SHIPPING_METHOD_SERVICE_POINT = 'servicepoint_flatrate_servicepoint_flatrate';

    public function before_order_save($observer)
    {
        $order = $observer->getOrder();

        if (Mage::getStoreConfig('sendcloud/servicepoint')) {
            $spp = $order->getData('sendcloud_service_point');
            $post = Mage::app()->getFrontController()->getRequest()->getPost();

            $shippingMethod = isset($post['shipping_method']) ? $post['shipping_method'] : $order->getShippingMethod();

            if ($shippingMethod === self::SHIPPING_METHOD_SERVICE_POINT && !$spp) {

                if (isset($post[self::POST_SERVICE_POINT_SELECTED])) {
                    $sendcloudServicePoint = $post[self::POST_SERVICE_POINT_SELECTED];
                    $sendcloudServicePointExtra = $post[self::POST_SERVICE_POINT_EXTRA];
                } else {
                    $checkoutSession = Mage::getSingleton('checkout/session');
                    $sendcloudServicePoint = $checkoutSession->getStepData('shipping_method', self::SESSION_SERVICE_POINT);
                    $sendcloudServicePointExtra = $checkoutSession->getStepData('shipping_method', self::SESSION_SERVICE_POINT_EXTRA);
                }

                $order->setData('sendcloud_service_point', $sendcloudServicePoint);
                $order->setData('sendcloud_service_point_extra', $sendcloudServicePointExtra);
            }
        }
    }

    public function after_shippingmethod_save($observer)
    {
        if (Mage::getStoreConfig('sendcloud/servicepoint')) {
            $post = Mage::app()->getFrontController()->getRequest()->getPost();

            if (isset($post['shipping_method'], $post[self::POST_SERVICE_POINT_SELECTED])
                    && $post['shipping_method'] === self::SHIPPING_METHOD_SERVICE_POINT) {
                $checkoutSession = Mage::getSingleton('checkout/session');
                $sendcloudServicePoint = $post[self::POST_SERVICE_POINT_SELECTED];
                $sendcloudServicePointExtra = $post[self::POST_SERVICE_POINT_EXTRA];

                $checkoutSession->setStepData('shipping_method', self::SESSION_SERVICE_POINT, $sendcloudServicePoint);
                $checkoutSession->setStepData('shipping_method', self::SESSION_SERVICE_POINT_EXTRA, $sendcloudServicePointExtra);
            }
        }
    }
}
