<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_SeoRichData
 */


class Amasty_SeoRichData_Block_Product_Richdata extends Mage_Catalog_Block_Product_Abstract
{
    const MPN_IDENTIFIER = 'mpn';
    const SKU_IDENTIFIER = 'sku';

    /**
     * @return array
     */
    public function getResultArray()
    {
        /** @var Amasty_SeoRichData_Helper_Data $helper */
        $helper = Mage::helper('amseorichdata');

        $product = $this->getProduct();
        if (!$product) {
            $product = Mage::registry('current_product') ?
                Mage::registry('current_product') :
                Mage::registry('product');
        }
        $priceCurrency = Mage::app()->getStore()->getCurrentCurrencyCode();
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();

        $offers[] = array();

        $showAvailability = false;
        $showCondition = false;

        if (Mage::getStoreConfig('amseorichdata/product/show_availability')) {
            $showAvailability = true;
        }

        if (Mage::getStoreConfig('amseorichdata/product/show_condition')) {
            $showCondition = true;
        }

        if ($product->getTypeId() == 'configurable'
            && ($offerMode = Mage::getStoreConfig('amseorichdata/product/show_configurable_list'))
        ) {
            $attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);
            //array to keep the price differences for each attribute value
            $pricesByAttributeValues = array();
            //base price of the configurable product
            $basePrice = $product->getFinalPrice();
            //loop through the attributes and get the price adjustments specified in the configurable product admin page
            foreach ($attributes as $attribute) {
                $prices = $attribute->getPrices();
                foreach ($prices as $price) {
                    if ($price['is_percent']) { //if the price is specified in percents
                        $pricesByAttributeValues[$price['value_index']] = (float)$price['pricing_value'] * $basePrice / 100;
                    } else { //if the price is absolute value
                        $pricesByAttributeValues[$price['value_index']] = (float)$price['pricing_value'];
                    }
                }
            }

            //get all simple products
            $simple = $product->getTypeInstance()->getUsedProductCollection();
            $simple->addAttributeToSelect('*');
            if ($offerMode == Amasty_SeoRichData_Model_Source_Offer::AGGREGATE) {
                $minPrice = INF;
                $maxPrice = 0;
                $offerCount = 0;
            }
            //loop through the products
            foreach ($simple as $sProduct) {
                if ($this->_useSimplePrice()) {
                    $price = $helper->getProductPrice($sProduct, $sProduct->getFinalPrice());
                } else {
                    $totalPrice = $basePrice;
                    //loop through the configurable attributes
                    foreach ($attributes as $attribute) {
                        //get the value for a specific attribute for a simple product
                        $value = $sProduct->getData($attribute->getProductAttribute()->getAttributeCode());
                        //add the price adjustment to the total price of the simple product
                        if (isset($pricesByAttributeValues[$value])) {
                            $totalPrice += $pricesByAttributeValues[$value];
                        }
                    }
                    $price = $helper->getProductPrice($sProduct, $totalPrice);
                }
                if ($offerMode == Amasty_SeoRichData_Model_Source_Offer::AGGREGATE) {
                    $offerCount++;
                    $maxPrice = max($maxPrice, $price);
                    $minPrice = min($minPrice, $price);
                } else {
                    $offers[] = $this->generateOffers($sProduct, $priceCurrency, $currentUrl, true, $price);
                }
            }
            if ($offerMode == Amasty_SeoRichData_Model_Source_Offer::AGGREGATE) {
                $offers[] = array(
                    '@type' => 'AggregateOffer',
                    'lowPrice' => $minPrice,
                    'highPrice' => $maxPrice,
                    'offerCount' => $offerCount,
                    'priceCurrency' => $priceCurrency
                );
            }
        } elseif ($product->getTypeId() == 'grouped' && Mage::getStoreConfig('amseorichdata/product/show_grouped_list')) {
            $products = $product->getTypeInstance()->getAssociatedProducts();
            if (Mage::getStoreConfig('amseorichdata/product/show_grouped_list') ==
                Amasty_SeoRichData_Model_Source_Offer::AGGREGATE
            ) {
                $offers[] = $this->generateAggregateOffers($products, $priceCurrency);
            } else {
                $offers = $this->_prepareMassOffers($products, $priceCurrency, $currentUrl);
            }
        } else {
            $offers[] = $this->generateOffers($product, $priceCurrency, $currentUrl);
        }

        if (!$showAvailability) {
            foreach ($offers as $key => $offer) {
                if (isset($offer['availability'])) {
                    unset($offers[$key]['availability']);
                }
            }
        }

