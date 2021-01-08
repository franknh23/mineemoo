
<?php
 
class Advanced_Delivery_Block_Adminhtml_Createorder_Deliverydate
    extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_create_delivery_date_form');
    }

    public function getDateFormat()
    {
        return Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
    }
}
