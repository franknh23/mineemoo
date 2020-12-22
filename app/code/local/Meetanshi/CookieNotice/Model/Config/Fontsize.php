<?php

class Meetanshi_CookieNotice_Model_Config_Fontsize
{
    public function toOptionArray()
    {
        return array(
            array('value' => 14, 'label' => 'Small'),
            array('value' => 18, 'label' => 'Medium'),
            array('value' => 22, 'label' => 'Large')
        );
    }
}