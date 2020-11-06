<?php
class Stork_Shipcloud_Block_Adminhtml_Sales_Order_Shipment_View extends Mage_Adminhtml_Block_Sales_Order_Shipment_View
{
    public function __construct()
    {

        $shipment_id = $this->getShipment()->getId();
        if ($shipment_id) {
            $order = Mage::getModel('sales/order')->load($this->getShipment()->getOrderId());
            $isCarrier = $this->isCarrier($order);
            $order_idd = $this->getShipment()->getOrderId();
            if ($order_idd) {
                $collections = Mage::getModel('shipcloud/shipcloud');
                $colls = $collections->getCollection()->addFieldToFilter('shipment_id',$shipment_id)->addFieldToFilter('type','shipment');
                $coll=0;
                foreach($colls AS $k => $v){
                    $coll=$k;
                    break;
                }
                $collection = Mage::getModel('shipcloud/shipcloud')->load($coll);
                if ($collection->getShipmentId() == $shipment_id && $isCarrier) {
        					$this->_addButton('order_label', array(
        						'label' => Mage::helper('sales')->__('Shipcloud label'),
        						'onclick' => 'setLocation(\'' . $this->getUrl('shipcloud/adminhtml_shipcloud/showlabel/order_id/' . $order_idd . '/shipment_id/' . $shipment_id.'/type/shipment') . '\')',
        						'class' => 'go'
        					));
                } else {
                    $currentCarrier = '';
                    $currentCarrierOriginal = $order->getShippingMethod();
                    $currentCarrier = '';
                    if($currentCarrierOriginal == "flat_rate"){
                        $currentCarrier = '';
                    }else{
                        $currentShippingMethod = str_replace('sp_shipcloud_carrier_', '', $currentCarrierOriginal);
                        $arrCurrentShippingMethod = explode('_', $currentShippingMethod);
                        if(isset($arrCurrentShippingMethod[0])){
                            $currentCarrier = $arrCurrentShippingMethod[0];
                        }
                    }

                    if($currentCarrier != '' && $isCarrier){
                        $this->_addButton('order_label', array(
                            'label' => Mage::helper('sales')->__('Shipcloud label'),
                            'onclick' => 'setLocation(\'' . $this->getUrl('shipcloud/adminhtml_shipcloud/intermediate/carrier/'.strtoupper($currentCarrier).'/order_id/' . $order_idd . '/shipment_id/' . $shipment_id.'/type/shipment') . '\')',
                            'class' => 'go'
                        ));
                    }elseif($isCarrier){
                        $this->_addButton('order_label', array(
                            'label' => Mage::helper('sales')->__('Shipcloud label'),
                            'onclick' => 'setLocation(\'' . $this->getUrl('shipcloud/adminhtml_shipcloud/intermediate/order_id/' . $order_idd . '/shipment_id/' . $shipment_id.'/type/shipment') . '\')',
                            'class' => 'go'
                        ));
                    }

                }
            }
            parent::__construct();
        }
    }

    protected function isCarrier($order)
    {
        $carrier = $order->getShipcloudCreate();
        if($carrier != 'nocarrier' && $carrier !== 0)
        {
            return true;
        }

        return false;
    }
}
