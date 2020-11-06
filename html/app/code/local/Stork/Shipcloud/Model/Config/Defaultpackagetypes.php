<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marcellstork
 * Date: 20.01.15
 * Time: 11:47
 * To change this template use File | Settings | File Templates.
 */

class Stork_Shipcloud_Model_Config_Defaultpackagetypes
{
    public function toOptionArray()
    {
        /*return array(
          array('value' => 0, 'label' => 'First item'),
        );*/
        $M1 = array(
            array('label' => 'paket', 'value' => ''),
            array('label' => 'parcelletter', 'value' => 'parcel_letter'),
            array('label' => 'letter', 'value' => 'letter'),
            array('label' => 'books', 'value' => 'books')
        );
        return $M1;
    }
}
?>