<?php
class SendCloud_Plugin_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function panelUrl()
    {
        $panelUrl = getenv('SENDCLOUDSHIPPING_PANEL_URL');

        if (empty($panelUrl)) {
            $panelUrl = 'https://panel.sendcloud.sc';
        }

        return $panelUrl;
    }

    public function servicePointURL()
    {
        $servicePointUrl = getenv('SENDCLOUDSHIPPING_SERVICE_POINT_URL');

        if (empty($servicePointUrl)) {
            $servicePointUrl = 'https://servicepoints.sendcloud.sc';
        }

        return $servicePointUrl;
    }

    public function getBaseUrl()
    {
        $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        return rtrim($baseUrl, '/');
    }
}
