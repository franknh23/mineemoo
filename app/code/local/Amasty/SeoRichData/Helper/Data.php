<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_SeoRichData
 */

class Amasty_SeoRichData_Helper_Data extends Mage_Core_Helper_Abstract
{
    const HTTP_FORMAT = 'https';

    const BREADCRUMB_FINAL_ITEM = 'breadcrumbs/final_item';

    const BREADCRUMB_ENABLED = 'breadcrumbs/enabled';

    public function isYotpoReviewsEnabled()
    {
        return Mage::helper('core')->isModuleEnabled('Yotpo_Yotpo');
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param int $price
     * @return int
     */
    public function getProductPrice($product, $price = null)
    {
        if ($this->isAmastyConfEnabled()) {
            $product = Mage::app()->getLayout()->getBlockSingleton('amconf/catalog_product_price')->getProduct();
        }

        if (is_null($price)) {
            $price = $product->getFinalPrice();
        }

        if (Mage::getStoreConfig('amseorichdata/product/price_incl_tax')) {
            /** @var Mage_Tag_Helper_Data $taxHelper */
            $taxHelper = Mage::helper('tax');
            $price = $taxHelper->getPrice($product, $price, true);
        }

        if ($product->getTypeId() == 'grouped') {
            $price = $this->getGroupedPrice($product);
        }

        if ($product->getTypeId() == 'bundle') {
            $priceModel = $product->getPriceModel();

            list($minimalPrice, $maximalPrice) = $priceModel->getTotalPrices($product, null, null, false);
            list($minimalPriceInclTax, $maximalPriceInclTax) = $priceModel->getTotalPrices($product, null, true, false);

            $price = $minimalPrice;
            if (Mage::getStoreConfig('amseorichdata/product/price_incl_tax')) {
                $price = $minimalPriceInclTax;
            }
        }

        $price = Mage::app()->getStore()->convertPrice($price, false, false);
        return round($price, 2);
    }

    public function isAmastyConfEnabled()
    {
        return (string)Mage::getConfig()->getNode('modules/Amasty_Conf/active') == 'true';
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return int
     */
    public function getGroupedPrice($product)
    {
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addMinimalPrice()
            ->addIdFilter($product->getId())
            ->setPageSize(1);

        //$ogPrice = number_format((float)$ogPrice, 2, '.', '');

        return $collection->getFirstItem()->getMinimalPrice();
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getProductDescription($product)
    {
        $code = Mage::getStoreConfig('amseorichdata/product/use_short_description')
            ? 'short_description' : 'description';
        $description =Mage::getResourceModel('catalog/product')->getAttributeRawValue($product->getId(), $code, Mage::app()->getStore()->getStoreId());
        return strip_tags($description);
    }

    /**
     * @param string $path
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getModuleConfig($path, $storeId = null)
    {
        if (!$storeId) {
            $storeId = Mage::app()->getStore()->getId();
        }

        return Mage::getStoreConfig('amseorichdata/' . $path, $storeId);
    }

    /**
     * @return bool
     */
    public function isBreadcrumbEnabled()
    {
        return (bool)$this->getModuleConfig(
            self::BREADCRUMB_ENABLED
        );
    }

    /**
     * @return bool
     */
    public function isLastBreadcrumbEnabled()
    {
        return (bool)$this->getModuleConfig(
            self::BREADCRUMB_FINAL_ITEM
        );
    }

    /**
     * @return mixed
     */
    public function getBrandAttribute()
    {
        return $this->getModuleConfig('product/brand');
    }

    /**
     * @return array
     */
    public function getCustomAttributes()
    {
        return explode(
            ',',
            $this->getModuleConfig('product/custom_prop')
        );
    }
}
