<?php

class Meetanshi_CookieNotice_Model_Config_Type
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'v-bar', 'label' => 'Bar'),
            array('value' => 'v-box', 'label' => 'Popup')
        );
    }
}