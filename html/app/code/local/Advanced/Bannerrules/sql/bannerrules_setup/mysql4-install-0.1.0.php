<?php
/**
 * Stabeaddon
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Stabeaddon.com license that is
 * available through the world-wide-web at this URL:
 * http://www.stabeaddon.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Stabeaddon
 * @package     Stabeaddon_Bannerrules
 * @copyright   Copyright (c) 2012 Stabeaddon (http://www.stabeaddon.com/)
 * @license     http://www.stabeaddon.com/license-agreement.html
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * create bannerrules table
 */
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('bannerrules')};

CREATE TABLE {$this->getTable('bannerrules')} (
  `bannerrules_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `description` text NOT NULL default '',
  `customer_group` varchar(255) NOT NULL default '',
  `website` varchar(255) NOT NULL default '',  
  `from_date` datetime NULL,
  `to_date` datetime NULL,
  `priority` int(11) NOT NULL ,
  `conditions_serialized` mediumtext default NULL,
  `show_block` varchar(255) NOT NULL default '',
  `position_cart` smallint(6) NOT NULL default '0',  
  `position_checkout` smallint(6) NOT NULL default '0',  
  `position_oscheckout` smallint(6) NOT NULL default '0',  
  `status` smallint(6) NOT NULL default '0',  
  PRIMARY KEY (`bannerrules_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();

