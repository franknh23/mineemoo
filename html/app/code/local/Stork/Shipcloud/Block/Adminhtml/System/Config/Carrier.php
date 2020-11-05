<?php
class Stork_Shipcloud_Block_Adminhtml_System_Config_Carrier extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
		$arrCarriers = array();
		$arrCarriers['dhl'] = 'DHL';
		$arrCarriers['dpd'] = 'DPD';
		$arrCarriers['fedex'] = 'FEDEX';
		$arrCarriers['hermes'] = 'HERMES';
		$arrCarriers['ups'] = 'UPS';

		$elementHtml = '<select id="shipcloud_sp_pickup_manualy_pickup_manualy_carrier" name="groups[sp_pickup_manualy][fields][pickup_manualy_carrier][value]">';
		foreach($arrCarriers as $key=>$value){
			if($element->getValue() == $key){
				$elementHtml .= '<option value="'.$key.'" selected="selected">'.$value.'</option>';
			}else{
				$elementHtml .= '<option value="'.$key.'">'.$value.'</option>';
			}
		}
		$elementHtml .= '</select>';
		return $elementHtml;
    }
}
