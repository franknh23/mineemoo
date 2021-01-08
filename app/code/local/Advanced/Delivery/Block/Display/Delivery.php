<?php

class Advanced_Delivery_Block_Display_Delivery extends Mage_Core_Block_Template {

    public function __construct() {
        if (Mage::getStoreConfig('delivery/general/enabled')) {
            $this->setTemplate('delivery/sales/view.phtml');
        }
    }

}
