<?php

class Meetanshi_CookieNotice_Model_Config_Fontfamily
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'unset', 'label' => __('Auto')),
            array('value' => 'Open+Sans', 'label' => __('Open Sans')),
            array('value' => 'Lato', 'label' => __('Lato')),
            array('value' => 'Old+Standard+TT', 'label' => __('Old Standard TT')),
            array('value' => 'Abril+Fatface', 'label' => __('Abril Fatface')),
            array('value' => 'PT+Serif', 'label' => __('PT Serif')),
            array('value' => 'Ubuntu', 'label' => __('Ubuntu')),
            array('value' => 'Vollkorn', 'label' => __('Vollkorn')),
        );
    }
}