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

/**
 * Dhl_Versenden_Test_Model_ConfigTest
 *
 * @category Dhl
 * @package  Dhl_Versenden
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Dhl_Versenden_Test_Model_ConfigTest extends EcomDev_PHPUnit_Test_Case
{
    protected function disableSandboxMode()
    {
        $path = sprintf(
            '%s/%s/%s',
            Dhl_Versenden_Model_Config::CONFIG_SECTION,
            Dhl_Versenden_Model_Config::CONFIG_GROUP,
            Dhl_Versenden_Model_Config::CONFIG_XML_PATH_SANDBOX_MODE
        );
        Mage::app()->getStore()->setConfig($path, '0');
    }

    /**
     * @test
     * @loadFixture Model_ConfigTest
     */
    public function isAutoloadEnabled()
    {
        $config = new Dhl_Versenden_Model_Config();
        $this->assertFalse($config->isAutoloadEnabled());
    }

    /**
     * @test
     * @loadFixture Model_ConfigTest
     */
    public function getTitle()
    {
        $config = new Dhl_Versenden_Model_Config();
        $this->assertEquals('foo', $config->getTitle());
        $this->assertEquals('bar', $config->getTitle('store_one'));
        $this->assertEquals('baz', $config->getTitle('store_two'));
    }

    /**
     * @test
     * @loadFixture Model_ConfigTest
     */
    public function isActive()
    {
        $config = new Dhl_Versenden_Model_Config();
        $this->assertTrue($config->isActive());
        $this->assertFalse($config->isActive('store_one'));
        $this->assertTrue($config->isActive('store_two'));
    }

    /**
     * @test
     * @loadFixture Model_ConfigTest
     */
    public function isSandboxModeEnabled()
    {
        $config = new Dhl_Versenden_Model_Config();

        // sandbox
        $this->assertTrue($config->isSandboxModeEnabled());

        // production
        $this->disableSandboxMode();
        $this->assertFalse($config->isSandboxModeEnabled());
    }

    /**
     * @test
     * @loadFixture Model_ConfigTest
     */
    public function isLoggingEnabled()
    {
        $config = new Dhl_Versenden_Model_Config();
        $this->assertTrue($config->isLoggingEnabled());
        $this->assertTrue($config->isLoggingEnabled(Zend_Log::DEBUG));
    }

    /**
     * @test
     * @loadFixture Model_ConfigTest
     */
    public function getWebserviceCredentials()
    {
        $config = new Dhl_Versenden_Model_Config();

        // sandbox
        $this->assertEquals('uBar', $config->getWebserviceAuthUsername());
        $this->assertEquals('pBar', $config->getWebserviceAuthPassword());

        // production
        $this->disableSandboxMode();
        $this->assertEquals('uFoo', $config->getWebserviceAuthUsername());
        $this->assertEquals('pFoo', $config->getWebserviceAuthPassword());
    }

    /**
     * @test
     * @loadFixture Model_ConfigTest
     */
    public function getWebserviceEndpoint()
    {
        $config = new Dhl_Versenden_Model_Config();

        // sandbox
        $this->assertEquals('sandbox endpoint', $config->getEndpoint());

        // production
        $this->disableSandboxMode();
        $this->assertNull($config->getEndpoint());
    }
}
