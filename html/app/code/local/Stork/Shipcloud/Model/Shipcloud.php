<?php

/**
 * Class Stork_Shipcloud_Model_Shipcloud
 */
class Stork_Shipcloud_Model_Shipcloud extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('shipcloud/shipcloud');
    }

    public function getShipmentCost(array $sendData, $parameters = array())
    {
        $data = array();

        if (!empty($sendData)) {
            $data = $this->_getFullData($sendData);
        } else {
            Mage::throwException('Not set send data parameters!');
        }

        //set url for shipment qoutes
        $data['method_url'] = 'interface_post_shipment_quotes_url';

        //unset keys from array, that don`t need
        unset($data['create_shipping_label']);
        unset($data['description']);
        unset($data['reference_number']);

        unset($data['to']['state']);
        unset($data['to']['phone']);
        unset($data['to']['care_of']);
        unset($data['to']['last_name']);
        unset($data['to']['first_name']);
        unset($data['to']['company']);

        unset($data['from']['state']);
        unset($data['from']['phone']);
        unset($data['from']['care_of']);
        unset($data['from']['last_name']);
        unset($data['from']['first_name']);
        unset($data['from']['company']);

        unset($data['package']['type']);

        $data['package']['width'] = Mage::getStoreConfig('shipcloud/profile/width');
        $data['package']['height'] = Mage::getStoreConfig('shipcloud/profile/height');
        $data['package']['length'] = Mage::getStoreConfig('shipcloud/profile/length');

        if (isset($parameters['width']) && !empty($parameters['width'])) {
            $data['package']['width'] = $parameters['width'];
        }

        if (isset($parameters['height']) && !empty($parameters['height'])) {
            $data['package']['height'] = $parameters['height'];
        }

        if (isset($parameters['length']) && !empty($parameters['length'])) {
            $data['package']['length'] = $parameters['length'];
        }

        $response = $this->_sendData($data);

        unset($response['image']);

        return $response;
    }

    /**
     *
     */
    public function ShipTo(array $sendData, $paremeters = array())
    {

        $data = array();

        if (!empty($sendData)) {
            $data = $this->_getFullData($sendData);
        } else {
            Mage::throwException('Not set send data parameters!');
        }

        $response = $this->_sendData($data, $sendData);


        return $response;
    }

	public function ShipToPickupRequest(array $sendData, $paremeters = array())
    {

        $data = array();

        if (!empty($sendData)) {
            $data = $this->_getFullData($sendData);
        } else {
            Mage::throwException('Not set send data parameters!');
        }

        $response = $this->_sendDataPickupRequest($data, $sendData);

        return $response;
    }

	private function _sendDataPickupRequest(array $dataToSend, $parameters = array())
    {
        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Affiliate-ID: plugin.magento.4JcAix8R',
        );

	      $url = 'https://api.shipcloud.io/v1/pickup_requests';

        $arrResponse = array(
            'status' => false,
            'error' => '',
            'response' => null,
        );

    		$dataToSendPickupRequest = array();
    		$dataToSendPickupRequest['carrier'] = strtolower($parameters['carrier']);
    		$dataToSendPickupRequest['pickup_date'] = $parameters['pickup_date'];

    		if (isset($parameters['response_id']) && trim($parameters['response_id']) != '') {
          $dataToSendPickupRequest['shipments'] = array();
    			$dataToSendPickupRequest['shipments'][] = array('id' => $parameters['response_id']);
        }

        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($handle, CURLOPT_USERPWD, $dataToSend['service_access']['api_key']);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_TIMEOUT, 30);
        curl_setopt($handle, CURLOPT_POST, true);

        unset($dataToSend['service_access']);

        curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($dataToSendPickupRequest));
        $responseJson = curl_exec($handle);
        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);
        $decodedResponse = json_decode($responseJson, true);

        if ($code === 200) {
            $arrResponse['status'] = true;
            $arrResponse['response'] = $decodedResponse;
            $this->insertPickupRequest($decodedResponse);

        } else {
            $arrResponse['status'] = false;
            $arrResponse['error'] = $decodedResponse['errors'];
        }

        return $arrResponse;
    }

	private function insertPickupRequest($decodedResponse)
    {
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		if(isset($decodedResponse['shipments']) && !empty($decodedResponse['shipments'])){
			foreach($decodedResponse['shipments'] as $key=>$value){
        $pickup = Mage::getModel('shipcloud/pickup');
        $pickup->setData(array(
          'pickup_id' => $decodedResponse['id'],
          'carrier_pickup_number' => $decodedResponse['carrier_pickup_number'],
          'carrier' => $decodedResponse['carrier'],
          'pickup_date' => date("Y-m-d H:i:s", strtotime($decodedResponse['pickup_date'])),
          'shipment_key' => $value['id'],
          'user_id' => Mage::getSingleton('admin/session')->getUser()->getId()
        ))->save();
			}
		}
	}


    private function _getFullData($params)
    {
        $sipper = array(
            'from' => $this->_getShipperParameters(),
        );
        $serviceAccess = array(
            'service_access' => $this->_getServiceAccessData()
        );
        $createdData = $this->_getAdditionalParamters($params);

        $shipTo = $this->_getShipToParameters($params);
        $details = array(
            'description' => (array_key_exists('customdutydescription', $params)) ? $params['customdutydescription'] : null,
            'reference_number' => (array_key_exists('packagingdescription', $params)) ? $params['packagingdescription'] : null,
            'create_shipping_label' => 'true',
        );

        if ($params['service'] == 'returns') {
            $details['reference_number'] = '123456789';
            unset($details['description']);
        }

        $returnData = array_merge_recursive($serviceAccess, $sipper, $shipTo, $createdData, $details);
        return $returnData;
    }

    private function _getShipperParameters()
    {

        $data = array(
            'company' => Mage::getStoreConfig('shipcloud/shipper/companyname'),
            'first_name' => Mage::getStoreConfig('shipcloud/shipper/firstname'),
            'last_name' => Mage::getStoreConfig('shipcloud/shipper/lastname'),
            'care_of' => Mage::getStoreConfig('shipcloud/shipper/attentionname'),
            'street' => Mage::getStoreConfig('shipcloud/shipper/street'),
            'street_no' => Mage::getStoreConfig('shipcloud/shipper/streetno'),
            'city' => Mage::getStoreConfig('shipcloud/shipper/city'),
            'zip_code' => Mage::getStoreConfig('shipcloud/shipper/postalcode'),
            'state' => Mage::getStoreConfig('shipcloud/shipper/stateprovincecode'),
            'country' => Mage::getStoreConfig('shipcloud/shipper/countrycode'),
            'phone' => Mage::getStoreConfig('shipcloud/shipper/phonenumber')
        );

        return $data;
    }

    private function _getReturnToParameters()
    {

        $data = array(
            'company' => Mage::getStoreConfig('shipcloud/returncompany/companyname'),
            'first_name' => Mage::getStoreConfig('shipcloud/returncompany/firstname'),
            'last_name' => Mage::getStoreConfig('shipcloud/returncompany/lastname'),
            'care_of' => Mage::getStoreConfig('shipcloud/returncompany/attentionname'),
            'street' => Mage::getStoreConfig('shipcloud/returncompany/street'),
            'street_no' => Mage::getStoreConfig('shipcloud/returncompany/streetno'),
            'city' => Mage::getStoreConfig('shipcloud/returncompany/city'),
            'zip_code' => Mage::getStoreConfig('shipcloud/returncompany/postalcode'),
            'state' => Mage::getStoreConfig('shipcloud/returncompany/stateprovincecode'),
            'country' => Mage::getStoreConfig('shipcloud/returncompany/countrycode'),
            'phone' => Mage::getStoreConfig('shipcloud/returncompany/phonenumber')
        );

        return $data;
    }

    private function _getServiceAccessData()
    {

        $strAccessType = Mage::getStoreConfig('shipcloud/profile/testing') == 1 ? 'test' : 'live';
        $apiKey = '';
        if ($strAccessType === 'test') {
            $apiKey = Mage::getStoreConfig('shipcloud/profile/shipcloud_sandbox_key');
        } elseif ($strAccessType === 'live') {
            $apiKey = Mage::getStoreConfig('shipcloud/profile/shipcloud_production_key');
        }
        $returnData = array(
            'acces_type' => $strAccessType,
            'api_key' => $apiKey,
            'password' => Mage::getStoreConfig('shipcloud/profile/shipcloud_account_password')
        );

        return $returnData;
    }

    private function _getShipToParameters($params)
    {
        $configOptions = new Stork_Shipcloud_Model_Config_Options;
        if ($params['service'] == 'returns') {

            $returnData = array(
                'to' => $this->_getReturnToParameters()
            );
        } else {
            $returnData = array(
                'to' => array(
                    'company' => (array_key_exists('shiptocompanyname', $params)) ? $params['shiptocompanyname'] : null,
                    'first_name' => (array_key_exists('shiptofirstname', $params)) ? $params['shiptofirstname'] : null,
                    'last_name' => (array_key_exists('shiptolastname', $params)) ? $params['shiptolastname'] : null,
                    'care_of' => (array_key_exists('shiptoattentionname', $params)) ? $params['shiptoattentionname'] : null,
                    'street' => $params['street'],
                    'street_no' => $params['streetno'],
                    'city' => (array_key_exists('shiptocity', $params)) ? $params['shiptocity'] : null,
                    'zip_code' => (array_key_exists('shiptopostalcode', $params)) ? $params['shiptopostalcode'] : null,
                    'state' => (array_key_exists('shiptostateprovincecode', $params)) ? $configOptions->getProvinceCode($params['shiptostateprovincecode']): null,
                    'country' => (array_key_exists('shiptocountrycode', $params)) ? $params['shiptocountrycode'] : null,
                    'phone' => (array_key_exists('shiptophonenumber', $params)) ? $params['shiptophonenumber'] : null
                ),
            );
        }
        return $returnData;
    }

    private function _getAdditionalParamters($params)
    {

        $carrier = $params['carrier'];
        $service = $params['service'];
        $packType = $params['type'];


        if ($carrier == "UPS"){
            $packType = '';

            if($service == "one_day"){
                $service = "one_day_early";
            }
            elseif ($service == "one_day_early"){
                $service = "one_day";
            }
        }


        if ($service != 'returns') {

            switch ($carrier) {
                case 'FEDEX':
                    $service = 'one_day_early';
                    break;

                case 'LIEFERY':
                    $service = 'same_day';
                    break;

                default:
                    break;
            }
        } else {
//            $carrier = 'UPS';
            $service = 'returns';
        }

        if ((array_key_exists('parcel_letter', $params)) && $params['carrier'] != "DPD" && $params['packagetype'] === "parcel_letter" && $params['carrier'] != "DPAG" ) {
            $packType = $params['packagetype'];
        }

        if ($params['carrier'] === "DPAG" && $params['packagetype'] === "letter"){
            $packageData['type'] = 'letter';
        }
        elseIf($params['carrier'] === "DPAG" && $params['packagetype'] === "books"){
            $packageData['type'] = 'books';
         }
        elseIf($params['carrier'] === "DPAG" && $params['packagetype'] === "parcel_letter"){
            $packageData['type'] = 'parcel_letter';
        }

        $packageData = array(
            'width' => $params['width'],
            'length' => $params['length'],
            'height' => $params['height'],
            'weight' => $params['weight'],
//            'type' => $packType,
        );

        if ($service == 'returns')
            $packageData['description'] = 'retoure';

        if ($packType == 'shipment') {
            if (($params['carrier'] == "DHL" && $params['insurancevalue'] > 0) || ($params['carrier'] == "UPS" && $params['insurancevalue'] > 0)) {

                $insurenceCurrency = isset($params['insurancecurrency']) ? trim($params['insurancecurrency']) : '';
                $packageData['declared_value'] = array(
                    'amount' => $params['insurancevalue'],
                    'currency' => $insurenceCurrency
                );
                $packageData['type'] = '';
            }
            if(
                    $params['carrier'] == "DHL"
                    || $params['carrier'] == "DPD"
                    || $params['carrier'] == "FEDEX"

                    )
                $packageData['type'] = '';



        }

        $returnData = array(
            'service' => $service,
            'carrier' => $carrier,
            'package' => $packageData
        );

        return $returnData;
    }

    private function _sendData(array $dataToSend, $parameters = array())
    {
        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Affiliate-ID: plugin.magento.4JcAix8R',
        );

        $customRequest = '';
        $method = '';


        if (isset($dataToSend['method_url']) && !empty($dataToSend['method_url']))
            $method = $dataToSend['method_url'];
        else
            $method = 'interface_post_shipment_url';

        $url = Mage::getStoreConfig('shipcloud/profile/' . $method);

        $arrResponse = array(
            'status' => false,
            'error' => '',
            'response' => null,
        );
        unset($dataToSend['method_url']);

        if(array_key_exists('carrier', $parameters) && array_key_exists('packagetype', $parameters)){

            if($parameters['carrier']== "DPAG"  && $parameters['packagetype']=="letter")
            {
                if(!isset($dataToSend['package'])){
                    $dataToSend['package'] = array();
                }

                $dataToSend['package'] = array(
                    "weight" => $parameters['weight'],
                    "width" => $parameters['width'],
                    "length" => $parameters['length'],
                    "height" => $parameters['height'],
                    "type" => 'letter'
                );

            }


            if($parameters['carrier']== "DPAG" && $parameters['packagetype']=="books")
            {
                if(!isset($dataToSend['package'])){
                    $dataToSend['package'] = array();
                }

                $dataToSend['package'] = array(
                    "weight" => $parameters['weight'],
                    "width" => $parameters['width'],
                    "length" => $parameters['length'],
                    "height" => $parameters['height'],
                    "type" => 'books'
                );

            }

            if($parameters['carrier']== "DPAG" && $parameters['packagetype']=="parcel_letter")
            {
                if(!isset($dataToSend['package'])){
                    $dataToSend['package'] = array();
                }

                $dataToSend['package'] = array(
                    "weight" => $parameters['weight'],
                    "width" => $parameters['width'],
                    "length" => $parameters['length'],
                    "height" => $parameters['height'],
                    "type" => 'parcel_letter'
                );

            }
        }





        if((array_key_exists('dpd_saturday_delivery', $parameters) &&
      		$parameters['dpd_saturday_delivery'] == 1) &&
      		(trim($dataToSend['service']) == 'one_day' || trim($dataToSend['service']) == 'one_day_early') &&
      		strtolower($dataToSend['carrier']) == 'dpd'
      		){
      		$dataToSend['additional_services'] = array();
      		$dataToSend['additional_services'][] = array("name" => "saturday_delivery");

      		$dataToSend['carrier'] = strtolower($dataToSend['carrier']);
      	}

          if(array_key_exists('dtracking_change_customer_notification', $parameters) &&
              $parameters['tracking_change_customer_notification'] == 1
              ){
              $order = Mage::getModel('sales/order')->load($parameters['order_id']);
              $dataToSend['notification_email'] = $order->getCustomerEmail();
          }



      	if(strtolower($dataToSend['carrier']) == 'dpd' && array_key_exists('dpd_service_predict', $parameters) && $parameters['dpd_service_predict'] == 1){
      		$order = Mage::getModel('sales/order')->load($parameters['order_id']);
      		$store = Mage::getModel('core/store')->load($order->getData('store_id'));

      		if($store->getData('code') == 'default'){
      			$localeCode = Mage::getModel('core/locale')->getLocaleCode();
      		}else{
      			$localeCode = $store->getLocaleCode();
      		}

      		$arrLocaleCode = explode('_', $localeCode);

      		if(!isset($arrLocaleCode[0]) || trim($arrLocaleCode[0]) == ''){
      			$arrResponse['status'] = false;
      			$arrResponse['error'] = array(0 => Mage::helper('shipcloud')->__("Can not getting Language for DPD - Service predict!"));

      			return $arrResponse;
      		}else{
      			$locale = $arrLocaleCode[0];

      			if(!isset($dataToSend['additional_services'])){
      				$dataToSend['additional_services'] = array();
      			}

      			$dataToSend['additional_services'][] = array(
      				"name" => "advance_notice",
      				"properties" => array(
      					"email" => $order->getCustomerEmail(),
      					"language" => trim($locale)

      				)
      			);
      			$dataToSend['carrier'] = strtolower($dataToSend['carrier']);
      		}
      	}


      	if(strtolower($dataToSend['carrier']) == 'dhl' && array_key_exists('dhl_cash_on_delivery', $parameters) && $parameters['dhl_cash_on_delivery'] == 1){

      		$order = Mage::getModel('sales/order')->load($parameters['order_id']);

      		if(!isset($dataToSend['additional_services'])){
      			$dataToSend['additional_services'] = array();
      		}

      		$dataToSend['additional_services'][] = array(
      			"name" => "cash_on_delivery",
      			"properties" => array(
      				"amount" => number_format($order->getBaseGrandTotal(),2),
      				"currency" => $parameters['insurancecurrency'],
      				"bank_account_holder" => Mage::getStoreConfig('shipcloud/sp_dhl_cash_on_delivery/bank_account_holder'),
      				"bank_name" => Mage::getStoreConfig('shipcloud/sp_dhl_cash_on_delivery/bank_name'),
      				"bank_account_number" => Mage::getStoreConfig('shipcloud/sp_dhl_cash_on_delivery/bank_account_number'),
      				"bank_code" => Mage::getStoreConfig('shipcloud/sp_dhl_cash_on_delivery/bank_code')
      			)
      		);
      		$dataToSend['carrier'] = strtolower($dataToSend['carrier']);
      	}

      	if(Mage::getStoreConfig('shipcloud/sp_pakadoo_with_shipcloud/pakadoo_with_shipcloud_select') == 1){
      		$order = Mage::getModel('sales/order')->load($parameters['order_id']);
      		if ($order->getData()){
      			$customerId = $order->getCustomerId();

            $pakadooCollection = Mage::getModel('shipcloud/pakadoo')->load($customerId,'customer_id');

      			if(!empty($pakadooCollection) && !empty($pakadooCollection->getPakadooId()) && trim($pakadooCollection->getPakadooId()) != ''){
      				$pakadooId = $pakadooCollection->getPakadooId();
      				$labelModel = Mage::getModel('shipcloud/shipcloud');
      				$responsePakadooAddress = $labelModel->createPakadooAddress($pakadooId);

      				if (empty($responsePakadooAddress['error']) && isset($responsePakadooAddress['response']['id']) && trim($responsePakadooAddress['response']['id']) != '') {

      					$store = Mage::getModel('core/store')->load($order->getStoreId());
      					$order_date = date('Y-m-d', strtotime($order->getCreatedAt()));

      					$dataToSend['metadata']['pakadoo'] = array(
      						"shop_name" => $store->getName(),
      						"order_number" => $order->getIncrementId(),
      						"order_date" => $order_date,
      						"e_mail_shop" => Mage::getStoreConfig('trans_email/ident_general/email'),
      						"order_total" => array(
      							"amount" => number_format($order->getBaseGrandTotal(),2),
      							"currency" => $parameters['insurancecurrency']
      						)
      					);

      					$dataToSend['to'] = array('id' => $responsePakadooAddress['response']['id']);

      				}else{
      					$arrResponse['status'] = false;
                  				$arrResponse['error'] = $decodedResponse['errors'];
      					return $arrResponse;
      				}
      			}
      		}
      	}
        //Debuging
        $masDataTest = json_encode($dataToSend);
        Mage::log($masDataTest, null, 'pakadoo_log.log', true);

        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($handle, CURLOPT_USERPWD, $dataToSend['service_access']['api_key']);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_TIMEOUT, 30);
        curl_setopt($handle, CURLOPT_POST, true);

        unset($dataToSend['service_access']);

        curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($dataToSend));
        $responseJson = curl_exec($handle);
        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);
        $decodedResponse = json_decode($responseJson, true);

        if ($code === 200) {
            $arrResponse['status'] = true;
            $arrResponse['response'] = $decodedResponse;

            if ($method == 'interface_post_shipment_url'){

                $arrResponse['image'] = $this->labelImageDownloadAndSave($arrResponse['response']['label_url']);
            }
        } else {
            $arrResponse['status'] = false;
            $arrResponse['error'] = $decodedResponse['errors'];
        }

        return $arrResponse;
    }

    public function labelImageDownloadAndSave($strLabelUrl)
    {
        $path = Mage::getBaseDir('media') . DS . 'shipcloud' . DS . 'label' . DS;
        $path_upsdir = Mage::getBaseDir('media') . DS . 'shipcloud' . DS;

        if (!is_dir($path_upsdir)) {
            mkdir($path_upsdir, 0777);
            mkdir($path_upsdir . DS . "label" . DS, 0777);
        }

        $userAgent = Mage::app()->getStore()->getName();

        $filename = explode(".pdf", $strLabelUrl);
        $filename = explode("/", $filename[0]);
        $labelPath = $filename[count($filename) - 1] . '.pdf';

        $labelimg = preg_replace('/pdf$/i', 'jpg', $labelPath);
        $pdfFile = $path . $labelPath;

        print $pdfFile;
        print $strLabelUrl;

        if (file_put_contents($pdfFile, fopen($strLabelUrl, 'r'))) {

            // include if need to use Imagick extention
            if (class_exists("Imagick")) {
                $img = new Imagick($pdfFile . '[0]');
                $img->setImageFormat('jpg');
                $img->setResolution(300, 300);
                $img->setCompression(Imagick::COMPRESSION_JPEG);
                $img->setCompressionQuality(100);

                print 111;
            }

            return array(
                'image_path' => $path . $labelimg,
                'image_name' => $labelimg,
                'origin_name' => $labelPath
            );
        }

        exit();
    }

    public function savePdf($strLabelUrl)
    {
        $path = Mage::getBaseDir('media') . DS . 'shipcloud' . DS . 'label' . DS;
        $path_upsdir = Mage::getBaseDir('media') . DS . 'shipcloud' . DS;

        if (!is_dir($path_upsdir)) {
            mkdir($path_upsdir, 0777);
            mkdir($path_upsdir . DS . "label" . DS, 0777);
        }

        $userAgent = Mage::app()->getStore()->getName();

        $filename = explode(".pdf", $strLabelUrl);
        $filename = explode("/", $filename[0]);
        $labelPath = $filename[count($filename) - 1] . '.pdf';

        $labelimg = preg_replace('/pdf$/i', 'jpg', $labelPath);
        $pdfFile = $path . $labelPath;
        if (file_put_contents($pdfFile, fopen($strLabelUrl, 'r'))) {

            // include if need to use Imagick extention
            if (class_exists("Imagick")) {
                $img = new Imagick($pdfFile . '[0]');
                $img->setImageFormat('jpg');
                $img->setResolution(300, 300);
                $img->setCompression(Imagick::COMPRESSION_JPEG);
                $img->setCompressionQuality(100);
            }

            return array(
                'image_path' => $path . $labelimg,
                'image_name' => $labelimg,
                'origin_name' => $labelPath
            );
        }
    }

    public function DeleteLabel($id)
    {
        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
        );
        $access = $this->_getServiceAccessData();

        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, trim(Mage::getStoreConfig('shipcloud/profile/interface_delete_shipment_url'), '/') . '/' . $id);
        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($handle, CURLOPT_USERPWD, $access['api_key']);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_TIMEOUT, 30);
        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
        $responseJson = curl_exec($handle);
        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);
        $decodedResponse = json_decode($responseJson, true);
