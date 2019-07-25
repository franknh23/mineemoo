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



/**
 * @method Mirasvit_Rma_Model_Item getFirstItem()
 * @method Mirasvit_Rma_Model_Item getLastItem()
 * @method Mirasvit_Rma_Model_Resource_Item_Collection|Mirasvit_Rma_Model_Item[] addFieldToFilter
 * @method Mirasvit_Rma_Model_Resource_Item_Collection|Mirasvit_Rma_Model_Item[] setOrder
 */
class Mirasvit_Rma_Model_Resource_Item_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('rma/item');
    }

    public function toOptionArray($emptyOption = false)
    {
        $arr = array();
        if ($emptyOption) {
            $arr[0] = array('value' => 0, 'label' => Mage::helper('rma')->__('-- Please Select --'));
        }
        /** @var Mirasvit_Rma_Model_Item $item */
        foreach ($this as $item) {
            $arr[] = array('value' => $item->getId(), 'label' => $item->getName());
        }

        return $arr;
    }

    public function getOptionArray($emptyOption = false)
    {
        $arr = array();
        if ($emptyOption) {
            $arr[0] = Mage::helper('rma')->__('-- Please Select --');
        }
        /** @var Mirasvit_Rma_Model_Item $item */
        foreach ($this as $item) {
            $arr[$item->getId()] = $item->getName();
        }

        return $arr;
    }

    public function getOrdersId()
    {
        $this->getSelect()
            ->group('offline_order_name');

        return $this;
    }

    protected function initFields()
    {
        $select = $this->getSelect();
        $select->joinLeft(array('reason' => $this->getTable('rma/reason')), 'main_table.reason_id = reason.reason_id', array('reason_name' => 'reason.name'));
        $select->joinLeft(array('resolution' => $this->getTable('rma/resolution')), 'main_table.resolution_id = resolution.resolution_id', array('resolution_name' => 'resolution.name'));
        $select->joinLeft(array('condition' => $this->getTable('rma/condition')), 'main_table.condition_id = condition.condition_id', array('condition_name' => 'condition.name'));
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $this->initFields();
    }

     /************************/
}
