<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
 class Advanced_Delivery_Model_Config_Dayoff extends Mage_Core_Model_Config_Data
    {

 
        public function toOptionArray()
        {
            $test_array[7] = array(
                'value' => '',
                'label' => Mage::helper('delivery')->__('N/A'),
            );
            $test_array[0] = array(
                'value' => 0,
                'label' => Mage::helper('delivery')->__('Sunday'),
            );
            $test_array[1] = array(
                'value' => 1,
                'label' => Mage::helper('delivery')->__('Monday'),
            );
            $test_array[2] = array(
                'value' => 2,
                'label' => Mage::helper('delivery')->__('Tuesday'),
            );
            $test_array[3] = array(
                'value' => 3,
                'label' => Mage::helper('delivery')->__('Wedenesday'),
            );
            $test_array[4] = array(
                'value' => 4,
                'label' => Mage::helper('delivery')->__('Thursday'),
            );
            $test_array[5] = array(
                'value' => 5,
                'label' => Mage::helper('delivery')->__('Friday'),
            );
            $test_array[6] = array(
                'value' => 6,
                'label' => Mage::helper('delivery')->__('Saturday'),
            );
            
            return $test_array;
        }

    }