        if (!$showCondition) {
            foreach ($offers as $key => $offer) {
                if (isset($offer['itemCondition'])) {
                    unset($offers[$key]['itemCondition']);
                }
            }
        }

        $data['product'] = array(
            '@context' => 'http://schema.org',
            '@type' => 'Product',
            'name' => $product->getName(),
            'description' => $helper->getProductDescription($product),
            'image' => $product->getImageUrl(),
            'url' => Mage::helper('core/url')->getCurrentUrl(),
            'offers' => $offers
        );

        if ($brandInfo = $this->getBrandInfo($product)) {
            $data['product']['brand'] = $brandInfo;
        }

        if (Mage::getStoreConfig('amseorichdata/rating/enabled')
            && Mage::helper('core')->isModuleEnabled('Mage_Review')
        ) {
            if (is_object($product->getRatingSummary()) && $product->getRatingSummary()->getReviewsCount() > 0) {
                $ratingValue = $product->getRatingSummary()->getRatingSummary();
                $ratingCount = $product->getRatingSummary()->getReviewsCount();
            } else {
                $storeId = Mage::app()->getStore()->getId();
                $summaryData = Mage::getModel('review/review_summary')
                    ->setStoreId($storeId)
                    ->load($product->getId());

                $ratingValue = $summaryData->getRatingSummary();
                $ratingCount = $summaryData->getReviewsCount();
            }

            if ($ratingCount && $ratingValue) {
                $data['product']['aggregateRating'] = array(
                    '@type' => 'AggregateRating',
                    'ratingValue' => round($ratingValue, 2),
                    'bestRating' => 100
                );

                $showTotals = Mage::getStoreConfig('amseorichdata/rating/totals');

                if ($showTotals == 2 || $showTotals == 3) {
                    $data['product']['aggregateRating']['ratingCount'] = $this->_getProductVotes($product);
                }

                if ($showTotals == 1 || $showTotals == 3) {
                    $data['product']['aggregateRating']['reviewCount'] = $ratingCount;
                }
            }

            if (Mage::getStoreConfigFlag('amseorichdata/yotpo/enabled') &&
                $helper->isYotpoReviewsEnabled()) {
                $reviews = $this->helper('yotpo/richSnippets')->getRichSnippet();

                $data['product']['aggregateRating'] = array(
                    '@type' => 'AggregateRating',
                    'ratingValue' => $reviews["average_score"] ? $reviews["average_score"] : 0,
                    'reviewCount' => $reviews["reviews_count"] ? $reviews["reviews_count"] : 0
                );
            }

            $data['product']['review'] = $this->getReviews($product);
        }

        $this->updateCustomProperties($data, $product);

