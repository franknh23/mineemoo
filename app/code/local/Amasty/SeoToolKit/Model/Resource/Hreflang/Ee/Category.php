<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


class Amasty_SeoToolKit_Model_Resource_Hreflang_Ee_Category extends Amasty_SeoToolKit_Model_Resource_Hreflang_Ee_Abstract
{
    protected $entityPath = 'catalog/category/view/id/';

    /**
     * @param array $categoryIds
     * @param array $storeIds
     * @return Varien_Db_Select
     */
    public function getSelect($categoryIds, $storeIds)
    {
        $categoryIds = $this->getPreparedIds($categoryIds);
        $select = parent::getSelect($categoryIds, $storeIds);
        $select->where('entity_type = ?', Enterprise_Catalog_Model_Category::URL_REWRITE_ENTITY_TYPE);
        return $select;
    }
}
