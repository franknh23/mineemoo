<?php 

class Tm_AjaxCart_Helper_Image extends Mage_Core_Helper_Abstract
{
    /**
     * @return Mage_Core_Model_Store
     */
    private function getStore(){
        return Mage::app()->getStore();
    }

    /**
     * Get product image width from module options
     * @return number
     */
    public function minicartProductImageWidth()
    {
        return abs((int)Mage::getStoreConfig('ajaxcart/minicart/img_width', $this->getStore()));
    }

    /**
     * Get product image height from module options
     * @return number
     */
    public function minicartProductImageHeight()
    {
        return abs((int)Mage::getStoreConfig('ajaxcart/minicart/img_height', $this->getStore()));
    }
}