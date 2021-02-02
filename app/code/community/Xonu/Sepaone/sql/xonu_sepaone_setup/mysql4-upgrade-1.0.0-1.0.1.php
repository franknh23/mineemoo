<?php
/**
 * @package Xonu_Sepaone
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$helper = Mage::helper('xonu_sepaone');

$setup = new Mage_Core_Model_Config();
$setup->saveConfig('xonu_directdebit/sepaone/webhook_secret', $helper->getAlphaNumericString(), 'default', 0);

$installer->endSetup();