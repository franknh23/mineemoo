<?php

class Stork_Shipcloud_Block_Adminhtml_Custom_Carrier extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	public $currentElement;
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract  $element)
    {
				$arrCarriers = array();
				$arrCarriers['standard'] = 'standard';
				$arrCarriers['one_day'] = 'Express';
				$arrCarriers['one_day_early'] = 'Express Saver';
				//groups[profile][fields][defaultcarriertype][value]
				$elementHtml = '<select id="shipcloud_profile_defaultcarriertype" name="groups[profile][fields][defaultcarriertype][value]">';
				foreach($arrCarriers as $key=>$value){
					if($element->getValue() == $key){
						$elementHtml .= '<option value="'.$key.'" selected="selected">'.$value.'</option>';
					}else{
						$elementHtml .= '<option value="'.$key.'">'.$value.'</option>';
					}
				}
				$elementHtml .= '</select>';
				//var_dump($element);die();
				return $elementHtml;
    }

    protected function getSelectHtml($type = 'other')
    {
        $arrOptions = array();
        switch ($type) {
            case 'UPS':
                $arrOptions = array(
                    'standard' => 'Standard',
                    'one_day' => 'Express',
                    'one_day_early' => 'Express Saver',

                    );
                break;

            case 'DHL':
                $arrOptions = array(
                    'standard' => 'Standard',
                    'one_day' => 'Express',

                    );

                break;

            case 'DPD':
                $arrOptions = array(
                    'standard' => 'Standard',
                    'one_day' => 'Express',

                    );

                break;

            default:
                $arrOptions = array(
                    'standard' => 'Standard',
                    );
                break;
        }

        $html = '';
				//        $html .= '<select id="default_carrier_type">';
				$currentCarrierType = '';
				if(!is_null($this->currentElement)){
				$currentCarrierType = $this->currentElement->getValue();
				}
        foreach ($arrOptions as $value => $label){
					if($currentCarrierType == $value){
						$html .= "<option value='".$value."' selected='selected'>".$label."</option>";
					}else{
						$html .= "<option value='".$value."'>".$label."</option>";
					}
				}
        return $html;
    }

}
