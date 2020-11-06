<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class Stork_Shipcloud_Model_Config_Defaultcarrier
{
    public function toOptionArray()
    {
        /*return array(
          array('value' => 0, 'label' => 'First item'),
        );*/
        return array(
            array('label' => 'No Carrier', 'value' => 'nocarrier'),
            array('label' => 'UPS', 'value' => 'UPS'),
            array('label' => 'DHL', 'value' => 'DHL'),
            array('label' => 'HERMES', 'value' => 'HERMES'),
            array('label' => 'DPD', 'value' => 'DPD'),
            array('label' => 'GLS', 'value' => 'GLS'),
            array('label' => 'ILOXX', 'value' => 'ILOXX'),
            array('label' => 'FEDEX', 'value' => 'FEDEX'),
            array('label' => 'LIEFERY', 'value' => 'LIEFERY'),
            array('label' => 'DP AG', 'value' => 'DPAG')
        );
    }
}
