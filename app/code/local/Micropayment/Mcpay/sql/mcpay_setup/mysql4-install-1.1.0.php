<?php

/** @var $installer Micropayment_Mcpay_Model_Resource_Setup */
$installer = $this;

/**
 * Prepare database for install
 */
$installer->startSetup();

/**
 * Create table 'paypal/settlement_report'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('mcpay_paydata'))
    ->addColumn('paydata_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Paydata Id')
    ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(), 'Account Id')
    ->addColumn('paymethod', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(), 'Paymethod')
    ->addColumn('data', Varien_Db_Ddl_Table::TYPE_TEXT, NULL, array(), 'Serialized Data')
    ->addColumn('last_modified', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Last Modified')
    ->addIndex(
      $installer->getIdxName(
        'mcpay_paydata',
        array('user_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
      ),
      array('user_id'),
      array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
    )
    ->setComment('Micropayment Paydata Table');
$installer->getConnection()->createTable($table);

/**
 * Prepare database after install
 */
$installer->endSetup();

