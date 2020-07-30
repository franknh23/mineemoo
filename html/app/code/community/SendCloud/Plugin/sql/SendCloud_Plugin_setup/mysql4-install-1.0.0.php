<?php
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

$salesFlatOrderTable = $installer->getTable('sales_flat_order');
$salesFlatOrderGridTable = $installer->getTable('sales_flat_order_grid');

$salesFlatOrderTableProps = $connection->describeTable($salesFlatOrderTable);
$salesFlatOrderGridTableProps = $connection->describeTable($salesFlatOrderGridTable);

if (!isset($salesFlatOrderTableProps['sendcloud_service_point'])) {
    $connection->addColumn($salesFlatOrderTable, 'sendcloud_service_point', 'INT(11)');
}

if (!isset($salesFlatOrderTableProps['sendcloud_service_point'])) {
    $connection->addColumn($salesFlatOrderGridTable, 'sendcloud_service_point', 'INT(11)');
}

if (!isset($salesFlatOrderGridTableProps['sendcloud_service_point_extra'])) {
    $connection->addColumn($salesFlatOrderTable, 'sendcloud_service_point_extra', 'TEXT');
}

if (!isset($salesFlatOrderGridTableProps['sendcloud_service_point_extra'])) {
    $connection->addColumn($salesFlatOrderGridTable, 'sendcloud_service_point_extra', 'TEXT');
}

$installer->endSetup();
