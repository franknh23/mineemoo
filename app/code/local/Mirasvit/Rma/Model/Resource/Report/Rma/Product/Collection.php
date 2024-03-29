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



class Mirasvit_Rma_Model_Resource_Report_Rma_Product_Collection extends Mage_Sales_Model_Mysql4_Report_Collection_Abstract
{
    protected $_periodFormat;
    protected $_reportType;
    protected $_selectedColumns = array();

    public function __construct()
    {
        parent::_construct();
        $this->setModel('adminhtml/report_item');
        $this->_resource = Mage::getResourceModel('sales/report')->init('rma/item');
        $this->setConnection($this->getResource()->getReadConnection());
    }

    protected function _applyDateRangeFilter()
    {
        if (!is_null($this->_from)) {
            $this->getSelect()->where($this->_periodFormat.' >= ?', $this->_from);
        }
        if (!is_null($this->_to)) {
            $this->getSelect()->where($this->_periodFormat.' <= ?', $this->_to);
        }

        return $this;
    }


    public function _applyStoresFilter()
    {
        $this->getSelect()->where('store_id = 0');
//        return $this->_applyStoresFilterToSelect($this->getSelect());
    }
    public function setFilterData($filterData)
    {
        if (isset($filterData['report_type'])) {
            $this->_reportType = $filterData['report_type'];
        }
        return $this;
    }

    protected function _getSelectedColumns()
    {
        if ('month' == $this->_period) {
            $this->_periodFormat = 'DATE_FORMAT(main_table.created_at, \'%Y-%m\')';
        } elseif ('year' == $this->_period) {
            $this->_periodFormat = 'EXTRACT(YEAR FROM main_table.created_at)';
        } else {
            $this->_periodFormat = 'DATE_FORMAT(main_table.created_at, \'%Y-%m-%d\')';
        }

        $this->_selectedColumns = array(
            'created_at' => $this->_periodFormat,
            'qty_returns' => 'count(qty_requested)',
            'qty_items' => 'sum(qty_requested)',
        );

        if ($this->_reportType == 'resolution') {
            $reasons = Mage::getModel('rma/resolution')->getCollection()
                ->addFieldToFilter('is_active', true);
        } elseif ($this->_reportType == 'condition') {
                $reasons = Mage::getModel('rma/condition')->getCollection()
                    ->addFieldToFilter('is_active', true);
        } else {
            $reasons = Mage::getModel('rma/reason')->getCollection()
                ->addFieldToFilter('is_active', true);

        }

        foreach ($reasons as $reason) {
            $this->_selectedColumns['reason'.$reason->getId()] = 'SUM(if(reason_id = '.$reason->getId().', 1, 0))';
        }


        // if ($this->isTotals()) {
        // }

        // if ($this->isSubTotals()) {
        // }
        return $this->_selectedColumns;
    }

    protected function _initSelect()
    {
        $select = $this->getSelect();
        $select->from(array('main_table' => $this->getResource()->getMainTable()), $this->_getSelectedColumns());

        $select->joinLeft(array('product' => $this->getTable('catalog/product')), 'main_table.product_id = product.entity_id', array('product_sku' => 'product.sku'));        //$select->joinLeft(array('customer' => $this->getTable('customer/customer')), 'main_table.customer_id = customer.customer_id', array('customer_name' => 'customer.name'));
        // $select->joinLeft(array('product_varchar' => $this->getTable('catalog/product')), 'main_table.product_id = product.entity_id', array('product_sku' => 'product.sku'));        //$select->joinLeft(array('customer' => $this->getTable('customer/customer')), 'main_table.customer_id = customer.customer_id', array('customer_name' => 'customer.name'));


        // alias then field name
        $productAttributes = array('product_name' => 'name');
        foreach ($productAttributes as $alias => $attributeCode) {
            $tableAlias = $attributeCode.'_table';
            $attribute = Mage::getSingleton('eav/config')
            ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeCode);

            //Add eav attribute value
            $this->getSelect()->joinLeft(
                    array($tableAlias => $attribute->getBackendTable()),
                    "main_table.product_id = $tableAlias.entity_id AND $tableAlias.attribute_id={$attribute->getId()}",
                    array($alias => 'value')
            );
        }
        $select->where('qty_requested > 0 and store_id = 0');

        if (!$this->isTotals() && !$this->isSubTotals()) {
            //поля по которым будут сделаны группировки при выводе отчета
            $select->group(array(
                $this->_periodFormat,
                'product_id',
            ));
        }
        if ($this->isSubTotals()) {
            $select->group(array(
                $this->_periodFormat,
            ));
        }

//                 echo $this->getSelect();
        return $this;
    }

    /************************/
}
