<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$quoteTable  = $installer->getTable('sales/quote_payment');
$orderTable  = $installer->getTable('sales/order_payment');

$method = $code = Mage::getModel('xonu_directdebit/payment_method_config')->getCode();

$installer->run("
  UPDATE $quoteTable SET sepa_mandate_id = NULL WHERE NOT method = '$method' AND sepa_holder IS NULL AND sepa_iban IS NULL AND sepa_bic IS NULL;
  UPDATE $orderTable SET sepa_mandate_id = NULL WHERE NOT method = '$method' AND sepa_holder IS NULL AND sepa_iban IS NULL AND sepa_bic IS NULL;
");

$installer->endSetup();