<?php

/**
 * Advanced Checkout
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the advancedcheckout.com license that is
 * available through the world-wide-web at this URL:
 * http://www.advancedcheckout.com/license-agreement
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category 	Advanced Checkout
 * @package 	Advanced_Onestepcheckout
 * @copyright 	Copyright (c) 2015 Advanced Checkout (http://www.advancedcheckout.com/)
 * @license 	http://www.advancedcheckout.com/license-agreement
 */
/**
 * Onestepcheckout Block
 * 
 * @category 	Onestepcheckout
 * @package 	Advanced_Onestepcheckout
 * @author  	Onestepcheckout Developer
 */
/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * create onestepcheckout table
 */
$sqlAddColumn = "
drop procedure if exists AddColumnUnlessExists;
create procedure AddColumnUnlessExists(
 IN dbName tinytext,
 IN tableName tinytext,
 IN fieldName tinytext,
 IN fieldDef text)
begin
 IF NOT EXISTS (
  SELECT * FROM information_schema.COLUMNS
  WHERE column_name=fieldName
  and table_name=tableName
  and table_schema=dbName
  )
 THEN
  set @ddl=CONCAT('ALTER TABLE ',tableName,
   ' ADD COLUMN ',fieldName,' ',fieldDef);
  prepare stmt from @ddl;
  execute stmt;
 END IF;
end
";
$write = Mage::getSingleton('core/resource')->getConnection('core_write');
$write->exec($sqlAddColumn);

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('onestepcheckout_field_position')};

    CREATE TABLE {$this->getTable('onestepcheckout_field_position')} (
      `onestepcheckout_field_position_id` int(11) unsigned NOT NULL auto_increment,
      `scope` varchar(255) NOT NULL default 'default',
      `scope_id` int(11) NOT NULL default 0,
      `path` varchar(255),
      `value` varchar(255),
      `width` int(11),  
      `position` int(11),  
      `use_default` int(11) NOT NULL default 1,
      `required` int(11) NOT NULL default 0,
      `remove` varchar(255) NOT NULL default 'not-remove',
      PRIMARY KEY (`onestepcheckout_field_position_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

		
	DROP TABLE IF EXISTS {$this->getTable('advanced_survey')};	
	CREATE TABLE {$this->getTable('advanced_survey')}(
			`survey_id` int(11) unsigned NOT NULL auto_increment,
			`question` varchar(255) default '',			 
			`answer` varchar(255) default '',			 
                        `order_id` int(10) unsigned NOT NULL,		   			  		   
                        PRIMARY KEY (`survey_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        
        call AddColumnUnlessExists(DATABASE(), '{$this->getTable('sales/quote_address')}', 'is_valid_vat', 'SMALLINT(1) DEFAULT NULL');
        call AddColumnUnlessExists(DATABASE(), '{$this->getTable('sales/quote_address')}', 'buy_without_vat', 'SMALLINT(1) DEFAULT NULL');
            
");

$_websites = Mage::getModel('core/website')->getCollection();
$_stores = Mage::getModel('core/store')->getCollection();

$fields = array(
    
    'firstname' => array(0, '', 1,0),
    'lastname' => array(0, '', 1,0),
    'email' => array(0, '', 1,0),
    'company' => array(0, '', 0,0),
    'street' => array(1, '', 1,1),
    'country' => array(0, '', 1,0),
    'city' => array(0, '', 0,0),
    'region' => array(1, '', 1,0),
    'postcode' => array(1, '', 0,0),
    'telephone' => array(0, '', 0,0),
    'fax' => array(0, '', 0,0),
    'birthday' => array(1, 'remove', 0,0),
    'gender' => array(1, 'remove', 0,0),
    'taxvat' => array(1, 'remove', 0,0),
    'prefix_name' => array(1, 'remove', 0,0),
    'middlename' => array(1, 'remove', 0,0),
    'suffix' => array(1, 'remove', 0,0),
);
$postion = 0;
$right = 1;
foreach ($fields as $id => $value) {
    $fieldsModel = Mage::getModel('onestepcheckout/fieldsposition');
    $fieldsModel->setData('scope', 'default')
            ->setData('scope_id', 0)
            ->setData('path', $id)
            ->setData('remove', $value[1])
            ->setData('use_default', 1)
            ->setData('required', $value[2]);

    $fieldsModel->setData('width', $value[3]);
    $fieldsModel->setData('position', $postion);
    $postion++;

    try {
        $fieldsModel->save();
    } catch (Exception $e) {
        
    }
}

$installer->endSetup();

