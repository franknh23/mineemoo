<?php

/**
 * Advanced Checkout
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Onestepcheckout.com license that is
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

class Advanced_Onestepcheckout_Model_Config_Source_Taxrules {

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {

        $tax_calculation_rule_table = Mage::getSingleton('core/resource')->getTableName('tax/tax_calculation_rule');
        $q = "SELECT `tax_calculation_rule_id`, `code` FROM {$tax_calculation_rule_table}";
        $rules = Mage::getSingleton('core/resource')->getConnection('read')->fetchPairs($q);

        $options = array();
        $options[] = array(
            'value' => '0',
            'label' => Mage::helper('onestepcheckout')->__('None')
        );

        foreach ($rules as $code => $name) {
            $options[] = array(
                'value' => $code,
                'label' => $name
            );
        }

        return $options;
    }

}
