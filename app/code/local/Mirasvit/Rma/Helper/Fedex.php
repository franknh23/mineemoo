<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   RMA
 * @version   2.1.0-beta
 * @build     1359
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Rma_Helper_Fedex extends Mage_Core_Helper_Abstract
{
    protected $_store = null;
    protected $_fedexCarrier = null;
    protected $_fedexClient = null;

    /*
     * Returns default FedEx method fron RMA config.
     *
     * @return string
     */
    public function getDefaultFedexMethod()
    {
        return strtoupper($this->getConfigData('fedex_method', false));
    }

    /*
     * Checks, whether FedEx service is properly enabled
     *
     * @return boolean
     */
     public function isEnabled()
     {
         return $this->getConfigData('fedex_enable', false) &&
            $this->getConfigData('key') != '' &&
            $this->getConfigData('password') != '' &&
            $this->getConfigData('account') != '' &&
            $this->getConfigData('meter_number') != '';
     }

    /*
     * Returns current store.
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if (!$this->_store) {
            $this->_store = Mage::app()->getStore();
        }

        return $this->_store;
    }

    /*
     * Returns FedEx carrier, installed in current Magento environment.
     *
     * @return Mage_Usa_Model_Shipping_Carrier_Fedex
     */
    public function getFedexCarrier()
    {
        if ($this->_fedexCarrier === null) {
            $this->_fedexCarrier = new Mage_Usa_Model_Shipping_Carrier_Fedex();
        }

        return $this->_fedexCarrier;
    }

    /*
     * Returns SOAP client, used for communicating with FedEx server (either sandbox or production).
     *
     * @return Mage_Usa_Model_Shipping_Carrier_Fedex
     */
    public function getFedexClient()
    {
        $wsdl = Mage::getModuleDir('etc', 'Mage_Usa').DS.'wsdl'.DS.'FedEx'.DS.'ShipService_v10.wsdl';
        $sandboxMode = $this->getConfigData('sandbox_mode');
        if (!$this->_fedexClient) {
            $this->_fedexClient = new SoapClient($wsdl, array('trace' => 1));
            $this->_fedexClient->__setLocation($sandboxMode
                ? 'https://wsbeta.fedex.com:443/web-services '
                : 'https://ws.fedex.com:443/web-services'
            );

            return $this->_fedexClient;
        }

        return $this->_fedexClient;
    }

    /*
     * Sets current store.
     *
     * @param Mage_Core_Model_Store
     */
    public function setStore($store)
    {
        $this->_store = $store;
    }

    /*
     * Returns configuration constant by shortened key.
     *
     * @param string - shortened key.
     * @param bool - selects prefix. true - global FedEx config, false - RMA FedEx Config.
     *
     * @return string
     */
    public function getConfigData($key, $global = true)
    {
        if ($global) {
            $configData = Mage::getStoreConfig('carriers/fedex/'.$key, $this->getStore());
        } else {
            $configData = Mage::getStoreConfig('rma/fedex/'.$key, $this->getStore());
        }

        return (!$configData) ? '' : $configData;
    }

    /*
     * Returns array of currently allowed FedEx container types
     *
     * @param string - current FedEx shipping method.
     * @param Mage_Sales_Core_Order - base order for current RMA.
     *
     * @return array
     */
    public function getContainers($method, $order)
    {
        $fedexCarrier = $this->getFedexCarrier();
        if ($fedexCarrier && $order->getShippingAddress()) {
            $params = new Varien_Object(array(
                'method' => $method,
                'country_shipper' => $order->getShippingAddress()->getCountryId(),
                'country_recipient' => Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $order->getStoreId()),
            ));

            return $fedexCarrier->getContainerTypes($params);
        }

        return array();
    }

    /*
     * Returns array of delivery confirmation types
     *
     * @param Mage_Sales_Core_Order - base order for current RMA.
     *
     * @return array
     */
    public function getDeliveryConfirmationTypes($order)
    {
        $fedexCarrier = $this->getFedexCarrier();
        $params = new Varien_Object(array('country_recipient' => Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $order->getStoreId())));
        if ($fedexCarrier && is_array($fedexCarrier->getDeliveryConfirmationTypes($params))) {
            return $fedexCarrier->getDeliveryConfirmationTypes($params);
        }

        return array();
    }

    /*
     * Creates PDF file from FedEx-generated image file.
     *
     * @param string - serialized image content.
     *
     * @return string
     */
    protected function makePDF($content)
    {
        $outputPdf = new Zend_Pdf();
        if (stripos($content, '%PDF-') !== false) {
            $pdfLabel = Zend_Pdf::parse($content);
            foreach ($pdfLabel->pages as $page) {
                $outputPdf->pages[] = clone $page;
            }
        } else {
            $image = imagecreatefromstring($content);
            if (!$image) {
                return false;
            }

            $xSize = imagesx($image);
            $ySize = imagesy($image);
            $page = new Zend_Pdf_Page($xSize, $ySize);

            imageinterlace($image, 0);
            $tmpFileName = sys_get_temp_dir().DS.'shipping_labels_'
                .uniqid(mt_rand()).time().'.png';
            imagepng($image, $tmpFileName);
            $pdfImage = Zend_Pdf_Image::imageWithPath($tmpFileName);
            $page->drawImage($pdfImage, 0, 0, $xSize, $ySize);

            unlink($tmpFileName);
            if ($page) {
                $outputPdf->pages[] = $page;
            }
        }

        return $outputPdf;
    }

    /*
     * Creates SOAP Authentification Block, based on FedEx credentials.
     *
     * @return array
     */
    protected function getAuthentificationDetails()
    {
        return array(
            'WebAuthenticationDetail' => array(
                'UserCredential' => array(
                    'Key' => $this->getConfigData('key'),
                    'Password' => $this->getConfigData('password'),
                ),
            ),
            'ClientDetail' => array(
                'AccountNumber' => $this->getConfigData('account'),
                'MeterNumber' => $this->getConfigData('meter_number'),
            ),
            'TransactionDetail' => array(
                'CustomerTransactionId' => '*** Express Domestic Shipping Request v9 using PHP ***',
            ),
            'Version' => array(
                'ServiceId' => 'ship',
                'Major' => '10',
                'Intermediate' => '0',
                'Minor' => '0',
            ),
        );
    }

    /*
     * Creates Commodity Block (shipment item list) for SOAP request.
     *
     * @param items
     *
     * @return array
     */
    protected function getCommodities($items)
    {
        $commodity = array();
        foreach ($items as $item) {
            $product = Mage::getModel('catalog/product')->load($item['product_id']);
            $commodity[] = array(
                'Name' => $item['name'],
                'NumberOfPieces' => 1,
                'Description' => $product->getDescription(),
                'CountryOfManufacture' => $product->getCountryOfManufacture() ? $product->getCountryOfManufacture() : 'US',
                'Weight' => array(
                    'Units' => 'LB',
                    'Value' => ($item['weight']) ? $item['weight'] : $this->getConfigData('fedex_default_weight', false),
                ),
                'Quantity' => $item['qty'],
                'QuantityUnits' => 'pcs',
                'UnitPrice' => array(
                    'Currency' => $this->getStore()->getCurrentCurrencyCode(),
                    'Amount' => $product->getPrice(),
                ),
                'CustomsValue' => array(
                    'Currency' => $this->getStore()->getCurrentCurrencyCode(),
                    'Amount' => 0,
                ),
            );
        }

        return $commodity;
    }

    /*
     * Creates Commodity Block (shipment item list) for SOAP request.
     *
     * @param items
     *
     * @return array
     */
    public function validateRequest($rma, $params)
    {
        $errors = array();

        $customer = Mage::getModel('customer/customer')->load($rma->getCustomerId());
        $address = Mage::getModel('customer/address')->load($customer->getDefaultBilling());
        if (!$address) {
            $errors[] = Mage::helper('rma')->__('Sender must have billing address!');
        }

        // Settings parameters
        if (!Mage::getStoreConfig('general/store_information/name') ||
            Mage::getStoreConfig('general/store_information/phone') ||
            Mage::getStoreConfig('general/store_information/merchant_country')) {
            $errors[] = Mage::helper('rma')->__('Please, set store credentials at Configuration -> General!');
        }

        $totalWeight = 0;
        foreach ($params['items'] as $item) {
            if (!$item['weight'] && !$this->getConfigData('fedex_default_weight', false)) {
                $errors[] = Mage::helper('rma')->__('Product '.$item['name'].' should have weight');
                continue;
            }
            $totalWeight += $item['weight'];
        }
        if ($totalWeight != $params['params']['weight']) {
            $errors[] = Mage::helper('rma')->__('Please, check products weight: it must be equal to overall!');
        }

        return $errors;
    }

    /**
     * Creates SOAP-request and receives FedEx processing data. If success, returns serialized label data.
     * If any error or exception, returns appropriate message.
     *
     * @param $rma - current RMA
     * @param $params - array with FedEx label parameters
     *
     * @return array
     *
     * @throws Zend_Pdf_Exception
     */
    public function createFedexLabel($rma, $params)
    {
        $validateErrors = $this->validateRequest($rma, $params);
        if (!count($validateErrors)) {
            return array('status' => 'fail', 'errata' => $validateErrors);
        }

        $client = $this->getFedexClient();

        $customer = Mage::getModel('customer/customer')->load($rma->getCustomerId());
        $address = $rma->getOrder()->getShippingAddress();
        if (!$address) {
            if (!$address = $rma->getOrder()->getBillingAddress()) {
                $address = Mage::getModel('customer/address')->load($customer->getDefaultBilling());
            }
        }

        try {
            $request = array(
                'RequestedShipment' => array(
                    'ShipTimestamp' => time(),
                    'DropoffType' => $this->getConfigData('dropoff'),
                    'PackagingType' => $params['params']['container'],
                    'ServiceType' => $this->getDefaultFedexMethod(),

                    'TotalWeight' => array(
                        'Units' => 'LB',
                        'Value' => $params['params']['weight'],
                    ),

                    // This is customer's credentials
                    'Shipper' => array(
                        'Contact' => array(
                            'PersonName' => trim($address->getFirstname().' '.$address->getLastname()),
                            'CompanyName' => $address->getCompany(),
                            'PhoneNumber' => $address->getTelephone(),
                            'EMailAddress' => ($customer) ? $customer->getEmail() : $rma->getOrder()->getCustomerEmail(),
                            ),
                            'Address' => array(
                            'StreetLines' => array(
                                $address->getStreet()[0],
                                (isset($address->getStreet()[1]) ? $address->getStreet()[1] : ''),
                            ),
                            'City' => $address->getCity(),
                            'StateOrProvinceCode' => Mage::getModel('directory/region')->load($address->getRegionId())->getCode(),
                            'PostalCode' => $address->getPostcode(),
                            'CountryCode' => $address->getCountryId(),
                        ),
                    ),

                    // This is our store credentials
                    'Recipient' => array(
                        'Contact' => array(
                            'PersonName' => $this->getConfigData('store_person', false),
                            'CompanyName' => Mage::getStoreConfig('general/store_information/name'),
                            'PhoneNumber' => Mage::getStoreConfig('general/store_information/phone'), // -- to constants (not sure)
                            'EMailAddress' => Mage::getStoreConfig('trans_email/ident_general/email'),
                            ),
                            'Address' => array(
                            'StreetLines' => array(
                                $this->getConfigData('store_address_line1', false),
                                $this->getConfigData('store_address_line2', false),
                            ),
                            'City' => $this->getConfigData('store_city', false),
                            'StateOrProvinceCode' => $this->getConfigData('store_state_code', false),
                            'PostalCode' => $this->getConfigData('store_postal_code', false),
                            'CountryCode' => Mage::getStoreConfig('general/store_information/merchant_country'), // -- to constants (not sure)
                        ),
                    ),

                    'ShippingChargesPayment' => array(
                        'PaymentType' => $this->getConfigData('fedex_charges_payor', false),
                        'Payor' => array(
                            'AccountNumber' => $this->getConfigData('account'),
                            'CountryCode' => 'US',
                        ),
                    ),

                    'CustomsClearanceDetail' => array(
                        'DutiesPayment' => array(
                            'PaymentType' => 'RECIPIENT',
                            'Payor' => array(
                                'AccountNumber' => $this->getConfigData('account'),
                                'CountryCode' => 'US',
                            ),
                        ),
                        'CustomsValue' => array(
                            'Currency' => 'USD',
                            'Amount' => 0,
                        ),

                        // Here goes products details
                        'Commodities' => $this->getCommodities($params['items']),
                    ),

                    'SmartPostDetail' => array(
                        'Indicia' => ($params['params']['weight'] >= 1) ? $this->getConfigData('fedex_smartpost_indicia', false) : Mirasvit_Rma_Model_Config::FEDEX_SMARTPOST_INDICIA_PRESORTED,
                        'AncillaryEndorsement' => 'RETURN_SERVICE',
                        'HubId' => $this->getConfigData('fedex_smartpost_hubid', false),
                    ),

                    'LabelSpecification' => array(
                        'LabelFormatType' => 'COMMON2D',
                        'ImageType' => 'PNG',
                        'LabelStockType' => 'PAPER_8.5X11_TOP_HALF_LABEL',
                    ),
                    'RateRequestTypes' => array('ACCOUNT'),
                    'PackageCount' => 1,
                    'RequestedPackageLineItems' => array(
                        'SequenceNumber' => '1',
                        'Weight' => array(
                            'Units' => 'LB',
                            'Value' => $params['params']['weight'],
                        ),
                        'CustomerReferences' => array(
                            'CustomerReferenceType' => 'CUSTOMER_REFERENCE',
                            'Value' => $this->getConfigData('fedex_reference', false),
                        ),
                        'SpecialServicesRequested' => array(
                            'SpecialServiceTypes' => 'SIGNATURE_OPTION',
                            'SignatureOptionDetail' => array(
                                'OptionType' => $params['params']['delivery_confirmation'],
                            ),
                        ),
                    ),
                ),
            );

            // Smart Post specials
            if ($this->getDefaultFedexMethod() == strtoupper(Mirasvit_Rma_Model_Config::FEDEX_METHOD_SMART_POST)) {
                unset($request['RequestedShipment']['RequestedPackageLineItems']['SpecialServicesRequested']);
            } else {
                unset($request['RequestedShipment']['SmartPostDetail']);
            }

            $request = $this->getAuthentificationDetails() + $request;
            $response = $client->processShipment($request);

            if ($response->HighestSeverity == 'SUCCESS' || $response->HighestSeverity == 'NOTE') {
                // Let's create label in PDF format
                $pdf = $this->makePDF($response->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image);
                $label = Mage::getModel('rma/fedex_label');
                $label->setRmaId($rma->getId());
                $label->setLabelDate(time());
                $label->setTrackNumber($response->CompletedShipmentDetail->CompletedPackageDetails->TrackingIds->TrackingNumber);
                $label->setPackageNumber(count(Mage::getModel('rma/fedex_label')->getCollection()) + 1);
                $label->setLabelBody($pdf->render());
                $label->save();

                return array('status' => 'success', 'data' => $pdf->render());
            } else {
                $errata = array();
                if (!is_array($response->Notifications)) {
                    $errata[] = $response->Notifications->Severity.' '.$response->Notifications->Code.': '.$response->Notifications->Message;
                } else {
                    foreach ($response->Notifications as $notification) {
                        if (is_array($response->Notifications)) {
                            $errata[] = $notification->Severity.' '.$notification->Code.': '.$notification->Message;
                        } else {
                            $errata[] = $notification;
                        }
                    }
                }

                return array('status' => 'fail', 'errata' => $errata);
            }
        } catch (SoapFault $fault) {
            Mage::log($fault, null, 'fedex-exception.log');

            return array('status' => 'fail', 'errata' => array('Unexpected exception. Please, review all FedEx settings and properties of shipping package!'));
        }
    }

    /*
     * If JSON array is created by JavaScript, it will be decoded as stdClass, not as an array. This function decodes it recursively.
     *
     * @param Zend stdClass - JSON serialized data
     *
     * @return string
     */
    public function jsonToArray($jsonData)
    {
        $arrayData = get_object_vars($jsonData);
        foreach (array_keys($arrayData) as $key) {
            if (is_object($arrayData[$key])) {
                $arrayData[$key] = $this->jsonToArray($arrayData[$key]);
            }
        }

        return $arrayData;
    }
}
