<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


abstract class Amasty_SeoToolKit_Model_Resource_Hreflang_Ee_Abstract extends Mage_Core_Model_Resource_Db_Abstract
{
    protected $entityPath;

    protected function _construct()
    {
        $this->_init('enterprise_urlrewrite/url_rewrite', 'url_rewrite_id');
    }

    /**
     * @param array $entityIds
     * @param array $storeIds
     * @return Varien_Db_Select
     */
    public function getSelect($entityIds, $storeIds)
    {
        $adapter = $this->_getReadAdapter();
        // Transform "catalog/category[product]/view/id/12" => 12
        $idFunction = $adapter->quoteInto('REPLACE(target_path,?,"")', $this->entityPath);

        $select = $adapter->select()
            ->from(
                array('main_table' => $this->getTable('enterprise_urlrewrite/url_rewrite')),
                array('store_id', 'entity_id' => $idFunction, 'request_path')
            )->where('target_path IN(?)', $entityIds)
            ->where('store_id IN(?)', $storeIds)
            ->where('is_system = ?', 1);

        return $select;
    }

    /**
     * @param array $entityIds
     * @param string $prefix
     * @return array
     */
    protected function getPreparedIds($entityIds)
    {
        return array_map(function($val) {
            return $this->entityPath . $val;
        }, $entityIds);
    }
}
