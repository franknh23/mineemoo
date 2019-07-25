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



class Mirasvit_Rma_Helper_Validator extends Mirasvit_MstCore_Helper_Validator_Abstract
{
    public function testMagentoCrc()
    {
        $filter = array(
            'app/code/Mage/Core',
            'js',
        );

        return Mage::helper('mstcore/validator_crc')->testMagentoCrc($filter);
    }

    public function testEnterpriseRMA()
    {
        $result = self::SUCCESS;
        $title = 'Enterprise RMA';
        $description = array();
        if (Mage::helper('mstcore')->isModuleInstalled('Enterprise_Rma')) {
            $result = self::INFO;
            $description[] = 'Enterprise RMA Module is installed and conflicts with Mirasvit RMA. To disable it, rename the file app/etc/modules/Enterprise_RMA.xml to app/etc/modules/Enterprise_RMA.xml.bak and flush the cache.';
        }

        return array($result, $title, $description);
    }

    public function testMirasvitCrc()
    {
        $modules = array('Rma');

        return Mage::helper('mstcore/validator_crc')->testMirasvitCrc($modules);
    }

    public function testISpeedCache()
    {
        $result = self::SUCCESS;
        $title = 'My_Ispeed';
        $description = array();
        if (Mage::helper('mstcore')->isModuleInstalled('My_Ispeed')) {
            $result = self::INFO;
            $description[] = 'Extension My_Ispeed is installed. Please, go to the Configuration > Settings > I-Speed > General Configuration and add \'rma\' to the list of Ignored URLs. Then clear ALL cache.';
        }

        return array($result, $title, $description);
    }

    public function testMgtVarnishCache()
    {
        $result = self::SUCCESS;
        $title = 'Mgt_Varnish';
        $description = array();
        if (Mage::helper('mstcore')->isModuleInstalled('Mgt_Varnish')) {
            $result = self::INFO;
            $description[] = 'Extension Mgt_Varnish is installed. Please, go to the Configuration > Settings > MGT-COMMERCE.COM > Varnish and add \'rma\' to the list of Excluded Routes. Then clear ALL cache.';
        }

        return array($result, $title, $description);
    }

