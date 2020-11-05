<?php

/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php

class Stork_Shipcloud_Adminhtml_ShipcloudController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        $this->loadLayout()
                ->_setActiveMenu('Shipcloud/items')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }
    public function emptyAction()
    {
        $this->_initAction()->renderLayout();
    }

    public function showlabelAction()
    {
        $type = $this->getRequest()->getParam('type');
        $shipment_id = $this->getRequest()->getParam('shipment_id');

        $all_params = $this->getRequest()->getParams();
        $order_ids = explode(':',$all_params['order_id']);
        $labelLocals = array();
        $labelReturns = array();

        $this->loadLayout();

        foreach ($order_ids as $order_id) {

          $shipment_ids = explode(':',$all_params['shipment_id']);

          $order = Mage::getModel('sales/order')->load($order_id);
          $shipment = $order->getShipmentsCollection();
          foreach ($shipment as $ship_id) {

              $id = $ship_id->getId();
              if(in_array($id,$shipment_ids)){
                $Label = Mage::getModel('shipcloud/shipcloud');
                $labelLocal = $Label->load($id, 'shipment_id');
                if(!empty($all_params['params'])){
                  $params = $all_params;
                  $params = array_merge($params,$all_params['params'][$order_id]);
                  unset($params['params']);
                  $params['order_id'] = $order_id;
                  $params['shipment_id'] = $id;

                  if(!empty($params['conf_weight'][$id])){
                    $params = array_merge($params,$params['conf_weight'][$id]);
                    unset($params['conf_weight']);
                  }

                }else{
                  $params = $all_params;
                }

                $labelData = $labelLocal->getData();
                if (empty($labelData)) {
                    $params['service'] = $params['carrier_service'];
                    $responseLabel = $Label->ShipTo($params);
                    if (empty($responseLabel['error'])) {
                        $headers = array(
                            'Accept: application/json',
                            'Content-Type: application/json',
                            'Affiliate-ID: plugin.magento.4JcAix8R',
                        );
                        $strAccessType = Mage::getStoreConfig('shipcloud/profile/testing') == 1 ? 'test' : 'live';
                        $apiKey = '';
                        if ($strAccessType === 'test') {
                            $apiKey = Mage::getStoreConfig('shipcloud/profile/shipcloud_sandbox_key');
                        } elseif ($strAccessType === 'live') {
                            $apiKey = Mage::getStoreConfig('shipcloud/profile/shipcloud_production_key');
                        }
                        $url = 'https://api.shipcloud.io/v1/shipments/'.$responseLabel['response']['id'];
                        $handle = curl_init();
                        curl_setopt($handle, CURLOPT_URL, $url);
                        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($handle, CURLOPT_USERPWD, $apiKey);
                        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
                        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($handle, CURLOPT_TIMEOUT, 30);
                        curl_setopt($handle, CURLOPT_HTTPGET, true);

                        $responseJson = curl_exec($handle);
                        curl_close($handle);
                        $decodedResponse = json_decode($responseJson, true);

                        $status = $decodedResponse['packages']['0']['tracking_events']['0']['status'];
                        $carrier = $decodedResponse['carrier'];

                        $labelLocalModel = Mage::getModel('shipcloud/shipcloud');
                        $labelLocalModel->setTitle("Order Id: " . $order_id . " Shipment Id: " . $id . " Tracking number: " . $responseLabel['response']['carrier_tracking_no']);
                        $labelLocalModel->setOrderId($order_id);
                        $labelLocalModel->setTrackingnumber($responseLabel['response']['carrier_tracking_no']);
                        $labelLocalModel->setTrackingurl($responseLabel['response']['tracking_url']);
                        $labelLocalModel->setResponseId($responseLabel['response']['id']);
                        $labelLocalModel->setLabelimg($responseLabel['image']['image_name']);
                        $labelLocalModel->setLabelname($responseLabel['image']['origin_name']);
                        $labelLocalModel->setLabelurl($responseLabel['response']['label_url']);
                        $params['returnlabelcreating'] == "1" ? $labelLocalModel->setReturnLabelUrl('1') : null;
                        $labelLocalModel->setCreatedTime(Date("Y-m-d H:i:s"));
                        $labelLocalModel->setUpdateTime(Date("Y-m-d H:i:s"));
                        $labelLocalModel->setShipmentId($id);
                        $labelLocalModel->setType($type);
                        $labelLocalModel->setPrice($responseLabel['response']['price']);
                        $labelLocalModel->setShippingStatus($status);
                        $labelLocalModel->setShippingCarrier($carrier);

                        $labelLocalModel->save();
                        $labelLocal = $labelLocalModel;
                        $labelLocals[$id] = $labelLocal;

                        $responseId = $responseLabel['response']['id'];

                        if ($params['addtrack'] == 1 && $type == 'shipment') {

                            $trTitle = $params['carrier'];
                            $shipment = Mage::getModel('sales/order_shipment')->load($id);
                            $track = Mage::getModel('sales/order_shipment_track')
                                    ->setNumber(trim($responseLabel['response']['carrier_tracking_no']))
                                    ->setCarrierCode(strtolower('custom'))
                                    ->setTitle($trTitle);
                            $shipment->addTrack($track);
                            $shipment->save();
                            if($shipment->getEmailSent()){
                                $shipment->getOrder()->setCustomerNoteNotify(true);
                                $shipment->sendEmail(true);
                            }
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
                            $result = Mage::helper('shipcloud')->__("Pickup request was success!");
                            Mage::getSingleton('core/session')->addSuccess($result);
                          }
                        }
                    }else {
                        $errorMessages = $this->createErrorMessages($responseLabel['error']);
                        $jsonDataArr = json_encode(array('status' => true, 'content' => $errorMessages));
                        echo $jsonDataArr;die();

                    }
                } else {
                    $labelLocals[$id] = $labelLocal;
                }

                $labelReturn = Mage::getModel('shipcloud/shipcloud')->getCollection()
                        ->addFieldToFilter('shipment_id', $id)
                        ->addFieldToFilter('return_label_url', 'return_label')
                        ->getFirstItem();


                $labelReturnData = $labelReturn->getData();

                if (empty($labelReturnData) && $params['returnlabelcreating'] == "1") {
                    $params['service'] = 'returns';
                    $responseLabelReturn = Mage::getModel('shipcloud/shipcloud')->ShipTo($params);
                    if (empty($responseLabelReturn['error'])) {
                      $headers = array(
                          'Accept: application/json',
                          'Content-Type: application/json',
                          'Affiliate-ID: plugin.magento.4JcAix8R',
                      );
                      $strAccessType = Mage::getStoreConfig('shipcloud/profile/testing') == 1 ? 'test' : 'live';
                      $apiKey = '';
                      if ($strAccessType === 'test') {
                          $apiKey = Mage::getStoreConfig('shipcloud/profile/shipcloud_sandbox_key');
                      } elseif ($strAccessType === 'live') {
                          $apiKey = Mage::getStoreConfig('shipcloud/profile/shipcloud_production_key');
                      }
                      $url = 'https://api.shipcloud.io/v1/shipments/'.$responseLabel['response']['id'];
                      $handle = curl_init();
                      curl_setopt($handle, CURLOPT_URL, $url);
                      curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
                      curl_setopt($handle, CURLOPT_USERPWD, $apiKey);
                      curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                      curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
                      curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
                      curl_setopt($handle, CURLOPT_TIMEOUT, 30);
                      curl_setopt($handle, CURLOPT_HTTPGET, true);

                      $responseJson = curl_exec($handle);
                      curl_close($handle);
                      $decodedResponse = json_decode($responseJson, true);
                        $labelReturnModel = Mage::getModel('shipcloud/shipcloud');
                        $labelReturnModel->setTitle("[Return Label] Order Id: " . $order_id . " Shipment Id: " . $id . " Tracking number: " . $responseLabelReturn['response']['carrier_tracking_no']);
                        $labelReturnModel->setOrderId($order_id);
                        $labelReturnModel->setTrackingnumber($responseLabelReturn['response']['carrier_tracking_no']);
                        $labelReturnModel->setTrackingurl($responseLabelReturn['response']['tracking_url']);
                        $labelReturnModel->setResponseId($responseLabelReturn['response']['id']);
                        $labelReturnModel->setLabelimg($responseLabelReturn['image']['image_name']);
                        $labelReturnModel->setLabelname($responseLabelReturn['image']['origin_name']);
                        $labelReturnModel->setLabelurl($responseLabelReturn['response']['label_url']);
                        $labelReturnModel->setReturnLabelUrl('return_label');
                        $labelReturnModel->setCreatedTime(Date("Y-m-d H:i:s"));
                        $labelReturnModel->setUpdateTime(Date("Y-m-d H:i:s"));
                        $labelReturnModel->setShipmentId($id);
                        $labelReturnModel->setType($type);
                        $labelReturnModel->setPrice($responseLabelReturn['response']['price']);
                        $labelLocalModel->setShippingStatus($status);
                        $labelLocalModel->setShippingCarrier($carrier);

                        $labelReturnModel->save();
                        $labelReturn = $labelReturnModel;

        		            $responseId = $responseLabelReturn['response']['id'];
                    }

                  }
                  $labelReturns[$id] = $labelReturn;
                  //var_dump($labelReturnData);exit;
              }
          }
        }

        Mage::register('model', $labelLocals);
        Mage::register('model2', $labelReturns);
        Mage::register('backLink', $this->getUrl('adminhtml/sales_order_shipment/view/shipment_id/' . $shipment_id));
        Mage::register('error', 'check if need');

        $this->renderLayout();

    }

  	public function createErrorMessages($arrErrors)
  	{
  		$strErrors = '<ul class="messages">';
  		foreach($arrErrors as $error){
  			$strErrors .= '<li class="error-msg"><ul><li><span>'.$error.'</span></li></ul></li>';
  		}
  		$strErrors .= '</ul>';
  		return $strErrors;
  	}
  	public function sendpickupAction()
    {
  		$params = $this->getRequest()->getParams();

  		$params['service'] = $params['carrier_service'];
  		$params['response_id'] = '';

      Mage::getModel('core/config')->saveConfig('shipcloud/sp_pickup_manualy/pickup_manualy_carrier', $params['carrier']);
      Mage::getModel('core/config')->cleanCache();
  		$Label = Mage::getModel('shipcloud/shipcloud');

  		$responseLabelPickupRequest = $Label->ShipToPickupRequest($params);

  		$currentUrl = Mage::helper('core/url')->getCurrentUrl();

  		if (!empty($responseLabelPickupRequest['error'])) {
  			Mage::getSingleton('core/session')->addError(implode(', ', $responseLabelPickupRequest['error']));
  		}else{
  			$result = Mage::helper('shipcloud')->__("Pickup request was success!");
  			Mage::getSingleton('core/session')->addSuccess($result);
  		}
  	}

    public function intermediateAction()
    {
        $order_ids = explode(':',$this->getRequest()->getParam('order_id'));
        $this->loadLayout();
        $order = array();
        $all_params = $this->getRequest()->getParam('shipment_id');
        $shipment_ids = explode(':',$all_params);
        $response_array = array();
        foreach ($shipment_ids as $shipment_id) {
            $response = Mage::getModel('shipcloud/shipcloud')->load($shipment_id, 'shipment_id');
            if(count($response->getData()) == 0){
                $response_array[$shipment_id] = $response->getData();
            }
        }
        if (count($response_array) > 0) {
            foreach ($order_ids as $order_id) {
                $order[$order_id] = Mage::getModel('sales/order')->load($order_id);
                $shippingAddress[$order_id] = $order[$order_id]->getShippingAddress();
            }
            $currentCarrier = Mage::getStoreConfig('shipcloud/profile/defaultcarrier');
            Mage::register('carierServiceHtml', $this->getCarrierServiceHtml($currentCarrier,false));
            Mage::register('orders', $order);
            Mage::register('shipTo', $shippingAddress);
            $this->renderLayout();
        } else {
            $this->_redirectUrl($this->getUrl('shipcloud/adminhtml_shipcloud/showlabel/order_id/' . $this->getRequest()->getParam('order_id') . '/shipment_id/' . $this->getRequest()->getParam('shipment_id') . '/type/' . $this->getRequest()->getParam('type')));
        }
    }
    public function deletelabelAction()
    {
        $path = Mage::getBaseDir('media') . DS . 'shipcloud' . DS . 'label' . DS;

        $id = $this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();
        $model = Mage::getModel('shipcloud/shipcloud');
        $label = $model->load($id);
        $labelData = $label->getData();

        if (!empty($labelData)) {
            $response = $model->DeleteLabel($label->getResponseId());
            @unlink($path . $label->getLabelname());
            @unlink($path . $label->getLabelimg());
        }

        $this->_redirectUrl($this->getUrl('shipcloud/adminhtml_shipcloud/showlabel/order_id/' . $labelData['order_id'] . '/shipment_id/' . $labelData['shipment_id']));
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

    public function saveAction()
    {
      $order_ids = $this->getRequest()->getParam('order_ids');

      $ships = array();
      $rva_redirect = 'shipcloud/adminhtml_shipcloud/intermediate/';
      $order_items = $this->getRequest()->getParam('shipment');
      if(!is_array($order_ids))
          $order_ids = explode(',',$order_ids);
      foreach ($order_ids as $order_id) {
        $order = Mage::getModel("sales/order")->load($order_id);
        $ship_ids = array();
        $order_qtys = array();
        $qty = 0;
        foreach ($order->getAllItems() as $item) {
          if(!empty((int)$item->getQtyToShip()))
            $order_qtys['last'][$item->getItemId()] = $item->getQtyToShip();
          $order_qtys['shipped'][$item->getItemId()] = $item->getQtyShipped();
          $qty += $order_qtys['last'][$item->getItemId()];
        }

        $get_pakage = Mage::getStoreConfig('shipcloud/pcarrier_group/default_units',Mage::app()->getStore());
        $use_one_if_empty = Mage::getStoreConfig('shipcloud/pcarrier_group/use_one_if_empty', Mage::app()->getStore());

        if(!empty($order_qtys['shipped'])){
          $shipment_ordered = $order->getShipmentsCollection();
          $shipment_ids = array();
          foreach ($shipment_ordered as $ship_id) {
            $shipment_ids[] = $ship_id->getId();
          }
          $ship_ids = array_merge($ship_ids,$shipment_ids);
        }

        if(!empty($order_qtys['last'])){
            if(!$use_one_if_empty){
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
            if($packages){
              foreach ($packages as $package) {
                $shipment[] = $this->createShipping($order_id, $package);
              }
            } else {
                $shipment[] = $this->createShipping($order_id, $order_qtys['last']);
            }
          $ship_ids = array_merge($ship_ids,$shipment);
        }

        $ships = array_merge($ships,$ship_ids);
      }
      $order_ids = implode(':',$order_ids);
      $ship_id = implode(':',$ships);
      $this->_redirect($rva_redirect, array('order_id' => $order_ids, 'shipment_id' => $ship_id, 'type' => 'shipment'));
      return;
    }
    private function createShipping($order_id, $qty)
    {

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

    /*
    * If qty empty, create one shippind
    *
    */
    protected function _initShipmentByQty($order_id,$qtys = null)
    {
        $this->_title($this->__('Sales'))->_title($this->__('Shipments'));


        $orderId = $order_id;

        $order = Mage::getModel('sales/order')->load($orderId);

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
            foreach ($order->getAllItems() as $item) {
                $savedQtys[$item->getId()] = $item->getQtyToShip();
            }
        } else {
            $savedQtys = $qtys;
        }

        try {
          $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys);
        } catch (Exception $e) {
          var_dump($e->getMessage());
        }

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

    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($shipment)
            ->addObject($shipment->getOrder())
            ->save();

        return $this;
    }
    public function updateAction()
    {
        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Affiliate-ID: plugin.magento.4JcAix8R',
        );
        $strAccessType = Mage::getStoreConfig('shipcloud/profile/testing') == 1 ? 'test' : 'live';
        $apiKey = '';
        if ($strAccessType === 'test') {
            $apiKey = Mage::getStoreConfig('shipcloud/profile/shipcloud_sandbox_key');
        } elseif ($strAccessType === 'live') {
            $apiKey = Mage::getStoreConfig('shipcloud/profile/shipcloud_production_key');
        }

        $shipclouds = Mage::getModel('shipcloud/shipcloud')->getCollection();
        $errors = 0;
        $sucsess = 0;
        foreach ($shipclouds as $shipcloud) {
            if(empty($shipcloud->getShippingCarrier()) || empty($shipcloud->getShippingStatus())){
              $url = 'https://api.shipcloud.io/v1/shipments/'.$shipcloud->getResponseId();
              $handle = curl_init();
              curl_setopt($handle, CURLOPT_URL, $url);
              curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
              curl_setopt($handle, CURLOPT_USERPWD, $apiKey);
              curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
              curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
              curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
              curl_setopt($handle, CURLOPT_TIMEOUT, 30);
              curl_setopt($handle, CURLOPT_HTTPGET, true);

              $responseJson = curl_exec($handle);
              curl_close($handle);
              $decodedResponse = json_decode($responseJson, true);

              if(empty($decodedResponse['errors'])){
                $shipcloud->setShippingCarrier($decodedResponse['carrier']);
                $shipcloud->setShippingStatus($decodedResponse['packages']['0']['tracking_events']['0']['status']);
                $shipcloud->save();
                $sucsess++;
              } else {
                $errors++;
              }
            }
        }
        $message = '';
        if(!empty($sucsess)){
          $message .= $sucsess." shipments added; ";
        }
        if(!empty($errors)){
          $message .= $errors." hipments can't find;";
        }
        Mage::getSingleton('core/session')->addSuccess($message);
        $this->_redirectReferer();
    }
}
