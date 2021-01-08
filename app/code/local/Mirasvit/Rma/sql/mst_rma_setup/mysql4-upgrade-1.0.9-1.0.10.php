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
if ($version == '1.0.10') {
    return;
} elseif ($version != '1.0.9') {
    die('Please, run migration Rma 1.0.9');
}
$installer->startSetup();
$sql = "
ALTER TABLE `{$this->getTable('rma/comment')}` ADD COLUMN `is_read` TINYINT(1) NOT NULL DEFAULT 0;
UPDATE `{$this->getTable('rma/comment')}` SET is_read=1;
";
$helper = Mage::helper('rma/migration');
$helper->trySql($installer, $sql);

/*                                    **/

$installer->endSetup();