    public function testTableStructure()
    {
        $structure = array(
            'customer/entity' => array(),
            'admin/user' => array(),
            'rma/comment' => array(
                'comment_id' => 'int(11)',
                'rma_id' => 'int(11)',
                'user_id' => 'int(10) unsigned',
                'customer_id' => 'int(10) unsigned',
                'customer_name' => 'varchar(255)',
                'text' => 'text',
                'is_html' => 'tinyint(1)',
                'is_visible_in_frontend' => 'tinyint(1)',
                'is_customer_notified' => 'tinyint(1)',
                'status_id' => 'int(11)',
                'created_at' => 'timestamp',
                'updated_at' => 'timestamp',
                'email_id' => 'int(11)',
                'is_read' => 'tinyint(1)',
            ),
            'rma/condition' => array(
                'condition_id' => 'int(11)',
                'name' => 'varchar(255)',
                'sort_order' => 'smallint(5)',
                'is_active' => 'tinyint(1)',
            ),
            'rma/field' => array(
                'field_id' => 'int(11)',
                'name' => 'varchar(255)',
                'code' => 'varchar(255)',
                'type' => 'varchar(255)',
                'values' => 'text',
                'description' => 'text',
                'is_active' => 'tinyint(1)',
                'sort_order' => 'smallint(5)',
                'is_required_staff' => 'tinyint(1)',
                'is_required_customer' => 'tinyint(1)',
                'is_visible_customer' => 'tinyint(1)',
                'is_editable_customer' => 'tinyint(1)',
                'visible_customer_status' => 'varchar(255)',
                'is_show_in_confirm_shipping' => 'tinyint(1)',
            ),
            'rma/item' => array(
                'item_id' => 'int(11)',
                'rma_id' => 'int(11)',
                'product_id' => 'int(11)',
                'order_item_id' => 'int(11)',
                'order_id' => 'int(11) unsigned',
                'reason_id' => 'int(11)',
                'resolution_id' => 'int(11)',
                'condition_id' => 'int(11)',
                'qty_requested' => 'int(11)',
                'qty_returned' => 'int(11)',
                'created_at' => 'timestamp',
                'updated_at' => 'timestamp',
                'name' => 'varchar(255)',
                'product_options' => 'text',
                'to_stock' => 'tinyint(1)',
            ),
            'rma/reason' => array(
                'reason_id' => 'int(11)',
                'name' => 'varchar(255)',
                'sort_order' => 'smallint(5)',
                'is_active' => 'tinyint(1)',
            ),
            'rma/resolution' => array(
                'resolution_id' => 'int(11)',
                'name' => 'varchar(255)',
                'sort_order' => 'smallint(5)',
                'is_active' => 'tinyint(1)',
                'code' => 'varchar(255)',
            ),
            'rma/rma' => array(
                'rma_id' => 'int(11)',
                'increment_id' => 'varchar(255)',
                'guest_id' => 'varchar(255)',
                'firstname' => 'varchar(255)',
                'lastname' => 'varchar(255)',
                'company' => 'varchar(255)',
                'telephone' => 'varchar(255)',
                'email' => 'varchar(255)',
                'street' => 'varchar(255)',
                'city' => 'varchar(255)',
                'region' => 'varchar(255)',
                'region_id' => 'int(11)',
                'country_id' => 'varchar(255)',
                'postcode' => 'varchar(255)',
                'customer_id' => 'int(10) unsigned',
                'status_id' => 'int(11)',
                'store_id' => 'smallint(5) unsigned',
                'tracking_code' => 'varchar(255)',
                'is_resolved' => 'tinyint(1)',
                'created_at' => 'timestamp',
                'updated_at' => 'timestamp',
                'ticket_id' => 'int(11)',
                'user_id' => 'int(11)',
                'last_reply_name' => 'varchar(255)',
                'last_reply_at' => 'timestamp',
                'is_gift' => 'tinyint(1)',
                'exchange_order_id' => 'int(11)',
                'credit_memo_id' => 'int(11)',
                'is_admin_read' => 'tinyint(1)',
            ),
            'rma/rma_creditmemo' => array(
                'rma_creditmemo_id' => 'int(11)',
                'rc_rma_id' => 'int(11)',
                'rc_credit_memo_id' => 'int(11)',
            ),
            'rma/rma_order' => array(
                'rma_order_id' => 'int(11)',
                're_rma_id' => 'int(11)',
                're_exchange_order_id' => 'int(11)',
            ),
            'rma/rma_store' => array(
                'rma_store_id' => 'int(11)',
                'rs_rma_id' => 'int(11)',
                'rs_store_id' => 'smallint(5) unsigned',
            ),
            'rma/rule' => array(
                'rule_id' => 'int(11)',
                'name' => 'varchar(255)',
                'event' => 'varchar(255)',
                'email_subject' => 'varchar(255)',
                'email_body' => 'text',
                'is_active' => 'int(11)',
                'conditions_serialized' => 'text',
                'is_send_owner' => 'tinyint(1)',
                'is_send_department' => 'tinyint(1)',
                'is_send_customer' => 'tinyint(1)',
                'other_email' => 'varchar(255)',
                'sort_order' => 'smallint(5)',
                'is_stop_processing' => 'tinyint(1)',
                'status_id' => 'int(11)',
                'user_id' => 'int(10) unsigned',
                'is_send_attachment' => 'tinyint(1)',
                'is_resolved' => 'smallint(5)',
            ),
            'rma/status' => array(
                'status_id' => 'int(11)',
                'name' => 'varchar(255)',
                'sort_order' => 'smallint(5)',
                'is_rma_resolved' => 'tinyint(1)',
                'customer_message' => 'text',
                'admin_message' => 'text',
                'history_message' => 'text',
                'is_active' => 'tinyint(1)',
                'code' => 'varchar(255)',
            ),
            'rma/template' => array(
                'template_id' => 'int(11)',
                'name' => 'varchar(255)',
                'template' => 'text',
                'is_active' => 'tinyint(1)',
            ),
            'rma/template_store' => array(
                'template_store_id' => 'int(11)',
                'ts_template_id' => 'int(11)',
                'ts_store_id' => 'smallint(5) unsigned',
            ),
            'rma/fedex_label' => array(
                'label_id' => 'int(11)',
                'rma_id' => 'int(11)',
                'package_number' => 'int(11)',
                'track_number' => 'varchar(255)',
                'label_date' => 'timestamp',
                'label_body' => 'blob',
            ),
        );

        $dbCheck = $this->dbCheckTables(array_keys($structure));
        if ($dbCheck[0] != self::SUCCESS) {
            return array(self::FAILED, 'RMA Database Structure', $dbCheck[2]);
        }

        $title = 'RMA Database Structure';
        $description = array();
        foreach (array_keys($structure) as $tableName) {
            // Pass 0: If table record has empty array - check is not performed
            if (!count($structure[$tableName])) {
                continue;
            }

            // Pass 1: Check for missing fields (sqlResult can not be reset for some reason)
            foreach (array_keys($structure[$tableName]) as $field) {
                $exists = false;
                $sqlResult = $this->_dbConn()->query('DESCRIBE '.$this->_dbRes()->getTableName($tableName).';');
                foreach ($sqlResult as $sqlRow) {
                    if (!$exists && $sqlRow['Field'] == $field) {
                        $exists = true;
                    }
                }
                if (!$exists) {
                    $description[] = $this->_dbRes()->getTableName($tableName).' has missing field: '.$field;
                }
            }

            // Pass 2: Check for types and alteration
            $sqlResult = $this->_dbConn()->query('DESCRIBE '.$this->_dbRes()->getTableName($tableName).';');
            foreach ($sqlResult as $sqlRow) {
                if (array_key_exists($sqlRow['Field'], $structure[$tableName])) {
                    if ($sqlRow['Type'] != $structure[$tableName][$sqlRow['Field']]) {
                        $description[] = $this->_dbRes()->getTableName($tableName).' has different structure, field '.$sqlRow['Field'].' has type '.$sqlRow['Type'];
                    }
                }
            }
        }

        return (count($description)) ?
            array(self::FAILED, $title, array_merge($description, array('Contact Mirasvit Support.'))) :
            array(self::SUCCESS, $title, $description);
    }

