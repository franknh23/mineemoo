<?php

class Meetanshi_CookieNotice_Model_Config_Box_Position
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'v-top-left', 'label' => 'Top left'),
            array('value' => 'v-top-right', 'label' => 'Top right'),
            array('value' => 'v-bottom-left', 'label' => 'Bottom left'),
            array('value' => 'v-bottom-right', 'label' => 'Bottom right')
        );
    }
}