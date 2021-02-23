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

$exportTable  = $installer->getTable('xonu_sepaone/export');
$historyTable = $installer->getTable('xonu_sepaone/history');
$logTable     = $installer->getTable('xonu_sepaone/log');

$installer->run("
    CREATE TABLE IF NOT EXISTS $exportTable (
      entity_id INT(11) NOT NULL AUTO_INCREMENT,
      order_id INT(11) NOT NULL,
      last_transaction_id CHAR(35) DEFAULT NULL,
      last_transaction_status varchar(255) DEFAULT '',
      exported TINYINT(4) DEFAULT '0',
      errors TINYINT(4) DEFAULT '0',
      exported_at DATETIME DEFAULT NULL,
      PRIMARY KEY (entity_id),
      UNIQUE KEY order_id_idx (order_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->run("
    CREATE TABLE IF NOT EXISTS $logTable (
      `entity_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,

      `order_id` int(11) DEFAULT NULL,
      `order_increment_id` VARCHAR(50) DEFAULT NULL,
      `mandate_id` VARCHAR(35) DEFAULT NULL,

      `request_type` VARCHAR(255) NOT NULL DEFAULT '',
      `request_at` DATETIME DEFAULT NULL,
      `request_headers` TEXT NOT NULL DEFAULT '',
      `request_custom` VARCHAR(255) NOT NULL DEFAULT '',
      `request_body` TEXT NOT NULL DEFAULT '',
      `request_body_length` SMALLINT unsigned NOT NULL DEFAULT '0',
      `request_uri` VARCHAR(255) NOT NULL DEFAULT '',

      `response_type` VARCHAR(255) NOT NULL DEFAULT '',
      `response_at` DATETIME DEFAULT NULL,
      `response_time` FLOAT UNSIGNED DEFAULT NULL,
      `response_code` TINYINT(3) unsigned NOT NULL DEFAULT '0',
      `response_headers` TEXT NOT NULL DEFAULT '',
      `response_body` TEXT NOT NULL DEFAULT '',
      `response_body_length` SMALLINT unsigned NOT NULL DEFAULT '0',
      `response_uri` VARCHAR(255) NOT NULL DEFAULT '',

      `remote_transaction_id` CHAR(35) DEFAULT NULL,
      `remote_livemode` TINYINT(3) UNSIGNED DEFAULT '0',

      PRIMARY KEY (`entity_id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
");

$installer->run("
    CREATE TABLE IF NOT EXISTS $historyTable (
      `entity_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `started_at` DATETIME DEFAULT NULL,
      `ended_at` DATETIME DEFAULT NULL,
      `processing_time` FLOAT UNSIGNED DEFAULT NULL,
      `count_transactions` INT(10) UNSIGNED NOT NULL DEFAULT '0',
      `count_errors` INT(10) UNSIGNED NOT NULL DEFAULT '0',
      `empty` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
      `test` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
      PRIMARY KEY (`entity_id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
");

$installer->endSetup();