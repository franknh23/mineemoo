<?php

class Tm_AjaxCart_Model_Source_Sample_Layouts
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'sample_value1', 'label'=>Mage::helper('adminhtml')->__('Sample Value1')),
            array('value' => 'sample_value2', 'label'=>Mage::helper('adminhtml')->__('Sample Value2')),
            array('value' => 'sample_value3', 'label'=>Mage::helper('adminhtml')->__('Sample Value3')),
        );
    }
}