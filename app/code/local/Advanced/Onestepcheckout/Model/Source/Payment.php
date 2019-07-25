<?php

class Advanced_Onestepcheckout_Model_Source_Payment {

    public function toOptionArray() {
        $options = array();
        $options[] = array(
            'label' => '--' . Mage::helper('onestepcheckout')->__('Please Select') . '--',
            'value' => ''
        );

        $storecode = Mage::app()->getRequest()->getParam('store');
        $website = Mage::app()->getRequest()->getParam('website');

        if ($storecode) {
            $scope = 'stores';
            $scopeId = (int) Mage::getConfig()->getNode('stores/' . $storecode . '/system/store/id');

        } elseif ($website) {
            $scope = 'websites';
            $scopeId = (int) Mage::getConfig()->getNode('websites/' . $website . '/system/website/id');
        } else {
            $scope = 'default';
            $scopeId = 0;
        }

        $methods = Mage::helper('payment')->getStoreMethods($scopeId);

        foreach ($methods as $key => $method) {
            $options[] = array(
                'label' => $method->getTitle(),
                'value' => $method->getCode()
            );
        }
        return $options;
    }

}
