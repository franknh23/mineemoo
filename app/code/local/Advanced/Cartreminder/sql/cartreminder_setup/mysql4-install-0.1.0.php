<?php

$installer = $this;

$installer->startSetup();

$installer->run("
     DROP TABLE IF EXISTS {$this->getTable('advanced_reminder')};
    CREATE TABLE {$this->getTable('advanced_reminder')} (
        `advanced_reminder_id` int(11) unsigned NOT NULL auto_increment,
        `quote_id` int(11),
        `number_of_reminder` int(11) not null default 0,
        `reminder_time` datetime,
		`key` varchar(255),
        PRIMARY KEY (`advanced_reminder_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
