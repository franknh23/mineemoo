<?php
/**
 * Dhl Versenden
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * PHP version 5
 *
 * @category  Dhl
 * @package   Dhl_Versenden
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2016 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
use \Dhl\Versenden\Bcs\Api\Webservice\RequestData;
use \Dhl\Versenden\Bcs\Api\Webservice\ResponseData;
use \Dhl\Versenden\Bcs\Api\Webservice\Adapter\Soap as SoapAdapter;
use \Dhl\Versenden\Bcs\Api\Webservice\Parser\Soap as SoapParser;
/**
 * Dhl_Versenden_Test_Model_Webservice_AdapterTest
 *
 * @category Dhl
 * @package  Dhl_Versenden
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Dhl_Versenden_Test_Model_Webservice_SoapAdapterTest
    extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     * @dataProvider dataProvider
     *
     * @param string $serializedResponse
     */
    public function getVersion($serializedResponse)
    {
        $response = unserialize($serializedResponse);

        $major = '3';
        $minor = '0';

        $soapClient = $this->getMockBuilder(\SoapClient::class)
            ->setMethods(array('getVersion'))
            ->disableOriginalConstructor()
            ->getMock();
        $soapClient
            ->expects($this->once())
            ->method('getVersion')
            ->willReturn($response);

        $adapter = new SoapAdapter($soapClient);
        $requestData = new RequestData\Version($major, $minor, null);
        $parser = new SoapParser\Version();

        /** @var ResponseData\Version $response */
        $response = $adapter->getVersion($requestData, $parser);
        $this->assertInstanceOf(ResponseData\Version::class, $response);
        $this->assertStringStartsWith($major, $response->getVersion());
    }

    /**
     * @test
     * @dataProvider dataProvider
     *
     * @param string $serializedResponse
     * @param string $serializedRequestData
     */
    public function createShipmentOrder($serializedResponse, $serializedRequestData)
    {
        $requestData = unserialize($serializedRequestData);
        $response = unserialize($serializedResponse);

        $soapClient = $this->getMockBuilder(\SoapClient::class)
            ->setMethods(array('createShipmentOrder'))
            ->disableOriginalConstructor()
            ->getMock();
        $soapClient
            ->expects($this->once())
            ->method('createShipmentOrder')
            ->willReturn($response);

        $adapter = new SoapAdapter($soapClient);
        $parser = new SoapParser\CreateShipmentOrder();

        $response = $adapter->createShipmentOrder($requestData, $parser);
        $this->assertInstanceOf(ResponseData\CreateShipment::class, $response);
        $this->assertNotNull($response->getShipmentNumber(0));
    }

    /**
     * Authentication errors have no CreationState, exception must be thrown before status parsing.
     *
     * @test
     * @dataProvider dataProvider
     *
     * @param string $serializedResponse
     * @param string $serializedRequestData
     *
     * @expectedException \Dhl\Versenden\Bcs\Api\Webservice\ResponseData\Status\Exception
     */
    public function createShipmentOrderLoginFailed($serializedResponse, $serializedRequestData)
    {
        $requestData = unserialize($serializedRequestData);
        $response    = unserialize($serializedResponse);

        $soapClient = $this->getMockBuilder(\SoapClient::class)
            ->setMethods(array('createShipmentOrder'))
            ->disableOriginalConstructor()
            ->getMock();
        $soapClient
            ->expects($this->once())
            ->method('createShipmentOrder')
            ->willReturn($response);

        $adapter = new SoapAdapter($soapClient);
        $parser  = new SoapParser\CreateShipmentOrder();

        $adapter->createShipmentOrder($requestData, $parser);
    }

    /**
     * @test
     * @dataProvider dataProvider
     *
     * @param string $serializedResponse
     * @param string $serializedRequestData
     *
     */
    public function deleteShipmentOrderStatusError($serializedResponse, $serializedRequestData)
    {
        $requestData = unserialize($serializedRequestData);
        $response = unserialize($serializedResponse);

        $soapClient = $this->getMockBuilder(\SoapClient::class)
            ->setMethods(array('deleteShipmentOrder'))
            ->disableOriginalConstructor()
            ->getMock();
        $soapClient
            ->expects($this->once())
            ->method('deleteShipmentOrder')
            ->willReturn($response);

        $adapter = new SoapAdapter($soapClient);
        $parser = new SoapParser\DeleteShipmentOrder();

        $response = $adapter->deleteShipmentOrder($requestData, $parser);
        $this->assertInstanceOf(ResponseData\DeleteShipment::class, $response);
        $this->assertFalse($response->getStatus()->isSuccess());
    }

    /**
     * @test
     * @dataProvider dataProvider
     *
     * @param string $serializedResponse
     * @param string $serializedRequestData
     */
    public function deleteShipmentOrder($serializedResponse, $serializedRequestData)
    {
        /** @var RequestData\DeleteShipment $requestData */
        $requestData = unserialize($serializedRequestData);
        $response = unserialize($serializedResponse);

        $soapClient = $this->getMockBuilder(\SoapClient::class)
            ->setMethods(array('deleteShipmentOrder'))
            ->disableOriginalConstructor()
            ->getMock();
        $soapClient
            ->expects($this->once())
            ->method('deleteShipmentOrder')
            ->willReturn($response);

        $adapter = new SoapAdapter($soapClient);
        $parser = new SoapParser\DeleteShipmentOrder();

        $response = $adapter->deleteShipmentOrder($requestData, $parser);
        $this->assertInstanceOf(ResponseData\DeleteShipment::class, $response);

        $shipmentNumbers = $requestData->getShipmentNumbers();
        $deletedItems = $response->getDeletedItems();
        $this->assertNotNull($deletedItems);
        $this->assertCount(count($shipmentNumbers), $deletedItems);

        foreach ($shipmentNumbers as $shipmentNumber) {
            $deletedItem = $deletedItems->getItem($shipmentNumber);
            $this->assertNotNull($deletedItem);
            $this->assertInstanceOf(ResponseData\Status\Item::class, $deletedItem);
        }
    }

    /**
     * @test
     * @expectedException \Dhl\Versenden\Bcs\Api\Webservice\Adapter\NotImplementedException
     */
    public function getLabel()
    {
        $major = '3';
        $minor = '0';
        $requestData = new RequestData\Version($major, $minor, null);

        $soapClient = $this->getMockBuilder(\SoapClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parser = new SoapParser\Version();

        $adapter = new SoapAdapter($soapClient);
        $adapter->getLabel($requestData, $parser);
    }

    /**
     * @test
     * @expectedException \Dhl\Versenden\Bcs\Api\Webservice\Adapter\NotImplementedException
     */
    public function getExportDoc()
    {
        $major = '3';
        $minor = '0';
        $requestData = new RequestData\Version($major, $minor, null);

        $soapClient = $this->getMockBuilder(\SoapClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parser = new SoapParser\Version();

        $adapter = new SoapAdapter($soapClient);
        $adapter->getExportDoc($requestData, $parser);
    }

    /**
     * @test
     * @expectedException \Dhl\Versenden\Bcs\Api\Webservice\Adapter\NotImplementedException
     */
    public function doManifest()
    {
        $major = '3';
        $minor = '0';
        $requestData = new RequestData\Version($major, $minor, null);

        $soapClient = $this->getMockBuilder(\SoapClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parser = new SoapParser\Version();

        $adapter = new SoapAdapter($soapClient);
        $adapter->doManifest($requestData, $parser);
    }

    /**
     * @test
     * @expectedException \Dhl\Versenden\Bcs\Api\Webservice\Adapter\NotImplementedException
     */
    public function getManifest()
    {
        $major = '3';
        $minor = '0';
        $requestData = new RequestData\Version($major, $minor, null);

        $soapClient = $this->getMockBuilder(\SoapClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parser = new SoapParser\Version();

        $adapter = new SoapAdapter($soapClient);
        $adapter->getManifest($requestData, $parser);
    }

    /**
     * @test
     * @expectedException \Dhl\Versenden\Bcs\Api\Webservice\Adapter\NotImplementedException
     */
    public function updateShipmentOrder()
    {
        $major = '3';
        $minor = '0';
        $requestData = new RequestData\Version($major, $minor, null);

        $soapClient = $this->getMockBuilder(\SoapClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parser = new SoapParser\Version();

        $adapter = new SoapAdapter($soapClient);
        $adapter->updateShipmentOrder($requestData, $parser);
    }

    /**
     * @test
     * @expectedException \Dhl\Versenden\Bcs\Api\Webservice\Adapter\NotImplementedException
     */
    public function validateShipment()
    {
        $major = '3';
        $minor = '0';
        $requestData = new RequestData\Version($major, $minor, null);

        $soapClient = $this->getMockBuilder(\SoapClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parser = new SoapParser\Version();

        $adapter = new SoapAdapter($soapClient);
        $adapter->validateShipment($requestData, $parser);
    }

    /**
     * @test
     */
    public function parsePackstation()
    {
        $zip = '111';
        $city = 'Foo';
        $country = 'Germany';
        $countryISOCode = 'DE';
        $state = 'Saxony';

        $packstationNumber = '123';
        $postNumber = '123456';

        $packStation = new RequestData\ShipmentOrder\Receiver\Packstation(
            $zip,
            $city,
            $country,
            $countryISOCode,
            $state,
            $packstationNumber,
            $postNumber
        );

        $postalFacility = SoapAdapter\PostalFacilityType::prepare($packStation);
        $this->assertInstanceOf(\Dhl\Versenden\Bcs\Soap\PackStationType::class, $postalFacility);
        $this->assertEquals($zip, $postalFacility->getZip());
        $this->assertEquals($city, $postalFacility->getCity());
        $this->assertEquals($packstationNumber, $postalFacility->getPackstationNumber());
        $this->assertEquals($postNumber, $postalFacility->getPostNumber());
    }

    /**
     * @test
     */
    public function parsePostfiliale()
    {
        $zip = '111';
        $city = 'Foo';
        $country = 'Germany';
        $countryISOCode = 'DE';
        $state = 'Saxony';

        $postfilialNumber = '123';
        $postNumber = '123456';

        $postfiliale = new RequestData\ShipmentOrder\Receiver\Postfiliale(
            $zip,
            $city,
            $country,
            $countryISOCode,
            $state,
            $postfilialNumber,
            $postNumber
        );

        $postalFacility = SoapAdapter\PostalFacilityType::prepare($postfiliale);
        $this->assertInstanceOf(\Dhl\Versenden\Bcs\Soap\PostfilialeType::class, $postalFacility);
        $this->assertEquals($zip, $postalFacility->getZip());
        $this->assertEquals($city, $postalFacility->getCity());
        $this->assertEquals($postfilialNumber, $postalFacility->getPostfilialNumber());
        $this->assertEquals($postNumber, $postalFacility->getPostNumber());
    }

    /**
     * @test
     */
    public function parseUnknownFacilityType()
    {
        $major = '3';
        $minor = '0';
        $facility = new RequestData\Version($major, $minor, null);
        $postalFacility = SoapAdapter\PostalFacilityType::prepare($facility);
        $this->assertNull($postalFacility);
    }

    /**
     * @test
     */
    public function parseServices()
    {
        $preferredDay = '2016-12-24';
        $preferredTime = '19002100';
        $visualCheckOfAge = 'A21';
        $returnShipment = false;
        $preferredLocation = 'Chimney';
        $preferredNeighbour = 'Santa Berger';
        $parcelAnnouncement = false;
        $cod = 40.96;
        $parcelOutletRouting = 'foo@example.org';
        $insurance = 34.06;
        $bulkyGoods = true;
        $printOnlyIfCodeable = true;

        $requestData = new RequestData\ShipmentOrder\ServiceSelection(
            $preferredDay, $preferredTime, $preferredLocation, $preferredNeighbour, $parcelAnnouncement,
            $visualCheckOfAge, $returnShipment, $insurance, $bulkyGoods, $parcelOutletRouting, $cod,
            $printOnlyIfCodeable
        );
        $shipmentServices = SoapAdapter\ServiceType::prepare($requestData);

        $this->assertInstanceOf(\Dhl\Versenden\Bcs\Soap\ShipmentService::class, $shipmentServices);

        $this->assertEquals($preferredDay, $shipmentServices->getPreferredDay()->getDetails());
        $this->assertEquals($preferredTime, $shipmentServices->getPreferredTime()->getType());
        $this->assertEquals($visualCheckOfAge, $shipmentServices->getVisualCheckOfAge()->getType());
        // $returnShipment is no ServiceType service
        $this->assertEquals($preferredLocation, $shipmentServices->getPreferredLocation()->getDetails());
        $this->assertEquals($preferredNeighbour, $shipmentServices->getPreferredNeighbour()->getDetails());
        // $parcelAnnouncement is no ServiceType service
        $this->assertEquals($parcelOutletRouting, $shipmentServices->getParcelOutletRouting()->getDetails());
        $this->assertEquals($cod, $shipmentServices->getCashOnDelivery()->getCodAmount());
        $this->assertEquals($insurance, $shipmentServices->getAdditionalInsurance()->getInsuranceAmount());
        $this->assertEquals($bulkyGoods, $shipmentServices->getBulkyGoods()->getActive());
        // $printOnlyIfCodeable is no ServiceType service
    }
}
