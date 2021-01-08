<?php

class Meetanshi_CookieNotice_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isEnable(){
        return Mage::getStoreConfig('cookienotice/general/enable');
    }
    public function isActive()
    {
        return !Mage::getStoreConfig('advanced/modules_disable_output/Meetanshi_CookieNotice');
    }

    public function getType()
    {
        return Mage::getStoreConfig('cookienotice/general/type');
    }

    public function getBarPosition()
    {
        return Mage::getStoreConfig('cookienotice/general/bar_position');
    }

    public function getBoxPosition()
    {
        return Mage::getStoreConfig('cookienotice/general/box_position');
    }

    public function getBehaviour()
    {
        return Mage::getStoreConfig('cookienotice/general/behaviour');
    }

    public function getAutohide()
    {
        return Mage::getStoreConfig('cookienotice/general/autohide');
    }

    public function getAutoAccept()
    {
        return Mage::getStoreConfig('cookienotice/general/autoaccept');
    }

    public function getAutoExpire()
    {
        return Mage::getStoreConfig('cookienotice/general/expire');
    }

    public function getCmsPage()
    {
        $pageId = Mage::getStoreConfig('cookienotice/content/cms_page');
        return Mage::helper('cms/page')->getPageUrl($pageId);
    }

    public function getShow()
    {
        return Mage::getStoreConfig('cookienotice/content/show');
    }

    public function getCustomMessage()
    {
        return Mage::getStoreConfig('cookienotice/content/custom_message');
    }

    public function getCustomMoreInfo()
    {
        return Mage::getStoreConfig('cookienotice/content/custom_more_info');
    }

    public function getCustomAccept()
    {
        return Mage::getStoreConfig('cookienotice/content/custom_accept');
    }

    public function getCustomDecline()
    {
        return Mage::getStoreConfig('cookienotice/content/custom_close');
    }

    public function acceptButtonBackgroundColor()
    {
        return Mage::getStoreConfig('cookienotice/popup_style/accept_button_background_color');
    }

    public function acceptButtonColor()
    {
        return Mage::getStoreConfig('cookienotice/popup_style/accept_button_color');
    }

    public function privacyPolicyBackgroundColor()
    {
        return Mage::getStoreConfig('cookienotice/popup_style/policy_button_background_color');
    }

    public function privacyPolicyColor()
    {
        return Mage::getStoreConfig('cookienotice/popup_style/privace_policy_color');
    }

    public function closeButtonBackgroundColor()
    {
        return Mage::getStoreConfig('cookienotice/popup_style/close_button_background_color');
    }

    public function closeButtonColor()
    {
        return Mage::getStoreConfig('cookienotice/popup_style/close_button_color');
    }

    public function headerBackgroundColor()
    {
        return Mage::getStoreConfig('cookienotice/popup_style/header_background_color');
    }

    public function headerFontColor()
    {
        return Mage::getStoreConfig('cookienotice/popup_style/header_font_color');
    }

    public function modelBorder()
    {
        return Mage::getStoreConfig('cookienotice/popup_style/model_border');
    }

    public function modelBorderColor()
    {
        return Mage::getStoreConfig('cookienotice/popup_style/model_border_color');
    }

    public function headerTextFontFamily()
    {
        return Mage::getStoreConfig('cookienotice/popup_style/font_family');
    }

    public function modelTitle()
    {
        return Mage::getStoreConfig('cookienotice/content/model_title');
    }

    public function onScroll()
    {
        return Mage::getStoreConfig('cookienotice/general/onScroll');
    }

    public function modelTitleColor()
    {
        return Mage::getStoreConfig('cookienotice/popup_style/model_title_color');
    }

    public function modelTitleFontSize()
    {
        return Mage::getStoreConfig('cookienotice/popup_style/model_title_size');
    }

    public function modelMessageSize()
    {
        return Mage::getStoreConfig('cookienotice/popup_style/model_message_size');
    }

    public function modelTextAlign()
    {
        return Mage::getStoreConfig('cookienotice/popup_style/model_text_align');
    }
}