    /**
     * Check permissions variable/blocks for emails.
     * Applies only to new magento versions and patched magento.
     *
     * @return array
     */
    public function testPermissionBlocksVariables()
    {
        $title = 'Blocks/Variables permissions';
        $path = Mage::getBaseDir('code').'/core/Mage/Admin/Model/Block.php';
        if (!file_exists($path)) {
            return array(self::INFO, $title, array('Skipped. Old magento version'));
        }
        $description = array();
        $blocks = array(
            'rma/rma_view_items',
        );

        foreach ($blocks as $block) {
            $collection = Mage::getModel('admin/block')->getCollection()
                ->addFieldToFilter('block_name', $block);
            if ($collection->count() == 0) {
                $description[] = "Permission for block $block is missing. Please, add via System > Permissions > Blocks.";
            }
        }

        return (count($description)) ?
            array(self::FAILED, $title, $description) :
            array(self::SUCCESS, $title, $description);
    }

    /**
     * Plz, add only tests for our BUGS into this function.
     * Always check that bug is present in current configuration of extension.
     *
     * critical bugs should return FAILED
     * less critical bugs should return INFO
     * small bugs should not be here
     *
     * @return array
     */
    public function testKnownIssues()
    {
        $result = self::SUCCESS;
        $version = Mage::helper('rma/code')->_version();
        $buildNumber = Mage::helper('rma/code')->_build();
        $title = 'Test for Known Issues (Version: '.$version.'.'.$buildNumber.')';
        $description = array();

        // started af68332
        // fixed in 898911b
        if ($buildNumber >= 854 && $buildNumber <= 1082) {
            $description[] = 'You may have a bug - workflow rules are not sorted by priority and processing can stop even if rule was not executed [RMA-111]. Update extension.';
            $result = self::FAILED;
        }

        // started 77a7cdccc
        // fixed in gfe769bf
        if ($buildNumber >= 684 && $buildNumber <= 1178) {
            $description[] = 'You may have a bug - customer can request RMA for an order, which exceeds RMA return period, via Guest RMA form [RMA-143]. Update extension.';
            $result = self::FAILED;
        }

        return array($result, $title, $description);
    }
}
