<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


require_once Mage::getModuleDir('controllers', 'Mage_Cms').DS.'IndexController.php';
class Amasty_SeoToolKit_NorouteController extends Mage_Cms_IndexController
{
    /**
     * @var null|string
     */
    protected $_extension = null;

    public function indexAction($coreRoute = null)
    {
        $pathInfo = $this->_retrieveRequestInfo();
        if ($this->_isNeedRedirect()) {
            $searchUrl = Mage::helper('catalogsearch')->getResultUrl($this->_getSearchQuery($pathInfo));
            $this->getResponse()->setRedirect($searchUrl);
        } else {
            $this->noRouteAction();
        }
    }

    /**
     * @param string $pathInfo
     * @return string
     */
    protected function _getSearchQuery($pathInfo)
    {
        $regExp = '/catalog\/(category|product)\/view\/id\/\d+/';
        preg_match($regExp, $pathInfo, $matches);
        $model = isset($matches[1]) ? $matches[1] : '';

        if ($model) {
            $id = $this->getRequest()->getParam('id');
            $currentModel = Mage::getModel('catalog/' . $model)->load($id);
            $query = $currentModel->getData('url_key');
            $query = str_replace('-', ' ', $query);
        } else {
            $query = trim($pathInfo, '/');
            $query = str_replace('/', ' ', $query);
            $query = str_replace('.html', '', $query);
        }

        return $query;
    }

    /**
     * Retrieve path info, save extension of requested data.
     *
     * @return string
     */
    protected function _retrieveRequestInfo()
    {
        $pathInfo = $this->getRequest()->getPathInfo();
        $urlParts = explode('.', $pathInfo);
        if (count($urlParts) > 1) {
            $this->_extension = end($urlParts);
        }

        return $pathInfo;
    }

    /**
     * @return bool
     */
    protected function _isNeedRedirect()
    {
        return (!$this->_extension || $this->_extension == 'html')
            && !$this->getRequest()->isXmlHttpRequest();
    }
}
