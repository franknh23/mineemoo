<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
 class Advanced_Delivery_Model_Config_Dateformat extends Mage_Core_Model_Config_Data
    {

 
        public function toOptionArray()
        {
            return array(
                    
                 'd/M/Y' => Mage::helper('delivery')->__('d/M/Y'),
                'M/d/y' => Mage::helper('delivery')->__('M/d/y'),
                'd-M-Y' => Mage::helper('delivery')->__('d-M-Y'),
                'M-d-y' => Mage::helper('delivery')->__('M-d-y'),
                'm.d.y' => Mage::helper('delivery')->__('m.d.y'),
                'd.M.Y' => Mage::helper('delivery')->__('d.M.Y'),
                'M.d.y' => Mage::helper('delivery')->__('M.d.y'),
                'Y-m-d' => Mage::helper('delivery')->__('Y-m-d')
              
            );
        }

    }

