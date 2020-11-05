<?php

class Stork_Shipcloud_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getTrackingNumber($id)
    {
        if($id!="")
        {
            $scData = Mage::getModel('shipcloud/shipcloud')->getCollection();
            $scData->addFieldToFilter('order_id',array('eq' => $id));

            foreach($scData as $values)
            {
                $trackingNumber = $values->getTrackingnumber();
            }
            return $trackingNumber;
        } else {
            return "";
        }
    }
    public function getServiceAccessData()
    {

        $strAccessType = Mage::getStoreConfig('shipcloud/profile/testing') == 1 ? 'test' : 'live';
        $apiKey = '';
        if ($strAccessType === 'test') {
            $apiKey = Mage::getStoreConfig('shipcloud/profile/shipcloud_sandbox_key');
        } elseif ($strAccessType === 'live') {
            $apiKey = Mage::getStoreConfig('shipcloud/profile/shipcloud_production_key');
        }
        $returnData = array(
            'acces_type' => $strAccessType,
            'api_key' => $apiKey,
            'password' => Mage::getStoreConfig('shipcloud/profile/shipcloud_account_password')
        );

        return $returnData;
    }
    public function getTrackingURL($id)
    {
        if ($id!="")
        {
            $scData = Mage::getModel('shipcloud/shipcloud')->getCollection();
            $scData->addFieldToFilter('order_id',array('eq' => $id));

            foreach($scData as $values)
            {
                $trackingURL = $values->getTrackingurl();
            }

            return $trackingURL;
        } else {
            return "";
        }
    }
}
