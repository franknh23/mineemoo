<?php
class Advanced_Cartreminder_Block_Cartreminder extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getCartreminder()     
     { 
        if (!$this->hasData('cartreminder')) {
            $this->setData('cartreminder', Mage::registry('cartreminder'));
        }
        return $this->getData('cartreminder');
        
    }
}