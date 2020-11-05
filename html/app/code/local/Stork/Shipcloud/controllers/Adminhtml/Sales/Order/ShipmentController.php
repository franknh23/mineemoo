<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales order shipment controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
require_once 'Mage/Adminhtml/controllers/Sales/Order/ShipmentController.php';

class Stork_Shipcloud_Adminhtml_Sales_Order_ShipmentController extends Mage_Adminhtml_Sales_Order_ShipmentController
{

    /**
     * Initialize shipment items QTY
     */
    protected function _getItemQtys()
    {
        $data = $this->getRequest()->getParam('shipment');
        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = array();
        }
        return $qtys;
    }

    public function savedAction()
    {
        $data = $this->getRequest()->getPost('shipment');
        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }
        $order_id = $this->getRequest()->getParam('order_id');
          $qty = $this->_getItemQtys();

        $qty = $qty[$order_id];
        $get_pakage = Mage::getStoreConfig('shipcloud/pcarrier_group/default_units',Mage::app()->getStore());
        $rva_redirect ='*/sales_order/view';
        if($data['shipcloud_create'] != "0"){
            $rva_redirect = 'shipcloud/adminhtml_shipcloud/intermediate/carrier/'.$data['shipcloud_create'];
        }
        if(empty($get_pakage) || $get_pakage >= $qty){
          try {
              if ($shipment = $this->_initShipment()) {
                  $shipment->register();

                  $comment = '';
                  if (!empty($data['comment_text'])) {
                      $shipment->addComment($data['comment_text'], isset($data['comment_customer_notify']), isset($data['is_visible_on_front']));
                      if (isset($data['comment_customer_notify'])) {
                          $comment = $data['comment_text'];
                      }
                  }

                  if (!empty($data['send_email'])) {
                      $shipment->setEmailSent(true);
                  }

                  //$shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                  $this->_saveShipment($shipment);
                  //$shipment->sendEmail(!empty($data['send_email']), $comment);
                  $this->_getSession()->addSuccess($this->__('The shipment has been created.'));
                  Mage::getSingleton('adminhtml/session')->getCommentText(true);
                  $this->_redirect($rva_redirect, array('order_id' => $order_id, 'shipment_id' => $shipment->getId(), 'type' => 'shipment','carrier' => $data['shipcloud_create']));
                  return;
              } else {
                  $this->_forward('noRoute');
                  return;
              }
          } catch (Mage_Core_Exception $e) {
              $this->_getSession()->addError($e->getMessage());
          } catch (Exception $e) {
              $this->_getSession()->addError($this->__('Cannot save shipment.'));
          }

        } else {
            $mod = $qty%$get_pakage;
            $div = ($qty - $mod)/$get_pakage+(($mod != 0)?1:0);
            if($div > 1){
              for ($i=0; $i < $div-1; $i++) {
                  $shipment[] = $this->createShipping($data, $get_pakage);
              }
            }
            if($mod > 0)
                $shipment[] = $this->createShipping($data, $mod);
            //echo $div.' -- '.$mod;
            $ship_id = implode(':',$shipment);

            $this->_redirect($rva_redirect, array('order_id' => $order_id, 'shipment_id' => $ship_id, 'type' => 'shipment','carrier' => $data['shipcloud_create']));

            return ;
        }
        $this->_redirect('*/*/new', array('order_id' => $order_id));

    }



    protected function _initShipmentByQty($order_id,$qtys = null)
    {
        $this->_title($this->__('Sales'))->_title($this->__('Shipments'));

        $order      = Mage::getModel('sales/order')->load($order_id);

        /**
         * Check order existing
         */
        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('The order no longer exists.'));
            return false;
        }
        /**
         * Check shipment is available to create separate from invoice
         */
        if ($order->getForcedDoShipmentWithInvoice()) {
            $this->_getSession()->addError($this->__('Cannot do shipment for the order separately from invoice.'));
            return false;
        }

        /**
         * Check shipment create availability
         */
        if (!$order->canShip()) {
            $this->_getSession()->addError($this->__('Cannot do shipment for the order.'));
            //return false;
        }

        if(empty($qtys)){
            $savedQtys = $this->qty;
        } else {
            $savedQtys = $qtys;
        }

        $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys);

        $tracks = $this->getRequest()->getPost('tracking');
        if ($tracks) {
            foreach ($tracks as $data) {
                if (empty($data['number'])) {
                    Mage::throwException($this->__('Tracking number cannot be empty.'));
                }
                $track = Mage::getModel('sales/order_shipment_track')
                    ->addData($data);
                $shipment->addTrack($track);
            }
        }

        return $shipment;
    }
    public function saveAction()
    {

        $order_items = $this->getRequest()->getParam('shipment');

        if($order_items['shipcloud_create'] == 'nocarrier' || $order_items['shipcloud_create'] == 0)
        {
            return parent::saveAction();
        }

        $order_id = $this->getRequest()->getParam('order_id');
        $order_qtys = array();

      $rva_redirect = 'shipcloud/adminhtml_shipcloud/intermediate/';

      $order = Mage::getModel("sales/order")->load($order_id);
      //$order_id = $id;

      $order_qtys = array();
      $qty = 0;
      $shipped = 0;
      $shipper_qty = array();
      foreach ($order->getAllItems() as $item) {
        if(!empty($order_items['items'][$item->getItemId()])){
          $order_qtys['ordered'][$item->getItemId()] = $item->getQtyOrdered();
          if(!empty($item->getQtyShipped()))
            $order_qtys['shipped'][$item->getItemId()] = $item->getQtyShipped();
            $order_qtys['last'][$item->getItemId()] = $order_items['items'][$item->getItemId()];
        }
        $qty += $order_qtys['last'][$item->getItemId()];
      }

      $get_pakage = Mage::getStoreConfig('shipcloud/pcarrier_group/default_units',Mage::app()->getStore());
      $use_one_if_empty = Mage::getStoreConfig('shipcloud/pcarrier_group/use_one_if_empty', Mage::app()->getStore());

        $packages = array();
        $ship_ids = array();
        if(!empty($order_qtys['last'])){
          if($use_one_if_empty){
             if($get_pakage >= $qty) {
               foreach ( $order_qtys['last'] as $id => $q) {
                 $packages[][$id] = $q;
               }
             } else {
               $mod = $qty%$get_pakage;
               $div = ($qty - $mod)/$get_pakage+(($mod != 0)?1:0);
               $ost = 0;
               $ord_q = $order_qtys['last'];

               if($div > 1){
                   for ($i=0; $i < $div; $i++) {
                       foreach ( $ord_q as $id => $q) {
                          if($q <= 0) continue;
                           if($q == $get_pakage || $q > $get_pakage){
                                $packages[][$id] = $get_pakage;
                               $ord_q[$id] = $q - $get_pakage;
                           } else {
                             $packages[][$id] = $q;
                             $ord_q[$id] = $q - $get_pakage;
                           }
                       }
                   }
               } else {
                   foreach ( $ord_q as $id => $q) {
                        $packages[][$id] = $q;
                   }
               }
             }
          } else {

            if(!empty($get_pakage) && $get_pakage < $qty) {
              $is = 0;

              $nead = $get_pakage;
              $last_item = '';
              $last_qty = 0;
              $packages = array();
              $i = 0;
              foreach ($order_qtys['last'] as $id => $item) {

                  if($is != $nead){
                      if($is == 0 && $item == $nead){
                          $packages[$i][$id] = $item;
                      } elseif($item != $nead && $is < $nead) {

                        if($item > ($nead-$is)){
                          if($is != 0){
                            $i--;
                          }
                          $packages[$i][$id] = $nead-$is;
                          $lasts_items = $item - ($nead-$is);
                          $mod = $lasts_items%$get_pakage;
                          $div = ($lasts_items - $mod)/$get_pakage;
                          if($div > 0){
                            for ($j=0; $j < $div; $j++) {
                              $i++;
                              $packages[$i][$id] = $get_pakage;
                            }
                          }
                          if($mod > 0){
                            $i++;
                            $packages[$i][$id] = $mod;
                            $is = $mod;
                          } else {
                            $is = 0;
                          }
                        } else {
                          if($is != 0){
                            $i--;
                          }
                          $packages[$i][$id] = $packages[$i][$id]+$item;
                          $is += $item;
                          if($is == $nead){
                            $is = 0;
                          }
                        }
                      }
                      $i++;
                  }
              }
            }
          }

          if(!empty($packages) && count($packages) > 1){
            foreach ($packages as $package) {
              $shipment[] = $this->createShipping($order_id, $package);
            }
          } else {
              if(empty($packages)){
                $packages = $order_qtys['last'];
              }
              $shipment[] = $this->createShipping($order_id, $packages);
          }
          $ship_ids = $shipment;
        }

        Mage::log(print_r($packages,true),null,'shipcloud_system.log',true);

      $order_ids = $order_id;
      $ship_id = implode(':',$ship_ids);

    $this->_redirect($rva_redirect, array('order_id' => $order_ids, 'shipment_id' => $ship_id, 'type' => 'shipment', 'carrier' => $order_items['shipcloud_create']));
    return;
  }

    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($shipment)
            ->addObject($shipment->getOrder())
            ->save();

        return $this;
    }

    protected function _initShipment()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Shipments'));

        $shipment = false;
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $orderId = $this->getRequest()->getParam('order_id');
        if ($shipmentId) {
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
        } elseif ($orderId) {
            $order      = Mage::getModel('sales/order')->load($orderId);

            /**
             * Check order existing
             */
            if (!$order->getId()) {
                $this->_getSession()->addError($this->__('The order no longer exists.'));
                return false;
            }
            /**
             * Check shipment is available to create separate from invoice
             */
            if ($order->getForcedDoShipmentWithInvoice()) {
                $this->_getSession()->addError($this->__('Cannot do shipment for the order separately from invoice.'));
                return false;
            }
            /**
             * Check shipment create availability
             */
            if (!$order->canShip()) {
                $this->_getSession()->addError($this->__('Cannot do shipment for the order.'));
                return false;
            }
            $savedQtys = $this->_getItemQtys();
            $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys);

            $tracks = $this->getRequest()->getPost('tracking');
            if ($tracks) {
                foreach ($tracks as $data) {
                    if (empty($data['number'])) {
                        Mage::throwException($this->__('Tracking number cannot be empty.'));
                    }
                    $track = Mage::getModel('sales/order_shipment_track')
                        ->addData($data);
                    $shipment->addTrack($track);
                }
            }
        }

        $order_items = $this->getRequest()->getParam('shipment');

        if($order && $order_items['shipcloud_create'])
        {
            $order->setShipcloudCreate($order_items['shipcloud_create']);
            $order->save();
        }

        Mage::register('current_shipment', $shipment);
        return $shipment;
    }

    private function createShipping($order_id, $qty){

      try {
          if ($shipment = $this->_initShipmentByQty($order_id,$qty)) {

              $shipment->register();

              $comment = '';

              $rva_redirect ='*/sales_order/view';
              $data = $this->getRequest()->getParam('shipment');
              $comment = '';
              if (!empty($data['comment_text'])) {
                  $shipment->addComment(
                      $data['comment_text'],
                      isset($data['comment_customer_notify']),
                      isset($data['is_visible_on_front'])
                  );
                  if (isset($data['comment_customer_notify'])) {
                      $comment = $data['comment_text'];
                  }
              }
              if (!empty($data['send_email'])) {
                  $shipment->setEmailSent(true);
              }

              $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));

              $this->_saveShipment($shipment);

              $shipment->sendEmail(!empty($data['send_email']), $comment);

              Mage::getSingleton('adminhtml/session')->getCommentText(true);


              return $shipment->getId();
          } else {
              $this->_forward('noRoute');
              return;
          }
      } catch (Mage_Core_Exception $e) {
          $this->_getSession()->addError($e->getMessage());
      } catch (Exception $e) {
          $this->_getSession()->addError($this->__('Cannot save shipment.'));
      }
    }
}
