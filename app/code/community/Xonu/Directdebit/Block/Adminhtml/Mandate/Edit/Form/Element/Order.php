<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */

class Xonu_Directdebit_Block_Adminhtml_Mandate_Edit_Form_Element_Order extends Varien_Data_Form_Element_Abstract
{
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setType('label');
    }

    public function getElementHtml()
    {
        $label = $this->getEscapedValue();
        $url = Mage::helper("adminhtml")->getUrl('adminhtml/sales_order/view', array('order_id' => $this->getOrderId()));

        $html = '<a href="'.$url.'" target="_blank">'.$label.'</a>';
        $html.= $this->getAfterElementHtml();
        return $html;
    }
}