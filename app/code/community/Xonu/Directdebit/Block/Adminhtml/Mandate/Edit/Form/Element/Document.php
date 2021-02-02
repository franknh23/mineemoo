<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */

class Xonu_Directdebit_Block_Adminhtml_Mandate_Edit_Form_Element_Document extends Varien_Data_Form_Element_Abstract
{
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setType('label');
    }

    public function getElementHtml()
    {
        $html = '<div style="background-color:#ffffff;padding:15px;margin-left:-210px;border:1px solid #E7E7E7;">'.$this->getDocumentHtml().'</div>';
        $html.= $this->getAfterElementHtml();
        return $html;
    }
}