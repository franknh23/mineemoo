<?php

class Meetanshi_CookieNotice_Model_Config_Message
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'default', 'label' => 'Default message'),
            array('value' => 'custom', 'label' => 'Custom message')
        );
    }
}