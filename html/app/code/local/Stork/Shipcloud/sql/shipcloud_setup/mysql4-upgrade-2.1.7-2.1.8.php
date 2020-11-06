<?php

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('shipcloud')}
ADD COLUMN `shipping_status` varchar(255) NOT NULL DEFAULT ''  after `price`,
ADD COLUMN `shipping_carrier` varchar(255) NOT NULL DEFAULT ''  after `shipping_status`;
");
$installer->endSetup();
