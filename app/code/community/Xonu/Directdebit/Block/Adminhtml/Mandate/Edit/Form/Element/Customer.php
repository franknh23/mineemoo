<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Block_Adminhtml_Mandate_Edit_Form_Element_Customer extends Varien_Data_Form_Element_Abstract
{
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setType('label');
    }

    public function getElementHtml()
    {
        $label = $this->getEscapedValue();
        if($this->getCustomerId() != '') {
            $url = Mage::helper("adminhtml")->getUrl('adminhtml/customer/edit', array('id' => $this->getCustomerId()));
            $html = '<a href="'.$url.'" target="_blank">'.$label.'</a>';
        } else {
            $html = $label;
        }
        $html.= $this->getAfterElementHtml();
        return $html;
    }
}