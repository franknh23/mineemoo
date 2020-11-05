<?php

class Stork_Shipcloud_Block_Adminhtml_Sales_Order_Grid_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
      $shipcloud = Mage::getModel('shipcloud/shipcloud')->load($row->getId(),'order_id');

      if(!empty($shipcloud) && !empty($shipcloud->getData())){
        return $shipcloud->getShippingStatus();
      }
        return '<a href="'.Mage::helper("adminhtml")->getUrl("shipcloud/adminhtml_shipcloud/save",array("order_ids" => $row->getId())).'">'.$this->__('Create').'</a>';
    }
}
