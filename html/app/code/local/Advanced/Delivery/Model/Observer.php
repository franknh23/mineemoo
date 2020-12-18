<?php

class Advanced_Delivery_Model_Observer {
    public function saveShippingMethod($observer) {
        $params = Mage::app()->getRequest()->getParams();
        
        if(isset($params['enabled_delivery_time']) && $params['enabled_delivery_time']){
            $useDelivery = '';
            $deliveryDate = '';
            $deliveryTime = '';
            $deliveryComment = '';
            if(isset($params['enabled_delivery_time']))
                $useDelivery = $params['enabled_delivery_time'];
            if(isset($params['delivery_date']))
                $deliveryDate = $params['delivery_date'];
            if(isset($params['delivery_time']))
                $deliveryTime = $params['delivery_time'];
            if(isset($params['delivery_comment']))    
                $deliveryComment = $params['delivery_comment'];

            $dateformat = Mage::helper('delivery')->getDateFormat();
            
            switch ($dateformat) {
                case '%Y-%m-%d':
                    $yyy = substr($deliveryDate, 0, 4);
                    $mm = substr($deliveryDate, 5, 2);
                    $dd = substr($deliveryDate, 8, 2);
                    $delivery = $yyy . '-' . $mm . '-' . $dd;
                    break;
                case '%y-%m-%d':
                    $yyy = substr($deliveryDate, 0, 4);
                    $mm = substr($deliveryDate, 5, 2);
                    $dd = substr($deliveryDate, 8, 2);
                    $delivery = $yyy . '-' . $mm . '-' . $dd;
                    break;
                case '%d-%m-%Y':
                    $yyy = substr($deliveryDate, 6, 4);
                    $mm = substr($deliveryDate, 3, 2);
                    $dd = substr($deliveryDate, 0, 2);
                    $delivery = $yyy . '-' . $mm . '-' . $dd;
                    break;
                case '%d-%m-%y':
                    $yyy = substr($deliveryDate, 6, 4);
                    $mm = substr($deliveryDate, 3, 2);
                    $dd = substr($deliveryDate, 0, 2);
                    $delivery = $yyy . '-' . $mm . '-' . $dd;
                    break;
                case '%Y-%d-%m':
                    $yyy = substr($deliveryDate, 0, 4);
                    $mm = substr($deliveryDate, 8, 2);
                    $dd = substr($deliveryDate, 5, 2);
                    $delivery = $yyy . '-' . $mm . '-' . $dd;
                    break;
                case '%y-%d-%m':
                    $yyy = substr($deliveryDate, 0, 4);
                    $mm = substr($deliveryDate, 8, 2);
                    $dd = substr($deliveryDate, 5, 2);
                    $delivery = $yyy . '-' . $mm . '-' . $dd;
                    break;
                default:
                    $delivery = substr($deliveryDate, 6, 4) . '-' . substr($deliveryDate, 0, 2) . '-' . substr($deliveryDate, 3, 2);
                    break;
            }

            $cookie = Mage::getSingleton('core/cookie');
            $cookie->set('use_delivery_date', $params['enabled_delivery_time']);
            $cookie->set('delivery_date', $delivery);
            $cookie->set('delivery_time', $deliveryTime);
            $cookie->set('delivery_comment', $deliveryComment);
            
       
        }
    }
    public function _afterSave($observer) {
        
        if(!Mage::getModel('core/cookie')->get('use_delivery_date')){
            return;
        }
        $order = $observer->getEvent()->getOder();
        
        $deliverydate = Mage::getModel('core/cookie')->get('delivery_date');
        $deliverytime = Mage::getModel('core/cookie')->get('delivery_time');
        $deliverycomment = Mage::getModel('core/cookie')->get('delivery_comment');
        $Id = $observer->getEvent()->getOrder()->getId();
        $order = Mage::getModel('sales/order')->load($Id);
        $Incrementid = $order->getIncrementId();
         Mage::log($deliverydate, null, 'delivery.log');
        try {
            $delivery = Mage::getModel('delivery/deliverydate');
            $delivery->setData('delivery_date', $deliverydate)
                    ->setData('hourstart', $deliverytime)
                    ->setData('description', $deliverycomment)
                    ->setData('order_id', $Id)
                    ->setData('increment_id', $Incrementid)
                    ->save();
            Mage::getModel('core/cookie')->delete('use_delivery_date','delivery_date', 'delivery_time', 'delivery_comment');
        } catch (Exception $e) {     
            Mage::log($e->getTraceAsString(), null, 'delivery.log');
        }
    }
}
