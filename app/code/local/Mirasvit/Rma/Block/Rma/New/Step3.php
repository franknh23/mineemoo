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



class Mirasvit_Rma_Block_Rma_New_Step3 extends Mirasvit_Rma_Block_Rma_New
{
    /**
     * @return Mirasvit_Rma_Model_Rma
     */
    public function getRma() {
        return Mage::registry('current_rma');
    }

    /**
     * @return bool
     */
    public function getShippingBlockIsEnabled()
    {
        return (int)$this->getConfig()->getGeneralShippingStepBlock() > 0;
    }

    /**
     * @var Mage_Cms_Model_Block
     */
    protected $addblock;

    /**
     * @return Mage_Cms_Model_Block
     */
    public function getShippingBlock()
    {
        if (!$this->addblock) {
            $this->addblock = Mage::getModel('cms/block')->load($this->getConfig()->getGeneralShippingStepBlock());
        }

        return $this->addblock;
    }

    /**
     * @return string
     */
    public function getShippingBlockTitle()
    {
        return $this->getShippingBlock()->getTitle();
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getShippingBlockContent()
    {
        $content = '';
        if ($this->getShippingBlock()) {
            $content = $this->getShippingBlock()->getContent();
        }

        return Mage::helper('cms')->getPageTemplateProcessor()->filter($content);
    }

    /**
     * @param Mirasvit_Rma_Model_Rma $rma
     * @return string
     */
    public function getSuccessUrl($rma)
    {
        return Mage::getUrl('rma/rma_new/success', array('id' => $rma->getId()));
    }
}
