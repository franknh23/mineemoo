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



class Mirasvit_Rma_Model_Resource_Comment extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('rma/comment', 'comment_id');
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        /** @var  Mirasvit_Rma_Model_Comment $object */
        if (!$object->getIsMassDelete()) {
        }

        return parent::_afterLoad($object);
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        /** @var  Mirasvit_Rma_Model_Comment $object */
        if (!$object->getId()) {
            $object->setCreatedAt(Mage::getSingleton('core/date')->gmtDate());
        }
        $object->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate());

        return parent::_beforeSave($object);
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        /** @var  Mirasvit_Rma_Model_Comment $object */
        if (!$object->getIsMassStatus()) {
        }

        return parent::_afterSave($object);
    }

    /************************/
}
