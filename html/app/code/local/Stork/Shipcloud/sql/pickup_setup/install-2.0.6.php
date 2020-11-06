<?php

$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('pickup')};
CREATE TABLE {$this->getTable('pickup')} (
  	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`pickup_id` int(11) NOT NULL DEFAULT '0',
	`carrier_pickup_number` varchar(255) NOT NULL DEFAULT '',
	`carrier` varchar(255) NOT NULL DEFAULT '',
	`pickup_date` datetime DEFAULT NULL,
	`shipment_key` varchar(255) NOT NULL DEFAULT '',
	`user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

$installer->endSetup(); 
