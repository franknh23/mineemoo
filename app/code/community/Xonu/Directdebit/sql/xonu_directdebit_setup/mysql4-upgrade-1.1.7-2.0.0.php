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
$historyTable = $installer->getTable('xonu_directdebit/history');
$mandateTable = $installer->getTable('xonu_directdebit/mandate');

$installer->run("
    ALTER TABLE $quoteTable ADD sepa_mandate_id VARCHAR(255);
    ALTER TABLE $orderTable ADD sepa_mandate_id VARCHAR(255);
");

$installer->run("
    CREATE TABLE IF NOT EXISTS $mandateTable (
      `entity_id` int(11) NOT NULL AUTO_INCREMENT,
      `mandate_identifier` varchar(35) NOT NULL,
      `creditor_identifier` varchar(35) NOT NULL,
      `recurrent` tinyint(1) NOT NULL DEFAULT '0',
      `revoked` tinyint(1) NOT NULL DEFAULT '0',
      `store_id` smallint(5) NOT NULL,
      `created_at` timestamp NULL DEFAULT NULL,
      `updated_at` timestamp NULL DEFAULT NULL,
      `last_order_id` int(11) NOT NULL,
      `last_order_created_at` timestamp NULL DEFAULT NULL,
      `customer_id` int(11) DEFAULT NULL,
      `customer_email` varchar(255) NOT NULL,
      `customer_firstname` varchar(255) NOT NULL,
      `customer_lastname` varchar(255) NOT NULL,
      `document_data` text NOT NULL,
      `document_data_checksum` char(32) NOT NULL,
      `document_html` blob NOT NULL,
      `document_html_checksum` char(32) NOT NULL,
      PRIMARY KEY (`entity_id`),
      UNIQUE KEY `identifier_UNIQUE` (`mandate_identifier`),
      UNIQUE KEY `last_order_id_UNIQUE` (`last_order_id`),
      KEY `customer_id_MUL` (`customer_id`),
      KEY `last_order_created_at_MUL` (`last_order_created_at`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
");

$installer->run("
    CREATE TABLE IF NOT EXISTS $historyTable (
      `entity_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `user_id` int(10) unsigned DEFAULT NULL,
      `started_at` datetime DEFAULT NULL,
      `ended_at` datetime DEFAULT NULL,
      `count` int(10) unsigned NOT NULL DEFAULT '0',
      `empty` tinyint(3) unsigned NOT NULL DEFAULT '1',
      `external` tinyint(3) unsigned NOT NULL DEFAULT '0',
      `filename` varchar(255) DEFAULT NULL,
      PRIMARY KEY (`entity_id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
");

$installer->setupAgreement();

$installer->endSetup();