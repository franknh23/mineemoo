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
if ($version == '1.0.17') {
    return;
} elseif ($version != '1.0.16') {
    die('Please, run migration Rma 1.0.16');
}
$installer->startSetup();
if (Mage::registry('mst_allow_drop_tables')) {
    $sql = "
       DROP TABLE IF EXISTS `{$this->getTable('rma/fedex_label')}`;
    ";
    $installer->run($sql);
}
$sql = "
CREATE TABLE IF NOT EXISTS `{$this->getTable('rma/fedex_label')}` (
    `label_id` int(11) NOT NULL AUTO_INCREMENT,
    `rma_id` INT(11) NOT NULL,
    `package_number` INT(11) ,
    `track_number` VARCHAR(255) NOT NULL DEFAULT '',
    `label_date` TIMESTAMP NULL,
    `label_body` BLOB,
    KEY `fk_rma_fedex_label_rma_id` (`rma_id`),
    CONSTRAINT `mst_be47b58261860e56a17c37c31fe492e4` FOREIGN KEY (`rma_id`) REFERENCES `{$this->getTable('rma/rma')}` (`rma_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (`label_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

";
$installer->run($sql);

/*                                    **/

$installer->endSetup();
