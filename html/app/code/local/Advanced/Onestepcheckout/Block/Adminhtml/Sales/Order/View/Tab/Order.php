<?php 
class Avanced_Onestepcheckout_Block_Adminhtml_Sales_Order_View_Tab_Extra
	extends Mage_Adminhtml_Block_Template
    implements Mage_Adminhtml_Block_Widget_Tab_Interface {
	
	public function _construct()	{
            
		parent::_construct();
		$this->setTemplate('onestepcheckout/sales/order/view/tab/extra.phtml');
	}

	public function canShowTab()	{
			return true;
	}

	public function isHidden()	{
			return false;
	}

	public function getOrder() {
		return Mage::registry('current_order');
	}
		
}