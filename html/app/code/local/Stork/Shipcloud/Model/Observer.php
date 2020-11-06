<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 28.12.11
 * Time: 9:38
 * To change this template use File | Settings | File Templates.
 */
class Stork_Shipcloud_Model_Observer
{
    public function initShipcloud($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ((get_class($block) == 'Mage_Adminhtml_Block_Widget_Grid_Massaction' || get_class($block) == 'Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid_Massaction') && $block->getRequest()->getControllerName() == 'sales_order') {
            $block->addItem('shipcloud_pdflabels', array(
                'label' => Mage::helper('sales')->__('Print Shipcloud Labels'),
                'url' => Mage::app()->getStore()->getUrl('shipcloud/adminhtml_pdflabels'),
            ));
        }
        return $this;
    }

    public function replaceEmailTemplate(Varien_Event_Observer $observer)
    {
        $blockentity = $observer->getBlock();
        if ($blockentity->getTemplate() =='email/order/shipment/track.phtml')
        {
            $blockentity->setTemplate('Stork/Shipcloud/email/order/shipment/track.phtml');
        }
    }
}
