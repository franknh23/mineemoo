<?php
class Stork_Shipcloud_Block_Shipcloud extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getShipcloud()     
     { 
        if (!$this->hasData('shipcloud')) {
            $this->setData('shipcloud', Mage::registry('shipcloud'));
        }
        return $this->getData('shipcloud');
        
    }
}