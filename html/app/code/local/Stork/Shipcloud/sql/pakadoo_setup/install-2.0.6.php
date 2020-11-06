<?php

$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('pakadoo')};
CREATE TABLE {$this->getTable('pakadoo')} (
  	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`pakadoo_id` varchar(255) NOT NULL DEFAULT '',
	`customer_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

$installer->endSetup();
