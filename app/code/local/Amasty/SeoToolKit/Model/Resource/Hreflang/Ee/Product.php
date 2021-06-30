<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


class Amasty_SeoToolKit_Model_Resource_Hreflang_Ee_Product extends Amasty_SeoToolKit_Model_Resource_Hreflang_Ee_Abstract
{
    protected $entityPath = 'catalog/product/view/id/';

    /**
     * @param array $productIds
     * @param array $storeIds
     * @return Varien_Db_Select
     */
    public function getSelect($productIds, $storeIds)
    {
        $productIds = $this->getPreparedIds($productIds);
        $select = parent::getSelect($productIds, $storeIds);
        $select->where('entity_type = ?', Enterprise_Catalog_Model_Product::URL_REWRITE_ENTITY_TYPE)
            ->order('store_id ASC'); //specific store values will overwrite defaults
        return $select;
    }
}
