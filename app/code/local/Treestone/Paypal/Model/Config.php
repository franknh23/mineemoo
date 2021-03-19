<?php

class Treestone_Paypal_Model_Config extends Mage_Paypal_Model_Config {
	
	 protected function _getDynamicImageUrl($type, $localeCode, $orderTotal, $pal)
    {
		
		return (Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'frontend/base/default/images/dynamicimageweb-'.$type.'.gif');
		
	}
}