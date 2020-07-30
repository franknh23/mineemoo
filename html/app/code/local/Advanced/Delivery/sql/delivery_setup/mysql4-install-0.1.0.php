<?php
$installer = $this;

$installer->startSetup();

/**
 * create deliverydate table
 */
$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('holiday')};
    CREATE TABLE {$this->getTable('holiday')} (
      `holiday_id` int(11) unsigned NOT NULL auto_increment,
      `store_id` varchar(255) NOT NULL default '',
      `datefrom` date NULL,
      `dateto` date NULL,
      `description` varchar(255) NOT NULL default '',
      `status` tinyint(1) NOT NULL default '1',
      PRIMARY KEY (`holiday_id`) 
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


    DROP TABLE IF EXISTS {$this->getTable('intervals')};
    CREATE TABLE {$this->getTable('intervals')} (
      `intervals_id` int(11) unsigned NOT NULL auto_increment,
      `store_id` varchar(255) NOT NULL default '',
      `hourstart` varchar(5) NOT NULL,
      `hourto` varchar(5) NOT NULL,
      `status` tinyint(1) NOT NULL default '1',
      PRIMARY KEY (`intervals_id`) 
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


    DROP TABLE IF EXISTS {$this->getTable('deliverydate')};
    CREATE TABLE {$this->getTable('deliverydate')} (
      `deliverydate_id` int(11) unsigned NOT NULL auto_increment,
      `order_id` int(11) NOT NULL ,
      `increment_id` int(100) NOT NULL ,
      `delivery_date` date NULL,
      `hourstart` varchar(255) NOT NULL,
      `description` text NOT NULL,
      `status` tinyint(1) NOT NULL default 0,
      PRIMARY KEY (`deliverydate_id`) 
    )ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();


