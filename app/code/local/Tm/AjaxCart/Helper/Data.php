<?php

class Tm_AjaxCart_Helper_Data extends Mage_Core_Helper_Data
{
    /**
     * Get store instance
     * @return Mage_Core_Model_Store
     */
    private function getStore(){
        return Mage::app()->getStore();
    }

    /**
     * Get number of visible products from module options
     * @return number
     */
    public function getVisibleProductCount()
    {
        return abs((int)Mage::getStoreConfig('ajaxcart/minicart/visible_products', $this->getStore()));
    }

    /**
     * Checks if view more button should be displayed
     * @return bool
     */
    public function showViewMoreButton()
    {
        $productsCount = $this->getVisibleProductCount();
        $cart = Mage::getModel('checkout/cart')->getQuote();

        if ($cart->getItemsCount() > $productsCount) {
            return true;
        }
        return false;
    }

    /**
     * Check module options if module is active
     * @return number
     */
    public function isActive(){
        return abs((int)Mage::getStoreConfig('ajaxcart/general/active', $this->getStore()));
    }

    /**
     * Get stock validation status in module settings
     * @return number
     */
    public function getStockValidationStatus(){
        return abs((int)Mage::getStoreConfig('ajaxcart/general/stock_validation', $this->getStore()));
    }

    /**
     * Get info about product stock item
     * @param $product  Mage_Catalog_Model_Product
     * @return array
     */
    public function getStockData($product)
    {
        $stockData = array();
        $stock_item = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        $stockData['stock_qty'] = $stock_item->getStockQty();
        $stockData['min_stock_qty'] = $stock_item->getMinQty();

        return $stockData;
    }

    /**
     * Checks if there are enough products in stock to order  $purchasedQty
     * @param $stockData    array   $this->getStockData($product)
     * @param $purchasedQty int     Product purchased qty
     * @return bool
     */
    public function isStockEnough($stockData, $purchasedQty)
    {
        if( $stockData['stock_qty'] - $purchasedQty < $stockData['min_stock_qty']){
            return false;
        }
        return true;
    }


    /**
     * Validates stock of the purchased item
     * @param $product Mage_Catalog_Model_Product
     * @param $params array|int request params or qty integer
     * @return bool
     */
    public function validateStock($product, $params)
    {
        if(is_array($params)){
            $qty = $params['qty'];
        } else {
            $qty = (int)$params;
        }

        switch ($product->getTypeId()) {
            case 'configurable':
                $subProduct = $product->getTypeInstance()->getProductByAttributes($params['super_attribute'], $product);
                $stock_data = $this->getStockData($subProduct);
                break;
            default:
                $stock_data = $this->getStockData($product);
        }

        if(!$this->isStockEnough($stock_data, $qty)){
            return false;
        }
        return true;
    }

}