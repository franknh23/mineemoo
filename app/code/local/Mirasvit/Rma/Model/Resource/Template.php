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



class Mirasvit_Rma_Model_Resource_Template extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('rma/template', 'template_id');
    }

    protected function loadStoreIds(Mage_Core_Model_Abstract $object)
    {
        /* @var  Mirasvit_Rma_Model_Template $object */
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('rma/template_store'))
            ->where('ts_template_id = ?', $object->getId());
        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $array = array();
            foreach ($data as $row) {
                $array[] = $row['ts_store_id'];
            }
            $object->setData('store_ids', $array);
        }

        return $object;
    }

    protected function saveStoreIds($object)
    {
        /* @var  Mirasvit_Rma_Model_Template $object */
        $condition = $this->_getWriteAdapter()->quoteInto('ts_template_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('rma/template_store'), $condition);
        foreach ((array) $object->getData('store_ids') as $id) {
            $objArray = array(
                'ts_template_id' => $object->getId(),
                'ts_store_id' => $id,
            );
            $this->_getWriteAdapter()->insert(
                $this->getTable('rma/template_store'), $objArray);
        }
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        /** @var  Mirasvit_Rma_Model_Template $object */
        if (!$object->getIsMassDelete()) {
            $this->loadStoreIds($object);
        }

        return parent::_afterLoad($object);
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        /** @var  Mirasvit_Rma_Model_Template $object */
        if (!$object->getId()) {
            $object->setCreatedAt(Mage::getSingleton('core/date')->gmtDate());
        }
        $object->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate());

        return parent::_beforeSave($object);
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        /** @var  Mirasvit_Rma_Model_Template $object */
        if (!$object->getIsMassStatus()) {
            $this->saveStoreIds($object);
        }

        return parent::_afterSave($object);
    }

    /************************/
}
