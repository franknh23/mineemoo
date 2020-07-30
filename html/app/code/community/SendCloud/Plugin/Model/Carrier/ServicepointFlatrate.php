<?php


class SendCloud_Plugin_Model_Carrier_ServicepointFlatrate
    extends SendCloud_Plugin_Model_Carrier_Abstract
{
    protected $_code = 'servicepoint_flatrate';

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!Mage::getStoreConfig('sendcloud/servicepoint')) {
            return false;
        }


        if ($this->getConfigData('free_shipping_enable')
                && $request->getBaseSubtotalInclTax() >= $this->getConfigData('free_shipping_subtotal')) {
            $request->setFreeShipping(true);
        }

        return parent::collectRates($request);
    }
}
