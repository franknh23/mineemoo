<?php

/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
require_once('Stork/Shipcloud/Model/fpdf.php');
require_once('Stork/Shipcloud/Model/fpdi.php');

class Stork_Shipcloud_Adminhtml_DolabelsController extends Mage_Adminhtml_Controller_Action
{

     public function indexAction()
    {
        $order_ids = $this->getRequest()->getParam('order_ids');

        if (!is_array($order_ids)) {
            $order_ids = explode(',', $order_ids);
        }

        $packages_per_shipment = Mage::getStoreConfig('shipcloud/sp_package_shipment_settings/packages_per_shipment');
        $units_per_package     = Mage::getStoreConfig('shipcloud/sp_package_shipment_settings/units_per_package');

        foreach($order_ids as $order_id) {

            $shipments = Mage::getModel('sales/order_shipment')->getCollection()->addFieldToFilter('order_id',$order_id);
            $shipment  = $shipments->getFirstItem();

            if($shipment->getId())
            {
                $order = Mage::getModel('sales/order')->load($order_id);

                if($order->getStatus() != 'processing'){
                    continue;
                }

                $total_packages  = 0;
                $count_shipments = 0;
                $all_qty         = 0;

                foreach($order->getAllVisibleItems() as $item){
                    $all_qty += $item->getQtyOrdered();
                }

                //count how many packages we have
                $total_packages  = (int)ceil($all_qty / $units_per_package);
                //count how many packages we have to do ship
                $count_shipments = (int)ceil($total_packages / $packages_per_shipment);

                //get response
                $Label      = Mage::getModel('shipcloud/shipcloud');
                $labelLocal = $Label->load($shipment->getId(), 'shipment_id');
                $labelData  = $labelLocal->getData();

                if (empty($labelData)) {

                    for($i = 1; $i <= $count_shipments; $i++)
                    {
                            //get params
                            $params     = $this->getShipParams($shipment, $order);

                            //get response
                            $Label      = Mage::getModel('shipcloud/shipcloud');

                            $params['service'] = $params['carrier_service'];
                            $responseLabel = $Label->ShipTo($params);
                            //dummy data
                            //$responseLabel = $this->getResponseShipCloud();

                            if (empty($responseLabel['error'])) {
                                $labelLocalModel = Mage::getModel('shipcloud/shipcloud');
                                $labelLocalModel->setTitle("Order Id: " . $order_id . " Shipment Id: " . $shipment->getId() . " Tracking number: " . $responseLabel['response']['carrier_tracking_no']);
                                $labelLocalModel->setOrderId($order_id);
                                $labelLocalModel->setTrackingnumber($responseLabel['response']['carrier_tracking_no']);
                                $labelLocalModel->setTrackingurl($responseLabel['response']['tracking_url']);
                                $labelLocalModel->setResponseId($responseLabel['response']['id']);
                                $labelLocalModel->setLabelimg($responseLabel['image']['image_name']);
                                $labelLocalModel->setLabelname($responseLabel['image']['origin_name']);
                                $labelLocalModel->setLabelurl($responseLabel['response']['label_url']);
                                $labelLocalModel->setReturnLabelUrl('1');
                                $labelLocalModel->setCreatedTime(Date("Y-m-d H:i:s"));
                                $labelLocalModel->setUpdateTime(Date("Y-m-d H:i:s"));
                                $labelLocalModel->setShipmentId($shipment->getId());
                                $labelLocalModel->setType('shipment');
                                $labelLocalModel->setPrice($responseLabel['response']['price']);
                                $labelLocalModel->save();

                                $labelLocal = $labelLocalModel;

                                $responseId = $responseLabel['response']['id'];

                                if ($params['addtrack'] == 1) {
                                    $trTitle = $params['carrier'];
                                    $shipment = Mage::getModel('sales/order_shipment')->load($shipment->getId());
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
                                                Mage::log(json_encode(implode(', ', $responseLabelPickupRequest['error'])), null, "shipcloud.log");
                                        }
                                }
                            }
                            else{
                                Mage::log(json_encode($responseLabel['error']), null, "shipcloud.log");
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
                                $labelReturnModel->setTitle("[Return Label] Order Id: " . $params['order_id'] . " Shipment Id: " . $params['shipment_id'] . " Tracking number: " . $responseLabelReturn['response']['carrier_tracking_no']);
                                $labelReturnModel->setOrderId($params['order_id']);
                                $labelReturnModel->setTrackingnumber($responseLabelReturn['response']['carrier_tracking_no']);
                                $labelReturnModel->setTrackingurl($responseLabelReturn['response']['tracking_url']);
                                $labelReturnModel->setResponseId($responseLabelReturn['response']['id']);
                                $labelReturnModel->setLabelimg($responseLabelReturn['image']['image_name']);
                                $labelReturnModel->setLabelname(basename($responseLabelReturn['response']['label_url']));
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
                     }
                }
            }
        }

        $this->_getSession()->addSuccess($this->__('The labels has been created.'));
        $this->_redirect('adminhtml/sales_order');
    }

