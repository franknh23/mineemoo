<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_SeoRichData
 */


class Amasty_SeoRichData_Block_Product_Breadcrumbs extends Mage_Catalog_Block_Product_Abstract
{
    protected $_result;

    public function _toHtml()
    {
        if (!Mage::helper('amseorichdata')->isBreadcrumbEnabled()) {
            return '';
        }

        $path  = Mage::helper('catalog')->getBreadcrumbPath();

        $itemListElement = array();

        foreach ($path as $category) {
            if (empty($category['link']) || empty($category['label']))
                continue;

            $this->pushBreadcrumb($itemListElement, $category['label'], $category['link']);
        }
        if (Mage::helper('amseorichdata')->isLastBreadcrumbEnabled()) {
            $item = Mage::registry('current_product');

            if ($item) {
                $item->setData('url', null);
                $item->setData('request_path', null);
                $lastUrl = Mage::getModel('catalog/product_url')->getProductUrl($item);
            } else {
                $item = Mage::registry('current_category');
                if ($item) {
                    $lastUrl = $item->getUrl();
                }
            }

            if (isset($lastUrl)) {
                $this->pushBreadcrumb($itemListElement, $item->getName(), $lastUrl);
            }
        }

        $data['breadcrumbs'] = array(
            '@context' => 'http://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemListElement
        );

        foreach ($data as $section) {
            $json = json_encode($section);
            $this->_result .= "<script type=\"application/ld+json\">{$json}</script>";
        }

        return parent::_toHtml() . $this->_result;
    }

    /**
     * @param array $itemListElement
     * @param string $label
     * @param string $link
     */
    private function pushBreadcrumb(&$itemListElement, $label, $link)
    {
        $itemListElement[] = array(
            '@type' => 'ListItem',
            'position' => count($itemListElement) + 1,
            'item' => array(
                '@id' => $link,
                'name' => $label
            )
        );
    }
}
