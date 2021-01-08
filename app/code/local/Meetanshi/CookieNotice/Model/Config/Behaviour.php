<?php

class Meetanshi_CookieNotice_Model_Config_Behaviour
{
    public function toOptionArray()
    {
        return array(
            array('value' => 365, 'label' => 'Never show again'),
            array('value' => 1, 'label' => 'Hide for the rest of the day'),
            array('value' => 0, 'label' => 'Hide for the rest of the session')
        );
    }
}