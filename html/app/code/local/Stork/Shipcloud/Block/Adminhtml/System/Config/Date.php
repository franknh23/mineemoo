<?php
class Stork_Shipcloud_Block_Adminhtml_System_Config_Date extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $date = new Varien_Data_Form_Element_Date;
    		$format = 'yyyy/MM/dd';

        $data = array(
            'name'      => $element->getName(),
            'html_id'   => $element->getId(),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
        );

    		if(date('w') == 6){
    			$pickup_date = date('Y/m/d', strtotime(date("Y/m/d")) + 2 * 60 * 60 * 24);
    		}else if(date('w') == 5){
    			$pickup_date = date('Y/m/d', strtotime(date("Y/m/d")) + 3 * 60 * 60 * 24);
    		}else{
    			$pickup_date = date('Y/m/d', strtotime(date("Y/m/d")) + 60 * 60 * 24);
    		}

        $date->setData($data);
    		$date->setValue($pickup_date, $format);
    		$date->setFormat($format);
        $date->setForm($element->getForm());

        return $date->getElementHtml();
    }
}
