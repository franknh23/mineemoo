<?php

$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('shipcloud')};
CREATE TABLE {$this->getTable('shipcloud')} (
  `shipcloud_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `order_id` int(11) NOT NULL DEFAULT '0',
  `trackingnumber` varchar(255) NOT NULL DEFAULT '',
  `return_label_url` VARCHAR(250) NOT NULL DEFAULT '',
  `trackingurl` varchar(255) NOT NULL DEFAULT '',
  `response_id` varchar(255) NOT NULL DEFAULT '',
  `labelname` varchar(255) NOT NULL DEFAULT '',
  `labelurl` varchar(255) NOT NULL DEFAULT '',
  `labelimg` varchar(255) NOT NULL DEFAULT '',
  `status` smallint(6) NOT NULL DEFAULT '0',
  `created_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  `shipment_id` int(11) DEFAULT '0',
  `type` varchar(20) DEFAULT 'shipment',
  `price` varchar(50) NOT NULL DEFAULT '0',
  PRIMARY KEY (`shipcloud_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

$installer->endSetup(); 
