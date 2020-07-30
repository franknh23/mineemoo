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
    call AddColumnUnlessExists(DATABASE(), '{$this->getTable('sales/quote_address')}', 'customer_brazil_tax', 'varchar(255) DEFAULT NULL');
    call AddColumnUnlessExists(DATABASE(), '{$this->getTable('sales/order_address')}', 'brazil_tax', 'varchar(255) DEFAULT NULL');       
");

$setup = new Mage_Eav_Model_Entity_Setup('customer_setup');

$setup->addAttribute('customer_address', 'brazil_tax', array(
    'type' => 'varchar',
    'input' => 'select',
    'label' => 'Brazil Tax',
    'global' => 1,
    'source' => 'onestepcheckout/customer_attribute_braziltax',
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'visible_on_front' => 1
));
Mage::getSingleton('eav/config')
        ->getAttribute('customer_address', 'brazil_tax')
        ->setData('used_in_forms', array('customer_register_address', 'customer_address_edit', 'adminhtml_customer_address'))
        ->save();

$htmlConfigs = Mage::getModel('core/config_data')->getCollection()
        ->addFieldToFilter('path', 'customer/address_templates/html');

foreach ($htmlConfigs as $htmlConfig) {
    try {
        $value = $htmlConfig->getValue() . '{{depend brazil_tax}}<br/>Brazil Tax: {{var brazil_tax}}{{/depend}}';
        $htmlConfig->setValue($value)->save();
    } catch (Exception $e) {
        
    }
}

$pdfConfigs = Mage::getModel('core/config_data')->getCollection()
        ->addFieldToFilter('path', 'customer/address_templates/pdf');
foreach ($pdfConfigs as $pdfConfig) {
    try {
        $value = $pdfConfig->getValue() . '{{depend brazil_tax}}<br/>Brazil Tax: {{var brazil_tax}}{{/depend}}|';
        $pdfConfig->setValue($value)->save();
    } catch (Exception $e) {
        
    }
}
$installer->endSetup();

