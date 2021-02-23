<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Block_Adminhtml_Renderer_Vieworder
                         extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected $_helper;

    public function render(Varien_Object $row)
    {
        $url = $this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getOrderId()));
        $label = $row->getData('increment_id');
        $html = '<a href="'.$url.'" target="sales_order_view">'.$label.'</a>';
        return $html;
    }

    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_directdebit');
        return $this->_helper;
    }
}