        return $data;
    }

    public function getResult()
    {
        $data = $this->getResultArray();
        $result = '';

        foreach ($data as $section) {
            $json = json_encode($section);
            $result .= "<script type=\"application/ld+json\">{$json}</script>";
        }

        return $result;
    }

    protected function _useSimplePrice()
    {
        $useSimplePrice = false;

        if (Mage::helper('amseorichdata')->isAmastyConfEnabled()) {
            /** @var Amasty_Conf_Helper_Data $amconfHelper */
            $amconfHelper = Mage::helper('amconf');
            $useSimplePrice = (
                $amconfHelper->getConfigUseSimplePrice() == 2 //2 - Yes for All Products
                || ($amconfHelper->getConfigUseSimplePrice() == 1
                    && $this->getProduct()->getData('amconf_simple_price')) //1 - Yes for Specified Products
            ) ? true : false;
        }

        return $useSimplePrice;
    }

    /**
     * @param $products
     * @param $priceCurrency
     *
     * @return array
     */
    private function generateAggregateOffers($products, $priceCurrency)
    {
        /** @var Amasty_SeoRichData_Helper_Data $helper */
        $helper = Mage::helper('amseorichdata');
        $minPrice = INF;
        $maxPrice = 0;
        $offerCount = 0;

        foreach ($products as $sProduct) {
            $price = $helper->getProductPrice($sProduct);
            $minPrice = min($minPrice, $price);
            $maxPrice = max($maxPrice, $price);
            $offerCount++;
        }

        return array(
            '@type' => 'AggregateOffer',
            'lowPrice' => $minPrice,
            'highPrice' => $maxPrice,
            'offerCount' => $offerCount,
            'priceCurrency' => $priceCurrency
        );
    }


    protected function _prepareMassOffers($products, $priceCurrency, $currentUrl)
    {
        $offers = array();
        foreach ($products as $sProduct) {
            $offers[] = $this->generateOffers($sProduct, $priceCurrency, $currentUrl, true);
        }

        return $offers;
    }

    public function _toHtml()
    {
        return parent::_toHtml() . $this->getResult();
    }

    protected function _getProductVotes($product)
    {
        $adapter = $product->getResource()->getReadConnection();
        $select = $adapter->select()->from($product->getResource()->getTable('rating/rating_vote_aggregated'), 'vote_count')
            ->where('store_id=?', Mage::app()->getStore()->getId())
            ->where('entity_pk_value=?', $product->getId())
            ->limit(1)
        ;

        return $adapter->fetchOne($select);
    }

    /**
     * @param $product
     *
     * @return null|string|Zend_Date
     */
    protected function getPriceValid($product)
    {
        $priceValid = null;
        if ($product->getSpecialPrice()
            && $product->getSpecialToDate()
            && Varien_Date::toTimestamp(date('Y-m-d')) <= Varien_Date::toTimestamp($product->getSpecialToDate())
        ) {
            $priceValid = new Zend_Date(
                Varien_Date::toTimestamp($product->getSpecialToDate())
            );
            $priceValid = $priceValid->getIso();
        }

        return $priceValid;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array|null
     */
    protected function getBrandInfo($product)
    {
        $info = null;
        $brand = Mage::helper('amseorichdata')->getBrandAttribute();
        if ($brand 
            && $product->getResource()->getAttribute($brand)
            && ($attributeValue = $product->getAttributeText($brand))
        ) {
            if (is_array($attributeValue)) {
                $info = array();
                foreach ($attributeValue as $value) {
                    $info[] = array(
                        '@type' => 'Thing',
                        'name' => $value
                    );
                }
            } else {
                $info = array(
                    '@type' => 'Thing',
                    'name'  => $attributeValue
                );
            }
        }

        return $info;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return array
     */
    protected function getReviews($product)
    {
        $reviews[] = array();
        $reviewCollection = Mage::getModel('review/review')->getCollection()
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
            ->addEntityFilter('product', $product->getId())
            ->setDateOrder();
        foreach ($reviewCollection as $review) {
            $rating = Mage::getModel('rating/rating')->getReviewSummary($review->getId());
            $reviews[] = array(
                '@type' => 'Review',
                'author' => $review->getNickname(),
                'datePublished' => $review->getCreatedAt(),
                'reviewBody' => $review->getDetail(),
                'name' => $review->getTitle(),
                'reviewRating' => array(
                    '@type' => 'Rating',
                    'ratingValue' => round($rating->getSum() / $rating->getCount(), 2),
                    'bestRating' => 100
                )
            );
        }

        return $reviews;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param string $priceCurrency
     * @param string $currentUrl
     * @param bool $child
     * @param null|string $price
     *
     * @return array
     */
    protected function generateOffers($product, $priceCurrency, $currentUrl, $child = false, $price = null)
    {
        if (!$price) {
            $price = Mage::helper('amseorichdata')->getProductPrice($product);
        }
        $offer = array(
            '@type'         => 'Offer',
            'priceCurrency' => $priceCurrency,
            'price'         => $price,
            'availability'  => $product->isAvailable() ?
                'http://schema.org/InStock' :
                'http://schema.org/OutOfStock',
            'seller'        => array(
                '@type' => 'Organization',
                'name'  => Mage::getStoreConfig('design/header/logo_alt')
            ),
            'url' => $currentUrl
        );
        if ($child) {
            $offer['itemOffered'] = array(
                '@type'         => 'Product',
                'name'          => $product->getName(),
                'sku'           => $product->getSku(),
                'itemCondition' => 'http://schema.org/NewCondition'
            );
        }
        if ($priceValid = $this->getPriceValid($product)) {
            $offer['priceValidUntil'] = $priceValid;
        }

        return $offer;
    }

    /**
     * @param array $result
     * @param Mage_Catalog_Model_Product $product
     */
    protected function updateCustomProperties(&$result, $product)
    {
        foreach (Mage::helper('amseorichdata')->getCustomAttributes() as $customAttribute) {
            $customAttribute = trim($customAttribute);
            if ($customAttribute
                && $product->getResource()->getAttribute($customAttribute)
                && $product->getData($customAttribute)
            ) {
                $result['product'][$customAttribute] = $product->getAttributeText($customAttribute)
                    ? $product->getAttributeText($customAttribute)
                    : $product->getData($customAttribute);
            } elseif ($customAttribute == self::MPN_IDENTIFIER && $product->getData(self::SKU_IDENTIFIER)) {
                $result['product'][self::MPN_IDENTIFIER] = $product->getData(self::SKU_IDENTIFIER);
            }
        }
    }
}
