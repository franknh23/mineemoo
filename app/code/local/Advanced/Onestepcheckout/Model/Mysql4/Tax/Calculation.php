<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Tax
 * @copyright  Copyright (c) 2006-2014 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tax Calculation Resource Model
 *
 * @category    Mage
 * @package     Mage_Tax
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Advanced_Onestepcheckout_Model_Mysql4_Tax_Calculation extends Mage_Tax_Model_Mysql4_Calculation {

    protected function _getRates($request) {

        $storeId = Mage::app()->getStore($request->getStore())->getId();

        $select = $this->_getReadAdapter()->select();
        $select
                ->from(array('main_table' => $this->getMainTable()))
                ->where('customer_tax_class_id = ?', $request->getCustomerClassId());
        if ($request->getProductClassId()) {
            $select->where('product_tax_class_id IN (?)', $request->getProductClassId());
        }

        if ($ruleIds = $request->getDisableByRule()) {

            $select->join(
                    array('rule' => $this->getTable('tax/tax_calculation_rule')), sprintf('rule.tax_calculation_rule_id = main_table.tax_calculation_rule_id AND rule.tax_calculation_rule_id not in (%s)', $ruleIds), array('rule.priority', 'rule.position')
            );
        } else {

            $select->join(
                    array('rule' => $this->getTable('tax/tax_calculation_rule')), 'rule.tax_calculation_rule_id = main_table.tax_calculation_rule_id', array('rule.priority', 'rule.position')
            );
        }

        $select->join(
                array('rate' => $this->getTable('tax/tax_calculation_rate')), 'rate.tax_calculation_rate_id = main_table.tax_calculation_rate_id', array('value' => 'rate.rate', 'rate.tax_country_id', 'rate.tax_region_id', 'rate.tax_postcode', 'rate.tax_calculation_rate_id', 'rate.code')
        );

        $select->joinLeft(
                array('title_table' => $this->getTable('tax/tax_calculation_rate_title')), "rate.tax_calculation_rate_id = title_table.tax_calculation_rate_id AND title_table.store_id = '{$storeId}'", array('title' => 'IFNULL(title_table.value, rate.code)')
        );

        $select
                ->where("rate.tax_country_id = ?", $request->getCountryId())
                ->where("rate.tax_region_id in ('*', '', ?)", $request->getRegionId());

        $selectClone = clone $select;

        $select
                ->where("rate.zip_is_range IS NULL")
                ->where("rate.tax_postcode in ('*', '', ?)", $this->_createSearchPostCodeTemplates($request->getPostcode()));

        $selectClone
                ->where("rate.zip_is_range IS NOT NULL")
                ->where("? BETWEEN rate.zip_from AND rate.zip_to", $request->getPostcode());

        /**
         * @see ZF-7592 issue http://framework.zend.com/issues/browse/ZF-7592
         */
        $select = $this->_getReadAdapter()->select()->union(array('(' . $select . ')', '(' . $selectClone . ')'));
        $order = array('priority ASC', 'tax_calculation_rule_id ASC', 'tax_country_id DESC', 'tax_region_id DESC', 'tax_postcode DESC', 'value DESC');
        $select->order($order);

        return $this->_getReadAdapter()->fetchAll($select);
    }

}
