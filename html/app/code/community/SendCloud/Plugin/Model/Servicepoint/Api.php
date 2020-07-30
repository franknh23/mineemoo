<?php

class SendCloud_Plugin_Model_Servicepoint_Api extends Mage_Api_Model_Resource_Abstract
{
    public function activate($scriptUrl)
    {
        if (strpos($scriptUrl, Mage::helper('SendCloud_Plugin')->servicePointURL()) === 0) {
            $this->_updateServicePointUrl($scriptUrl);
            return true;
        }
        return false;
    }

    public function deactivate()
    {
        $this->_updateServicePointUrl('');
        return true;
    }

    private function _updateServicePointUrl($scriptUrl)
    {
        $config = new Mage_Core_Model_Config();
        $config->saveConfig('sendcloud/servicepoint', $scriptUrl);
    }
}