//        echo '<pre>';var_dump($decodedResponse);die();
        if ($code === 200) {
            $arrResponse['status'] = true;
            $arrResponse['response'] = $decodedResponse;
        } else {
            $arrResponse['status'] = false;
            $arrResponse['error'] = $decodedResponse['errors'];
        }

        return $arrResponse;
    }

	/**
     *
     */
    public function createPakadooAddress($pakadooId)
    {
		$headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Affiliate-ID: plugin.magento.4JcAix8R',
        );

        $url = 'https://api.shipcloud.io/v1/addresses';

		$dataToSend = array(
            'service_access' => $this->_getServiceAccessData()
        );

        $arrResponse = array(
            'status' => false,
            'error' => '',
            'response' => null,
        );

		$dataToSendPakadooAddress = array();
		$dataToSendPakadooAddress['pakadoo_id'] = $pakadooId;

        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($handle, CURLOPT_USERPWD, $dataToSend['service_access']['api_key']);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_TIMEOUT, 30);
        curl_setopt($handle, CURLOPT_POST, true);

        curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($dataToSendPakadooAddress));
        $responseJson = curl_exec($handle);
        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);
        $decodedResponse = json_decode($responseJson, true);

        if ($code === 200) {
            $arrResponse['status'] = true;
            $arrResponse['response'] = $decodedResponse;
        } else {
            $arrResponse['status'] = false;
            $arrResponse['error'] = $decodedResponse['errors'];
        }

        return $arrResponse;

        $response = $this->_sendData($data, $sendData);
        return $response;
    }

}
