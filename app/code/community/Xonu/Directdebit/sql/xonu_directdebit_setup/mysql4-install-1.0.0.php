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
$exportTable = $installer->getTable('xonu_directdebit/export');

$installer->run("
ALTER TABLE $quoteTable ADD sepa_holder VARCHAR(255);
ALTER TABLE $quoteTable ADD sepa_iban   VARCHAR(255);
ALTER TABLE $quoteTable ADD sepa_bic    VARCHAR(255);

ALTER TABLE $orderTable ADD sepa_holder VARCHAR(255);
ALTER TABLE $orderTable ADD sepa_iban   VARCHAR(255);
ALTER TABLE $orderTable ADD sepa_bic    VARCHAR(255);

CREATE TABLE IF NOT EXISTS $exportTable (
  entity_id int(11) NOT NULL AUTO_INCREMENT,
  order_id int(11) NOT NULL,
  exported tinyint(4) DEFAULT '0',
  exported_at datetime DEFAULT NULL,
  PRIMARY KEY (entity_id),
  UNIQUE KEY order_id_idx (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();