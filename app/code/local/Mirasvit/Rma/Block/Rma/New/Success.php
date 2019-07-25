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



class Mirasvit_Rma_Block_Rma_New_Success extends Mirasvit_Rma_Block_Rma_New
{
    /**
     * @return Mirasvit_Rma_Model_Rma
     */
    public function getRma()
    {
        return Mage::registry('current_rma');
    }

    /**
     * @var Mage_Cms_Model_Block
     */
    protected $successblock;

    /**
     * @return bool
     */
    public function getSuccessBlockIsEnabled() {
        return (int)$this->getConfig()->getGeneralSuccessStepBlock() > 0;
    }

    /**
     * @return Mage_Cms_Model_Block
     */
    public function getSuccessBlock()
    {
        if (!$this->successblock) {
            $this->successblock = Mage::getModel('cms/block')
                ->load($this->getConfig()->getGeneralSuccessStepBlock());
        }

        return $this->successblock;
    }

    /**
     * @return string
     */
    public function getSuccessBlockTitle()
    {
        return $this->getSuccessBlock()->getTitle();
    }

    /**
     * @return string
     *
     * @throws Exception
     */
    public function getSuccessBlockContent()
    {
        $helper = Mage::helper('cms');
        $processor = $helper->getPageTemplateProcessor();

        return $processor->filter($this->getSuccessBlock()->getContent());
    }

    /**
     * @return string
     */
    public function getRmaListUrl()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($customer && $customer->getId()) {
            return Mage::helper('rma/url')->getRmaListUrl();
        } else {
            return Mage::helper('rma/url')->getGuestRmaListUrl();
        }
    }
}
