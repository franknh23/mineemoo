<?php

class Meetanshi_CookieNotice_Model_Config_Textalign
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'left', 'label' => 'Left'),
            array('value' => 'center', 'label' => 'Center'),
            array('value' => 'right', 'label' => 'Right')
        );
    }
}