<?php

$installer = $this;
$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('sociallogin')};
CREATE TABLE {$this->getTable('sociallogin_customer')} (
	`sociallogin_customer_id` int(11) unsigned NOT NULL auto_increment,	
	`sociallogin_id` varchar (255) NOT NULL,
	`customer_id` int(10) unsigned NOT NULL,
	INDEX(`customer_id`),
	FOREIGN KEY (`customer_id`) REFERENCES `{$this->getTable('customer_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
	PRIMARY KEY (`sociallogin_customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup(); 