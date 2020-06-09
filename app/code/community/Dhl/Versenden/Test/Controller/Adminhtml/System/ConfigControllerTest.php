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
 * Dhl_Versenden_Test_Controller_Adminhtml_System_ConfigControllerTest
 *
 * @category Dhl
 * @package  Dhl_Versenden
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Dhl_Versenden_Test_Controller_Adminhtml_System_ConfigControllerTest
    extends Dhl_Versenden_Test_Case_AdminController
{
    /**
     * Dispatch admin route, assert blocks being loaded.
     * @see Dhl_Versenden_Block_Adminhtml_System_Config_Heading
     * @see Dhl_Versenden_Block_Adminhtml_System_Config_Info
     *
     * @test
     * @loadFixture Controller_ConfigTest
     */
    public function renderSection()
    {
        $this->dispatch('adminhtml/system_config/edit/section/carriers');

        $this->assertResponseBodyContains('DHL Versenden');
        $this->assertResponseBodyRegExp('/Version: \d\.\d{1,2}\.\d{1,2}/');
    }
}
