<?php

/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php

class Webmeridian_Order_Adminhtml_Sales_ShipcloudController extends Mage_Adminhtml_Controller_Action
{
    protected $qty = array();
    protected function _initShipmentByQty($order_id,$qtys = null)
    {
        $this->_title($this->__('Sales'))->_title($this->__('Shipments'));


        $orderId = $order_id;

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
      $order_ids = $this->getRequest()->getParam('order_ids');
      $order_qtys = array();
      $ship_ids = array();
      $rva_redirect = 'shipcloud/adminhtml_shipcloud/intermediate/';
      foreach ($order_ids as $order_id) {
        $order = Mage::getModel("sales/order")->load($order_id);
        //$order_id = $id;
        $order_qtys = array();
        foreach ($order->getAllItems() as $item) {
          $order_qtys[$item->getItemId()] = $item->getQtyOrdered();
          $qty += $item->getQtyOrdered();
        }

        $get_pakage = Mage::getStoreConfig('shipcloud/pcarrier_group/default_units',Mage::app()->getStore());

        if(empty($get_pakage) || $get_pakage >= $qty){
          //echo 'heare';exit;
          foreach ( $order_qtys as $id => $q) {
            //var_dump($id.'---'.$q);
            //var_dump($ord_q);

                $shipment[] = $this->createShipping($order_id, array($id => $q));

              //var_dump($ord_q);
          }
          /*try {
              if ($shipment = $this->_initShipmentByQty($order_id , $qty)) {
                  $shipment->register();
                  $comment = '';

                  $this->_saveShipment($shipment);

                  $this->_getSession()->addSuccess($this->__('The shipment has been created.'));
                  Mage::getSingleton('adminhtml/session')->getCommentText(true);

                  $ship_ids[] = $shipment->getId();

              } else {
                  $this->_forward('noRoute');
                  return;
              }
          } catch (Mage_Core_Exception $e) {
              $this->_getSession()->addError($e->getMessage());
          } catch (Exception $e) {
              $this->_getSession()->addError($this->__('Cannot save shipment.'));
          }*/
          $ship_ids = $shipment;
        } else {

            $mod = $qty%$get_pakage;
            $div = ($qty - $mod)/$get_pakage+(($mod != 0)?1:0);
            $ost = 0;
            $ord_q = $order_qtys;
            //var_dump($ord_q);
            if($div > 1){

              for ($i=0; $i < $div; $i++) {
                foreach ( $ord_q as $id => $q) {
                  //var_dump($id.'---'.$q);
                  //var_dump($ord_q);
                    if($q == $get_pakage){
                        $shipment[] = $this->createShipping($order_id, array($id => $get_pakage));
                    } elseif($q - $get_pakage >= 0){
                      $shipment[] = $this->createShipping($order_id, array($id => $get_pakage));
                      //$ost = $q - $get_pakage;
                      $ord_q[$id] = $q - $get_pakage;
                    }
                    //var_dump($ord_q);
                }
                  //$shipment[] = $this->createShipping($order_id, array(,$get_pakage));
              }
            } else {

              foreach ( $ord_q as $id => $q) {
                //var_dump($id.'---'.$q);
                //var_dump($ord_q);

                    $shipment[] = $this->createShipping($order_id, array($id => $q));

                  //var_dump($ord_q);
              }
            }

            $ship_ids = $shipment;

        }
      }
      $order_ids = implode(':',$order_ids);
      $ship_id = implode(':',$ship_ids);

      $this->_redirect($rva_redirect, array('order_id' => $order_ids, 'shipment_id' => $ship_id, 'type' => 'shipment'));
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
    /*public function indexAction(){


      var_dump($this->getRequest()->getParams());
      $response = Mage::getModel('shipcloud/shipcloud')->load($this->getRequest()->getParam('shipment_id'), 'shipment_id');
      //$ids = $this->_validateOrderCloud();
      if($this->_validateOrderCloud()){


          //$this->_redirectUrl($this->getUrl('adminhtml/sales_order/index/'));
      }
      if (count($response->getData()) == 0) {
          $this->loadLayout();
          $order_id = $this->getRequest()->getParam('order_id');
          $type = $this->getRequest()->getParam('type');
          $this->imOrder = Mage::getModel('sales/order')->load($order_id);
          Mage::register('order', $this->imOrder);
          $shippingAddress = $this->imOrder->getShippingAddress();
          Mage::register('shipTo', $shippingAddress);

          $shipment_id = $this->getRequest()->getParam('shipment_id');
          $this->imShipment = Mage::getModel('sales/order_shipment')->load($shipment_id);
          Mage::register('shipment', $this->imShipment);
          Mage::register('type', $type);
          $currentCarrier = Mage::getStoreConfig('shipcloud/profile/defaultcarrier');
          Mage::register('carierServiceHtml', $this->getCarrierServiceHtml($currentCarrier));
          $shipmentAllItems = $this->imShipment->getAllItems();
          $totalPrice = 0;
          $totalWight = 0;
          $totalShipmentQty = 0;
          foreach ($shipmentAllItems AS $item) {
              $itemData = $item->getData();
              $totalPrice += $itemData['price'] * $itemData['qty'];
              $totalWight += $itemData['weight'] * $itemData['qty'];
              $totalShipmentQty += $itemData['qty'];
          }
          Mage::register('shipmentTotalWeight', $totalWight);
          $ship_method = $this->getRequest()->getParam('carrier', 0);
          Mage::register('shipMethod', $ship_method);

          $this->renderLayout();
      } else {
          $this->_redirectUrl($this->getUrl('adminhtml/sales_order/index/'));
      }


    }*/
    private function createShipping($order_id, $qty){
      try {
          if ($shipment = $this->_initShipmentByQty($order_id,$qty)) {
              $shipment->register();

              $comment = '';

              $rva_redirect ='*/sales_order/view';

              $this->_saveShipment($shipment);

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

    public function getCarrierServiceHtml($type = 'other', $withJs = true)
    {
        $order = Mage::getModel("sales/order")->load($this->getRequest()->getParam('order_id'));
        $currentCarrier = '';
        $currentCarrierOriginal = $order->getShippingMethod();

        if($currentCarrierOriginal == "flat_rate"){
          $currentCarrier = '';
        }else{
          $currentShippingMethod = str_replace('sp_shipcloud_carrier_', '', $currentCarrierOriginal);
          $arrCurrentShippingMethod = explode('_', $currentShippingMethod);

          if(isset($arrCurrentShippingMethod[0])){
            unset($arrCurrentShippingMethod[0]);
          }
          $currentCarrier = implode('_', $arrCurrentShippingMethod);
        }

        if($currentCarrier == ''){
          $currentCarrier = Mage::getStoreConfig('shipcloud/profile/defaultcarriertype');
        }else{
          switch ($currentCarrier) {
              case 'standard':
                  $currentCarrier = 'standard';
                  break;

              case 'express':
                  $currentCarrier = 'one_day';
                  break;

              case 'express_save':
                  $currentCarrier = 'one_day_early';
                  break;
          }
        }

        $arrOptions = array();
        switch ($type) {
            case 'UPS':
                $arrOptions = array(
                    'standard' => 'standard',
                    'one_day' => 'Express',
                    'one_day_early' => 'Express Saver',
                );
                break;

            case 'DHL':
                $arrOptions = array(
                    'standard' => 'standard',
                    'one_day' => 'Express',
                );

                break;

            case 'DPD':
                $arrOptions = array(
                    'standard' => 'standard',
                    'one_day' => 'Express',
                    'one_day_early' => 'Express Saver',
                );

                break;

            default:
                $arrOptions = array(
                    'standard' => 'standard',
                );
                break;
        }

        $html = '';

        if ($withJs) {
            $html .= '<select id="default_carrier_type" name="carrier_service">';
            foreach ($arrOptions as $value => $label):
                $selected = $currentCarrier == $value ? 'selected' : '';
                $html .= "<option value='$value' $selected>$label</option>";
            endforeach;
            $html .= '</select>';
            $html .= '<script type="text/javascript">'
                    . 'var upsOptions = "' . $this->getCarrierServiceHtml('UPS', false) . '";'
                    . 'var dhlOptions = "' . $this->getCarrierServiceHtml('DHL', false) . '";'
                    . 'var dpdOptions = "' . $this->getCarrierServiceHtml('DPD', false) . '";'
                    . 'var defOptions = "' . $this->getCarrierServiceHtml('other', false) . '";'
                    . 'jQuery("#carrier").on("change", function(e){'
                    . 'e.preventDefault();'
                    . 'var options = "";'
                    . 'switch (jQuery(this).val()){'
                    . 'case "UPS":'
                    . 'options = upsOptions;'
                    . 'break;'
                    . 'case "DHL":'
                    . 'options = dhlOptions;'
                    . 'break;'
                    . 'case "DPD":'
                    . 'options = dpdOptions;'
                    . 'break;'
                    . 'default:'
                    . 'options = defOptions;'
                    . 'break;'
                    . ''
                    . ''
                    . '}'
                    . 'jQuery("#default_carrier_type").html(options);'
                    . '});'
                    . '</script>';
        }else {
            foreach ($arrOptions as $value => $label):
                $selected = $currentCarrier == $value ? 'selected' : '';
                $html .= "<option value='$value' $selected>$label</option>";
            endforeach;
        }
        return $html;
    }

    public function savelabelAction()
    {
        $type = $this->getRequest()->getParam('type');
        $shipment_id = $this->getRequest()->getParam('shipment_id');

        $orderIds = $this->_getHelper()->getOrderCloudIds();
        foreach ($orderIds as $orderId) {
            $params = $this->getRequest()->getParams();

            $this->loadLayout();
            $Label = Mage::getModel('shipcloud/shipcloud');

            $labelLocal = $Label->load($params['shipment_id'], 'shipment_id');

            $labelData = $labelLocal->getData();

            if (empty($labelData)) {
                $params['service'] = $params['carrier_service'];
                $responseLabel = $Label->ShipTo($params);
                if (empty($responseLabel['error'])) {
                    $labelLocalModel = Mage::getModel('shipcloud/shipcloud');
                    $labelLocalModel->setTitle("Order Id: " . $orderId . " Shipment Id: " . $params['shipment_id'] . " Tracking number: " . $responseLabel['response']['carrier_tracking_no']);
                    $labelLocalModel->setOrderId($orderId);
                    $labelLocalModel->setTrackingnumber($responseLabel['response']['carrier_tracking_no']);
                    $labelLocalModel->setTrackingurl($responseLabel['response']['tracking_url']);
                    $labelLocalModel->setResponseId($responseLabel['response']['id']);
                    $labelLocalModel->setLabelimg($responseLabel['image']['image_name']);
                    $labelLocalModel->setLabelname($responseLabel['image']['origin_name']);
                    $labelLocalModel->setLabelurl($responseLabel['response']['label_url']);
                    $params['returnlabelcreating'] == "1" ? $labelLocalModel->setReturnLabelUrl('1') : null;
                    $labelLocalModel->setCreatedTime(Date("Y-m-d H:i:s"));
                    $labelLocalModel->setUpdateTime(Date("Y-m-d H:i:s"));
                    $labelLocalModel->setShipmentId($params['shipment_id']);
                    $labelLocalModel->setType($type);
                    $labelLocalModel->setPrice($responseLabel['response']['price']);

                    $labelLocalModel->save();
                    $labelLocal = $labelLocalModel;

    		            $responseId = $responseLabel['response']['id'];

                    if ($params['addtrack'] == 1 && $type == 'shipment') {

                        $trTitle = $params['carrier'];
                        $shipment = Mage::getModel('sales/order_shipment')->load($params['shipment_id']);
                        $track = Mage::getModel('sales/order_shipment_track')
                                ->setNumber(trim($responseLabel['response']['carrier_tracking_no']))
                                ->setCarrierCode(strtolower('custom'))
                                ->setTitle($trTitle);
                        $shipment->addTrack($track);

                        $shipment->save();
                    }

            				if(isset($params['pickup_requests']) && $params['pickup_requests'] == 1){
              					if(date('w') == 6){
              						$pickup_date = date('Y/m/d', strtotime(date("Y/m/d")) + 2 * 60 * 60 * 24);
              					}else if(date('w') == 5){
              						$pickup_date = date('Y/m/d', strtotime(date("Y/m/d")) + 3 * 60 * 60 * 24);
              					}else{
              						$pickup_date = date('Y/m/d', strtotime(date("Y/m/d")) + 60 * 60 * 24);
              					}

              					$params['pickup_date'] = $pickup_date;
              					$params['service'] = $params['carrier_service'];
              					$params['response_id'] = $responseId;
              					$responseLabelPickupRequest = $Label->ShipToPickupRequest($params);

              					if (!empty($responseLabelPickupRequest['error'])) {
              						Mage::getSingleton('core/session')->addError(implode(', ', $responseLabelPickupRequest['error']));
              					}else{
              						$result = "Pickup request was success!";
              						Mage::getSingleton('core/session')->addSuccess($result);
              					}
            				}
                }else {
            				//$errorMessages = $this->createErrorMessages($responseLabel['error']);
            				//echo $errorMessages;die();

            				$jsonDataArr = json_encode(array('status' => true, 'content' => $errorMessages));
            				echo $jsonDataArr;die();
                    //Mage::getSingleton('core/session')->addError(implode(', ', $responseLabel['error']));
                    //$this->_redirect('shipcloud/adminhtml_shipcloud/intermediate/order_id/' . $this->getRequest()->getParam('order_id') . '/shipment_id/' . $this->getRequest()->getParam('shipment_id'));
                }
            }

            $labelReturn = Mage::getModel('shipcloud/shipcloud')->getCollection()
                    ->addFieldToFilter('shipment_id', $params['shipment_id'])
                    ->addFieldToFilter('return_label_url', 'return_label')
                    ->getFirstItem();

            $labelReturnData = $labelReturn->getData();

            if (empty($labelReturnData) && $params['returnlabelcreating'] == "1") {
                $params['service'] = 'returns';
                $responseLabelReturn = Mage::getModel('shipcloud/shipcloud')->ShipTo($params);
                if (empty($responseLabelReturn['error'])) {
                    $labelReturnModel = Mage::getModel('shipcloud/shipcloud');
                    $labelReturnModel->setTitle("[Return Label] Order Id: " . $orderId . " Shipment Id: " . $params['shipment_id'] . " Tracking number: " . $responseLabelReturn['response']['carrier_tracking_no']);
                    $labelReturnModel->setOrderId($orderId);
                    $labelReturnModel->setTrackingnumber($responseLabelReturn['response']['carrier_tracking_no']);
                    $labelReturnModel->setTrackingurl($responseLabelReturn['response']['tracking_url']);
                    $labelReturnModel->setResponseId($responseLabelReturn['response']['id']);
                    $labelReturnModel->setLabelimg($responseLabelReturn['image']['image_name']);
                    $labelReturnModel->setLabelname($responseLabelReturn['image']['origin_name']);
                    $labelReturnModel->setLabelurl($responseLabelReturn['response']['label_url']);
                    $labelReturnModel->setReturnLabelUrl('return_label');
                    $labelReturnModel->setCreatedTime(Date("Y-m-d H:i:s"));
                    $labelReturnModel->setUpdateTime(Date("Y-m-d H:i:s"));
                    $labelReturnModel->setShipmentId($params['shipment_id']);
                    $labelReturnModel->setType($type);
                    $labelReturnModel->setPrice($responseLabelReturn['response']['price']);
                    $labelReturnModel->save();
                    $labelReturn = $labelReturnModel;
                    $responseId = $responseLabelReturn['response']['id'];
                }
            }

            Mage::register('model', $labelLocal);
            Mage::register('model2', $labelReturn);
            Mage::register('backLink', $this->getUrl('adminhtml/sales_order_shipment/view/shipment_id/' . $shipment_id));
            Mage::register('error', 'check if need');
            $this->renderLayout();
        }
        $this->_redirectUrl($this->getUrl('adminhtml/sales_order/index/'));

    }

    protected function _validateOrderCloud()
    {
        $error = false;
        $productIds = $this->_getHelper()->getOrderCloudIds();
        //var_dump($productIds);exit;
        if (!is_array($productIds)) {
            $error = $this->__('Please select products for attributes update');
        }

        if ($error) {
            $this->_getSession()->addError($error);
            //$this->_redirect('*/catalog_product/', array('_current'=>true));
        }

        return $error;
    }
    protected function _getHelper()
    {
        return Mage::helper('webmeridian_shipcloud');
    }
}
