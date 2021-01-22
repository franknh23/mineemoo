<?php

class Xonu_Sepaone_Helper_Invoice extends Xonu_Directdebit_Helper_Invoice {

    public function createInvoice($order) {
        if(!Mage::getStoreConfigFlag('xonu_directdebit/sepaone/active')) {
            parent::createInvoice($order);
        }
    }

}