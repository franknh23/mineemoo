<?php

class Stork_Shipcloud_Model_Carrier extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'sp_shipcloud_carrier';

    public function collectRates(
    Mage_Shipping_Model_Rate_Request $request
    )
    {

        $parameters = array(
            "service" => "standard",
            "street" => $request->getDestStreet(),
            "streetno" => 0, 
            "shiptopostalcode" => $request->getDestPostcode(),
            "shiptocity" => $request->getDestCity(),
            "shiptocountrycode" => $request->getDestCountryId(),
            "weight" => $request->getPackageWeight(),
            "length" => $request->getPackageDepth(),
            "width" => $request->getPackageWidth(),
            "height" => $request->getPackageHeight(),
            "type" => "letter",

        );

        if (!Mage::getStoreConfig('shipcloud/' . $this->_code . '/active')) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');
        /* @var $result Mage_Shipping_Model_Rate_Result */

        $result->append($this->_getDhlExpressShippingRate($parameters));
        $result->append($this->_getDhlShippingRate($parameters));
        $result->append($this->_getUpsShippingRate($parameters));
        $result->append($this->_getUpsExpressShippingRate($parameters));
        $result->append($this->_getUpsExpressSaveShippingRate($parameters));
        $result->append($this->_getDpdShippingRate($parameters));
        $result->append($this->_getIloxShippingRate($parameters));
        $result->append($this->_getGlsShippingRate($parameters));
        $result->append($this->_getFedexShippingRate($parameters));
        $result->append($this->_getHermesShippingRate($parameters));
        $result->append($this->_getLieferyShippingRate($parameters));
        //$result->append($this->_getDPAGShippingRate($parameters));

		//var_dump($result);die();

        return $result;
    }

    protected function _getDhlShippingRate($parameters)
    {
        $rate = Mage::getModel('shipping/rate_result_method');
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */

        $rate->setCarrier($this->_code);

        $parameters['carrier'] = 'dhl';
        $shipcloud = Mage::getModel('shipcloud/shipcloud');

        $shipcloudData = $shipcloud->getShipmentCost($parameters);
        $responseShipmentCost = 0;
        
        if ($shipcloudData !== false) {
            $responseShipmentCost = $shipcloudData['response']['shipment_quote']['price'];
        }
        
        $extraAmount = Mage::getStoreConfig('shipcloud/' . $this->_code . '/dhl_extra_cost');
        
        $error = '';
        if (isset($shipcloudData['error']) && !empty($shipcloudData['error'])) {
            $error .= 'SHIPCLOUD  ERROR: ';
            $error .= implode('. ', $shipcloudData['error']);
            Mage::log($error, null, 'shipcloud.log');
            return false;
        }
        
        /**
         * getConfigData(config_key) returns the configuration value for the
         * carriers/[carrier_code]/[config_key]
         */
        $rate->setCarrierTitle($this->getConfigData('title'));

        $rate->setMethod('dhl');
        $rate->setMethodTitle('DHL (shipcloud) ');

        $rate->setPrice($responseShipmentCost + $extraAmount);
        $rate->setCost(0);
        return $rate;
    }

    protected function _getDhlExpressShippingRate($parameters)
    {
        $rate = Mage::getModel('shipping/rate_result_method');
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */

        $rate->setCarrier($this->_code);
        
        $parameters['carrier'] = 'dhl';
        $parameters['service'] = 'one_day';
//        print_r($parameters);die();
        $shipcloud = Mage::getModel('shipcloud/shipcloud');
        $shipcloudData = $shipcloud->getShipmentCost($parameters);
        $responseShipmentCost = 0;
        
        if ($shipcloudData !== false) {
            $responseShipmentCost = $shipcloudData['response']['shipment_quote']['price'];
        }
        
        $extraAmount = Mage::getStoreConfig('shipcloud/' . $this->_code . '/dhl_express_extra_cost');
        $error = '';
        if (isset($shipcloudData['error']) && !empty($shipcloudData['error'])) {
            $error .= 'SHIPCLOUD  ERROR: ';
            $error .= implode('. ', $shipcloudData['error']);
            Mage::log($error, null, 'shipcloud.log');
            return false;
        }
        /**
         * getConfigData(config_key) returns the configuration value for the
         * carriers/[carrier_code]/[config_key]
         */
        $rate->setCarrierTitle($this->getConfigData('title'));

        $rate->setMethod('dhl_express');
        $rate->setMethodTitle('DHL Express (shipcloud) ');

        $rate->setPrice($responseShipmentCost + $extraAmount);
        $rate->setCost(0);

        return $rate;
    }

    protected function _getUpsShippingRate($parameters)
    {
        $rate = Mage::getModel('shipping/rate_result_method');
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */

        $rate->setCarrier($this->_code);
        
        $parameters['carrier'] = 'ups';
        $shipcloud = Mage::getModel('shipcloud/shipcloud');

        $shipcloudData = $shipcloud->getShipmentCost($parameters);
        $extraAmount = Mage::getStoreConfig('shipcloud/' . $this->_code . '/ups_extra_cost');
        $error = '';
        if (isset($shipcloudData['error']) && !empty($shipcloudData['error'])) {
            $error .= 'SHIPCLOUD  ERROR: ';
            $error .= implode('. ', $shipcloudData['error']);
            Mage::log($error, null, 'shipcloud.log');
            return false;
        }
        
        $responseShipmentCost = 0;
        
        if ($shipcloudData !== false) {
            $responseShipmentCost = $shipcloudData['response']['shipment_quote']['price'];
        }
        
        /**
         * getConfigData(config_key) returns the configuration value for the
         * carriers/[carrier_code]/[config_key]
         */
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setPrice($responseShipmentCost + $extraAmount);
        $rate->setMethod('ups');
        $rate->setMethodTitle('UPS (shipcloud) ');

        $rate->setCost(0);

        return $rate;
    }

    protected function _getUpsExpressShippingRate($parameters)
    {
        $rate = Mage::getModel('shipping/rate_result_method');
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */

        $rate->setCarrier($this->_code);
        
        $parameters['carrier'] = 'ups';
        $parameters['service'] = 'one_day_early';
        $shipcloud = Mage::getModel('shipcloud/shipcloud');

        $shipcloudData = $shipcloud->getShipmentCost($parameters);
        $responseShipmentCost = 0;

        if ($shipcloudData !== false) {
            $responseShipmentCost = $shipcloudData['response']['shipment_quote']['price'];
        }
        
        $extraAmount = Mage::getStoreConfig('shipcloud/' . $this->_code . '/ups_express_extra_cost');
        $error = '';
        if (isset($shipcloudData['error']) && !empty($shipcloudData['error'])) {
            $error .= 'SHIPCLOUD  ERROR: ';
            $error .= implode('. ', $shipcloudData['error']);
            Mage::log($error, null, 'shipcloud.log');
            return false;
        }
        /**
         * getConfigData(config_key) returns the configuration value for the
         * carriers/[carrier_code]/[config_key]
         */
        $rate->setCarrierTitle($this->getConfigData('title'));

        $rate->setMethod('ups_express');
        $rate->setMethodTitle('UPS Express (shipcloud) ');

        $rate->setPrice($responseShipmentCost + $extraAmount);
        $rate->setCost(0);

        return $rate;
    }

    protected function _getUpsExpressSaveShippingRate($parameters)
    {
        $rate = Mage::getModel('shipping/rate_result_method');
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */

        $rate->setCarrier($this->_code);
        
        $parameters['carrier'] = 'ups';
        $parameters['service'] = 'one_day';
        $shipcloud = Mage::getModel('shipcloud/shipcloud');

        $shipcloudData = $shipcloud->getShipmentCost($parameters);
        $responseShipmentCost = 0;
        
        if ($shipcloudData !== false) {
            $responseShipmentCost = $shipcloudData['response']['shipment_quote']['price'];
        }
        
        $extraAmount = Mage::getStoreConfig('shipcloud/' . $this->_code . '/ups_express_save_extra_cost');
        $error = '';
        if (isset($shipcloudData['error']) && !empty($shipcloudData['error'])) {
            $error .= 'SHIPCLOUD  ERROR: ';
            $error .= implode('. ', $shipcloudData['error']);
            Mage::log($error, null, 'shipcloud.log');
            return false;
        }
        /**
         * getConfigData(config_key) returns the configuration value for the
         * carriers/[carrier_code]/[config_key]
         */
        $rate->setCarrierTitle($this->getConfigData('title'));

        $rate->setMethod('ups_express_save');
        $rate->setMethodTitle('UPS Express Saver (shipcloud) ');

        $rate->setPrice($responseShipmentCost + $extraAmount);
        $rate->setCost(0);

        return $rate;
    }
    protected function _getDpdShippingRate($parameters)
    {
        $rate = Mage::getModel('shipping/rate_result_method');
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */

        $rate->setCarrier($this->_code);
        
        $parameters['carrier'] = 'dpd';
        $parameters['service'] = 'standard';
        $shipcloud = Mage::getModel('shipcloud/shipcloud');

        $shipcloudData = $shipcloud->getShipmentCost($parameters);
        $responseShipmentCost = 0;
        
        if ($shipcloudData !== false) {
            $responseShipmentCost = $shipcloudData['response']['shipment_quote']['price'];
        }
        
        $extraAmount = Mage::getStoreConfig('shipcloud/' . $this->_code . '/dpd_extra_cost');
        $error = '';
        if (isset($shipcloudData['error']) && !empty($shipcloudData['error'])) {
            $error .= 'SHIPCLOUD  ERROR: ';
            $error .= implode('. ', $shipcloudData['error']);
            Mage::log($error, null, 'shipcloud.log');
            return false;
        }
        /**
         * getConfigData(config_key) returns the configuration value for the
         * carriers/[carrier_code]/[config_key]
         */
        $rate->setCarrierTitle($this->getConfigData('title'));

        $rate->setMethod('dpd');
        $rate->setMethodTitle('DPD (shipcloud) ');

        $rate->setPrice($responseShipmentCost + $extraAmount);
        $rate->setCost(0);

        return $rate;
    }
    protected function _getIloxShippingRate($parameters)
    {
        $rate = Mage::getModel('shipping/rate_result_method');
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */

        $rate->setCarrier($this->_code);
        
        $parameters['carrier'] = 'iloxx';
        $parameters['service'] = 'standard';
        $shipcloud = Mage::getModel('shipcloud/shipcloud');

        $shipcloudData = $shipcloud->getShipmentCost($parameters);
        $responseShipmentCost = 0;
        
        if ($shipcloudData !== false) {
            $responseShipmentCost = $shipcloudData['response']['shipment_quote']['price'];
        }
        
        $extraAmount = Mage::getStoreConfig('shipcloud/' . $this->_code . '/ilox_extra_cost');

        $error = '';
        if (isset($shipcloudData['error']) && !empty($shipcloudData['error'])) {
            $error .= 'SHIPCLOUD  ERROR: ';
            $error .= implode('. ', $shipcloudData['error']);
            Mage::log($error, null, 'shipcloud.log');
            return false;
        }
        /**
         * getConfigData(config_key) returns the configuration value for the
         * carriers/[carrier_code]/[config_key]
         */
        $rate->setCarrierTitle($this->getConfigData('title'));

        $rate->setMethod('iloxx');
        $rate->setMethodTitle('ILOXX (shipcloud) ');

        $rate->setPrice($responseShipmentCost + $extraAmount);
        $rate->setCost(0);

        return $rate;
    }
    protected function _getGlsShippingRate($parameters)
    {
        $rate = Mage::getModel('shipping/rate_result_method');
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */

        $rate->setCarrier($this->_code);
        
        $parameters['carrier'] = 'gls';
        $parameters['service'] = 'standard';
        $shipcloud = Mage::getModel('shipcloud/shipcloud');

        $shipcloudData = $shipcloud->getShipmentCost($parameters);
        $responseShipmentCost = 0;
        
        if ($shipcloudData !== false) {
            $responseShipmentCost = $shipcloudData['response']['shipment_quote']['price'];
        }
        
        $extraAmount = Mage::getStoreConfig('shipcloud/' . $this->_code . '/gls_extra_cost');
        $error = '';
        if (isset($shipcloudData['error']) && !empty($shipcloudData['error'])) {
            $error .= 'SHIPCLOUD  ERROR: ';
            $error .= implode('. ', $shipcloudData['error']);
            Mage::log($error, null, 'shipcloud.log');
            return false;
        }
        /**
         * getConfigData(config_key) returns the configuration value for the
         * carriers/[carrier_code]/[config_key]
         */
        $rate->setCarrierTitle($this->getConfigData('title'));

        $rate->setMethod('gls');
        $rate->setMethodTitle('GLS (shipcloud) ');

        $rate->setPrice($responseShipmentCost + $extraAmount);
        $rate->setCost(0);

        return $rate;
    }
    protected function _getFedexShippingRate($parameters)
    {
        $rate = Mage::getModel('shipping/rate_result_method');
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */

        $rate->setCarrier($this->_code);
        
        $parameters['carrier'] = 'fedex';
        $parameters['service'] = 'standard';
        $shipcloud = Mage::getModel('shipcloud/shipcloud');

        $shipcloudData = $shipcloud->getShipmentCost($parameters);
        $responseShipmentCost = 0;
        
        if ($shipcloudData !== false) {
            $responseShipmentCost = $shipcloudData['response']['shipment_quote']['price'];
        }
        
        $extraAmount = Mage::getStoreConfig('shipcloud/' . $this->_code . '/fedex_extra_cost');
        $error = '';
        if (isset($shipcloudData['error']) && !empty($shipcloudData['error'])) {
            $error .= 'SHIPCLOUD  ERROR: ';
            $error .= implode('. ', $shipcloudData['error']);
            Mage::log($error, null, 'shipcloud.log');
            return false;
        }
        /**
         * getConfigData(config_key) returns the configuration value for the
         * carriers/[carrier_code]/[config_key]
         */
        $rate->setCarrierTitle($this->getConfigData('title'));

        $rate->setMethod('fedex');
        $rate->setMethodTitle('FedEx (shipcloud) ');

        $rate->setPrice($responseShipmentCost + $extraAmount);
        $rate->setCost(0);

        return $rate;
    }

    protected function _getHermesShippingRate($parameters)
    {
        $rate = Mage::getModel('shipping/rate_result_method');
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */

        $rate->setCarrier($this->_code);

        $parameters['carrier'] = 'hermes';
        $parameters['service'] = 'standard';
        $shipcloud = Mage::getModel('shipcloud/shipcloud');

        $shipcloudData = $shipcloud->getShipmentCost($parameters);
        $responseShipmentCost = 0;

        if ($shipcloudData !== false) {
            $responseShipmentCost = $shipcloudData['response']['shipment_quote']['price'];
        }

        $extraAmount = Mage::getStoreConfig('shipcloud/' . $this->_code . '/hermes_extra_cost');
        $error = '';
        if (isset($shipcloudData['error']) && !empty($shipcloudData['error'])) {
            $error .= 'SHIPCLOUD  ERROR: ';
            $error .= implode('. ', $shipcloudData['error']);
            Mage::log($error, null, 'shipcloud.log');
            return false;
        }
        /**
         * getConfigData(config_key) returns the configuration value for the
         * carriers/[carrier_code]/[config_key]
         */
        $rate->setCarrierTitle($this->getConfigData('title'));

        $rate->setMethod('hermes');
        $rate->setMethodTitle('Hermes (shipcloud) ');

        $rate->setPrice($responseShipmentCost + $extraAmount);
        $rate->setCost(0);

        return $rate;
    }

    protected function _getLieferyShippingRate($parameters)
    {
        $rate = Mage::getModel('shipping/rate_result_method');
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */

        $rate->setCarrier($this->_code);

        $parameters['carrier'] = 'liefery';
        $parameters['service'] = 'same_day';
        $shipcloud = Mage::getModel('shipcloud/shipcloud');

        $shipcloudData = $shipcloud->getShipmentCost($parameters);
        $responseShipmentCost = 0;

        if ($shipcloudData !== false) {
            $responseShipmentCost = $shipcloudData['response']['shipment_quote']['price'];
        }

        $extraAmount = Mage::getStoreConfig('shipcloud/' . $this->_code . '/liefery_extra_cost');
        $error = '';
        if (isset($shipcloudData['error']) && !empty($shipcloudData['error'])) {
            $error .= 'SHIPCLOUD  ERROR: ';
            $error .= implode('. ', $shipcloudData['error']);
            Mage::log($error, null, 'shipcloud.log');
            return false;
        }
        /**
         * getConfigData(config_key) returns the configuration value for the
         * carriers/[carrier_code]/[config_key]
         */
        $rate->setCarrierTitle($this->getConfigData('title'));

        $rate->setMethod('liefery');
        $rate->setMethodTitle('Liefery (shipcloud) ');

        $rate->setPrice($responseShipmentCost + $extraAmount);
        $rate->setCost(0);

        return $rate;
    }

    protected function _getDPAGShippingRate($parameters)
    {
        $rate = Mage::getModel('shipping/rate_result_method');
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */

        $rate->setCarrier($this->_code);

        $parameters['carrier'] = 'dpag';
        $parameters['service'] = 'standard';
        $parameters['type'] = 'letter';
        $shipcloud = Mage::getModel('shipcloud/shipcloud');

        $shipcloudData = $shipcloud->getShipmentCost($parameters);
        $responseShipmentCost = 0;

        if ($shipcloudData !== false) {
            $responseShipmentCost = $shipcloudData['response']['shipment_quote']['price'];
        }

        $extraAmount = Mage::getStoreConfig('shipcloud/' . $this->_code . '/dpag_extra_cost');
        $error = '';
        if (isset($shipcloudData['error']) && !empty($shipcloudData['error'])) {
            $error .= 'SHIPCLOUD  ERROR: ';
            $error .= implode('. ', $shipcloudData['error']);
            Mage::log($error, null, 'shipcloud.log');
            return false;
        }
        /**
         * getConfigData(config_key) returns the configuration value for the
         * carriers/[carrier_code]/[config_key]
         */
        $rate->setCarrierTitle($this->getConfigData('title'));

        $rate->setMethod('dpag');
        $rate->setMethodTitle('DP AG (shipcloud) ');

        $rate->setPrice($responseShipmentCost + $extraAmount);
        $rate->setCost(0);

        return $rate;
    }


    public function getAllowedMethods()
    {
        return array(
            'dhl' => 'DHL',
            'dhl_express' => 'DHL Express',
            'ups' => 'UPS',
            'ups_express' => 'UPS Express',
            'ups_express_saver' => 'UPS Express Saver',
            'dpd' => 'DPD',
            'iloxx' => 'ILOXX',
            'gls' => 'GLS',
            'hermes' => 'HERMES',
            'fedex' => 'FedEx',
            'liefery' => 'LIEFERY',
            'dpag' => 'DPAG'
        );
    }
	
	public function getAllCarriers()
    {
        return array(
            'dhl' => 'DHL',
            'dhl_express' => 'DHL Express',
            'ups' => 'UPS',
            'ups_express' => 'UPS Express',
            'ups_express_save' => 'UPS Express Saver',
            'dpd' => 'DPD',
            'iloxx' => 'ILOXX',
            'gls' => 'GLS',
            'hermes' => 'HERMES',
            'fedex' => 'FedEx',
            'liefery' => 'LIEFERY',
            'dpag' => 'DPAG'
        );
    }

}
