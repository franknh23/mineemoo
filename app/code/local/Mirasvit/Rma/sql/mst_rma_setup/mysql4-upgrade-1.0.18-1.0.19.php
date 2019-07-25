<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   RMA
 * @version   2.1.0-beta
 * @build     1359
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$version = Mage::helper('mstcore/version')->getModuleVersionFromDb('mst_rma');
if ($version == '1.0.19') {
    return;
} elseif ($version != '1.0.18') {
    die('Please, run migration Rma 1.0.18');
}
$installer->startSetup();
$sql = "
ALTER TABLE `{$this->getTable('rma/item')}` ADD COLUMN `order_id` INT(11) UNSIGNED AFTER `order_item_id`;

ALTER TABLE `{$this->getTable('rma/item')}` ADD COLUMN `offline_order_name` VARCHAR(255) NULL DEFAULT NULL AFTER `order_id`;

ALTER TABLE `{$this->getTable('rma/item')}` ADD KEY `fk_rma_rma_order_id` (`order_id`);

ALTER TABLE `{$this->getTable('rma/rma')}` ADD COLUMN `offline_address` VARCHAR(512) NULL AFTER `postcode`;

ALTER TABLE `{$this->getTable('rma/rma')}` DROP FOREIGN KEY `mst_69eaead6204d31508d9b7cddf50050ea`;

ALTER TABLE `{$this->getTable('rma/item')}`
  ADD CONSTRAINT `mst_69eaead6204d31508d9b7cddf50050ea`
  FOREIGN KEY (`order_id`)
  REFERENCES `{$this->getTable('sales/order')}` (`entity_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;
";

//$sql .= "
//ALTER TABLE `{$this->getTable('rma/rma')}` DROP FOREIGN KEY `mst_69eaead6204d31508d9b7cddf50050ea`;
//
//ALTER TABLE `{$this->getTable('rma/rma')}` DROP COLUMN IF EXISTS `order_id`, DROP INDEX IF EXISTS `fk_rma_rma_order_id` ;
//";


$helper = Mage::helper('rma/migration');
$helper->trySql($installer, $sql);

$installer->endSetup();