    public function getShipParams($shipment,$order){

        $shipTo     = $shipment->getShippingAddress();
        $shipMethod = $order->getShippingMethod();

        $carrier = $shipTo->getCountryId() == 'DE' ? 'DHL' : 'UPS';

        $package_type = '';

        foreach (Mage::getModel('shipcloud/config_defaultpackagetypes')->toOptionArray() AS $M2 => $MV){
            if(Mage::getStoreConfig('shipcloud/profile/defaultpackagetypes')==$MV['value']){
                $package_type = $MV['value'];
            }
        }

        $addressStreet1 = trim($shipTo->getStreet(1));
        $addressStreet2 = trim($shipTo->getStreet(2));
        $addressStreet2 = $addressStreet2 == '' ? 'no' : $addressStreet2;

        $carierServiceHtml = $this->getCarrierServiceHtml($order);

        $shipmentAllItems = $shipment->getAllItems();
        $totalPrice = 0;
        $totalWight = 0;
        $totalShipmentQty = 0;
        foreach ($shipmentAllItems AS $item) {
            $itemData = $item->getData();
            $totalPrice += $itemData['price'] * $itemData['qty'];
            $totalWight += $itemData['weight'] * $itemData['qty'];
            $totalShipmentQty += $itemData['qty'];
        }

        $shipmentTotalWeight = $totalWight;

        $currentCurrency = Mage::app()->getStore()->getBaseCurrencyCode();

        $returnlabelcreating = Mage::getStoreConfig('shipcloud/profile/returnlabelcreating');
        $returnlabelcreating = $returnlabelcreating != null ? $returnlabelcreating : 0;

        $pickup_requests = Mage::getStoreConfig('shipcloud/sp_pickup_requests/pickup_requests_select');
        $pickup_requests = $pickup_requests != null ? $pickup_requests : 0;

        $dpd_saturday_delivery = Mage::getStoreConfig('shipcloud/profile/dpd_saturday_delivery_select');
        $dpd_saturday_delivery = $dpd_saturday_delivery != null ? $dpd_saturday_delivery : 0;

        $dhl_cash_on_delivery = Mage::getStoreConfig('shipcloud/sp_dhl_cash_on_delivery/dhl_cash_on_delivery_select');
        $dhl_cash_on_delivery = $dhl_cash_on_delivery != null ? $dhl_cash_on_delivery : 0;

        $dpd_service_predict = Mage::getStoreConfig('shipcloud/sp_dpd_service_predict/dpd_service_predict_select');
        $dpd_service_predict = $dpd_service_predict != null ? $dpd_service_predict : 0;

        $tracking_change_customer_notification = Mage::getStoreConfig('shipcloud/profile/tracking_change_customer_notification');
        $tracking_change_customer_notification = $tracking_change_customer_notification != null ? $tracking_change_customer_notification : 0;

        $weight = round($shipmentTotalWeight, 1) > 0 ? round($shipmentTotalWeight, 1) : '0.1';

        $data = array(
            'packagingdescription' => Mage::getStoreConfig('shipcloud/profile/packagingdescription'),
            'addtrack' => Mage::getStoreConfig('shipcloud/profile/addtrack'),
            'shipmentdescription' => Mage::helper('adminhtml')->__('Customer') . ': ' . $shipTo->getFirstname() . ' ' . $shipTo->getLastname() . ' ' . Mage::helper('adminhtml')->__('Order Id') . ': ' . $order->getIncrementId(),
            'carrier'=>$carrier,
            'carrier_service'=>$carierServiceHtml,
            'packagetype'=>$package_type,
            'customdutydescription'=>'Gift',
            'returnlabelcreating'=> $returnlabelcreating,
            'pickup_requests'=>$pickup_requests,
            'dpd_saturday_delivery'=>$dpd_saturday_delivery,
            'dhl_cash_on_delivery'=>$dhl_cash_on_delivery,
            'dpd_service_predict'=>$dpd_service_predict,
            'tracking_change_customer_notification'=>$tracking_change_customer_notification,
            'weight'=>(string)0.1,
            'length'=>Mage::getStoreConfig('shipcloud/profile/length'),
            'width'=>Mage::getStoreConfig('shipcloud/profile/width'),
            'height'=>Mage::getStoreConfig('shipcloud/profile/height'),
            'insurancevalue'=>Mage::getStoreConfig('shipcloud/profile/insurrancevalue'),
            'insurancecurrency'=>$currentCurrency,
            'shiptocompanyname'=>strlen($shipTo->getCompany()) > 0 ? $shipTo->getCompany() : $shipTo->getFirstname() . ' ' . $shipTo->getLastname(),
            'shiptofirstname'=>$shipTo->getFirstname(),
            'shiptolastname'=>$shipTo->getLastname(),
            'shiptoattentionname'=>$shipTo->getFirstname() . ' ' . $shipTo->getLastname(),
            'shiptophonenumber'=>$shipTo->getTelephone(),
            'street'=>$addressStreet1,
            'streetno'=>$addressStreet2,
            'shiptocity'=>$shipTo->getCity(),
            'shiptostateprovincecode'=>$shipTo->getRegion(),
            'shiptopostalcode'=>$shipTo->getPostcode(),
            'shiptocountrycode'=>$shipTo->getCountryId(),
            'type'=>'shipment',
        );
        return $data;
    }

    public function getResponseShipCloud(){

        $responseLabel = array(
            'image' => array(
                'image_name'=>'test',
                'origin_name'=>'test',
            ),
            'response' => array(
                'carrier_tracking_no'=>111,
                'tracking_url'=>'test',
                'id'=>111,
                'label_url'=>'test',
                'price'=>'25',
            )
        );

        return $responseLabel;
    }

    public function getCarrierServiceHtml($order){

        $type           = Mage::getStoreConfig('shipcloud/profile/defaultcarrier');
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

        $_currentCarrier = '';

        foreach ($arrOptions as $value => $label):
            if($currentCarrier == $value){
                $_currentCarrier = $value;
                break;
            }
        endforeach;

        $_currentCarrier = $_currentCarrier == '' ? 'standard' : $_currentCarrier;

        return $_currentCarrier;
    }

}
