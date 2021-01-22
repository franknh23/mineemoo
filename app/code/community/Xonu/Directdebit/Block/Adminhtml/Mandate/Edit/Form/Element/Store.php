<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */

class Xonu_Directdebit_Block_Adminhtml_Mandate_Edit_Form_Element_Store extends Varien_Data_Form_Element_Abstract
{
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setType('label');
    }

    public function getElementHtml()
    {
        $html = Mage::helper('xonu_directdebit')->getStoreInfo((int)$this->getEscapedValue());
        $html.= $this->getAfterElementHtml();
        return $html;
    }
}