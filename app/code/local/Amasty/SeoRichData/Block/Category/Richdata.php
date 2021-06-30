<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_SeoRichData
 */

class Amasty_SeoRichData_Block_Category_Richdata extends Mage_Core_Block_Template
{
    protected $_reviewSummaryInfo;
    protected $_landingPage;
    protected $_visible;

    protected $collection;

    protected function _construct()
    {
        parent::_construct();

        $page = Mage::registry('amlanding_page');

        if ($page) {
            $this->_landingPage = $page;

            $this->_visible = Mage::getStoreConfigFlag('amseorichdata/category/landing');
        } elseif (Mage::app()->getRequest()->getModuleName() == 'amshopby') {
            $this->_visible = Mage::getStoreConfigFlag('amseorichdata/category/navigation');
        } else {
            $this->_visible = Mage::getStoreConfigFlag('amseorichdata/category/enabled');
        }
    }

    protected function _toHtml()
    {
        if ($this->_visible && $this->getCollection()->getSize()) {
            $data = json_encode($this->getResult());
            return "<script type=\"application/ld+json\">{$data}</script>";
        } else {
            return '';
        }
    }

    /**
     * @return array
     */
    public function getResult()
    {
        $data = array();
        /** @var Amasty_SeoRichData_Block_Product_Richdata $productBlock */
        $productBlock = $this->getLayout()->createBlock(
            'amseorichdata/product_richdata'
        );

        foreach ($this->getCollection() as $product) {
            $productBlock->setProduct($product);
            $productInfo = $productBlock->getResultArray();
            if (isset($productInfo['product'])) {
                $data[] = $productInfo['product'];
            }
        }

        return $data;
    }

    public function getCollection()
    {
        if ($this->collection === null) {
            if ($this->_landingPage) {
                $this->collection = Mage::getSingleton('catalog/layer')->getProductCollection();
            } else {
                $this->collection = $this->getLayout()
                    ->getBlockSingleton('catalog/product_list')
                    ->getLoadedProductCollection();
            }
        }

        return $this->collection;
    }

    public function getName()
    {
        if ($this->_landingPage) {
            return $this->_landingPage->getTitle();
        } else {
            return Mage::registry('current_category')->getName();
        }
    }

    public function getMinimalPrice($isFormatted = true)
    {
        $collection = clone $this->getCollection();

        $collection->clear();
        $collection->addPriceData();

        $collection->getSelect()
            ->reset(Zend_Db_Select::ORDER)
            ->order('min_price ASC')
            ->limit(1)
        ;

        $price = 0;

        if ($product = $collection->getFirstItem()) {
            $price = Mage::helper('tax')->getPrice($product, $product->getMinPrice());
        }

        if ($isFormatted) {
            $price = Mage::getModel('directory/currency')->format(
                $price,
                array('display' => Zend_Currency::NO_SYMBOL),
                false
            );
        }

        return $price;
    }

    protected function _getSummaryInfo()
    {
        if (!$this->_reviewSummaryInfo) {
            $select = clone $this->getCollection()->getSelect();
            $resource = $this->getCollection()->getResource();

            $select
                ->reset(Varien_Db_Select::COLUMNS)
                ->reset(Varien_Db_Select::ORDER)
                ->reset(Varien_Db_Select::LIMIT_COUNT)
                ->reset(Varien_Db_Select::LIMIT_OFFSET)
                ->reset(Varien_Db_Select::GROUP)
                ->join(
                    array('summary' => $resource->getTable('review/review_aggregate')),
                    'summary.entity_pk_value = e.entity_id',
                    array('rating' => 'AVG(summary.rating_summary)', 'reviews' => 'SUM(summary.reviews_count)')
                )
                ->where('summary.store_id = ?', Mage::app()->getStore()->getId())
                ->where('summary.reviews_count > 0')
            ;

            $this->_reviewSummaryInfo = new Varien_Object();
            $this->_reviewSummaryInfo->setData($resource->getReadConnection()->fetchRow($select));
        }

        return $this->_reviewSummaryInfo;
    }

    public function getReviewsCount()
    {
        return $this->_getSummaryInfo()->getReviews();
    }

    public function getRatingSummary()
    {
        return $this->_getSummaryInfo()->getRating();
    }

    public function getVotesCount()
    {
        $select = clone $this->getCollection()->getSelect();

        $resource = $this->getCollection()->getResource();

        $select
            ->reset(Varien_Db_Select::COLUMNS)
            ->reset(Varien_Db_Select::ORDER)
            ->reset(Varien_Db_Select::LIMIT_COUNT)
            ->reset(Varien_Db_Select::LIMIT_OFFSET)
            ->reset(Varien_Db_Select::GROUP)
            ->join(
                array('votes' => $resource->getTable('rating/rating_vote_aggregated')),
                'votes.entity_pk_value = e.entity_id',
                array('vote_count' => 'SUM(vote_count)')
            )
            ->where('votes.store_id=?', Mage::app()->getStore()->getId());

        return $resource->getReadConnection()->fetchOne($select);
    }

    /**
     * @return bool
     */
    public function isReviewEnabled()
    {
        return Mage::getStoreConfig('amseorichdata/rating/enabled')
            && Mage::helper('core')->isModuleEnabled('Mage_Review')
            && $this->getReviewsCount();
    }
}
