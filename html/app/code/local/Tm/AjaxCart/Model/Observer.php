<?php

class Tm_AjaxCart_Model_Observer
{
    public function inspectProductData($observer = null)
    {
        $event = $observer->getEvent();
        ZEND_DEBUG::dump(111);
        exit;
    }
}