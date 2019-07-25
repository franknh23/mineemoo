<?php

class Advanced_Onestepcheckout_Block_Adminhtml_Source_Shippingdefault  extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {

    /**
     * Render element html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $addInheritCheckbox = false;
        if ($element->getCanUseWebsiteValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = Mage::helper('adminhtml')->__('Use Website');
        }
        elseif ($element->getCanUseDefaultValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = Mage::helper('adminhtml')->__('Use Default');
        }

        if ($addInheritCheckbox) {
            $inherit = $element->getInherit()==1 ? 'checked="checked"' : '';
            if ($inherit) {
                $element->setDisabled(true);
            }
        }
        
        
        $moduleName = $this->getModuleName();
        $vales = $element->getValue();
        
        $disable = '';
        if($element->getDisabled()){
            $disable = 'disabled';
        }
        
        
                $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
                $shipping = array();
                
        
        
        $useContainerId = $element->getData('use_container_id');
        $html = sprintf('<tr id="row_%s"><td class="label"><label for="%s">%s</label></td>', $element->getHtmlId(), $element->getHtmlId(), $element->getLabel());
        $html .= '<td class="value">';
        
        $html .=  sprintf('<select %s id="%s" name="%s" class=" select">',$disable, $element->getHtmlId(),$element->getName());
        
        foreach($methods as $_ccode => $_carrier) {
                    if($_methods = $_carrier->getAllowedMethods())  {
                        if(!$_title = Mage::getStoreConfig("carriers/$_ccode/title"))
                            $_title = $_ccode;
                        $shipping[] = array('value' => $_ccode,'label' => $_title, 'is_group' => true);
                        $html .=  '<optgroup label="'.$_title.'">';                   
                            foreach($_methods as $_mcode => $_method)   {                                
                                $_code = $_ccode . '_' . $_mcode;
                                $selected = ($vales==$_code)?'selected':'';
                                $shipping[]=array('value' => $_code,'label' =>'--'. $_title);
                                $html .= '<option '.$selected.' value="'.$_code.'">  '.$_title.' '.$_method.'</option>';
                            }
                        $html .= '</optgroup>';
                    }
                }
        
                
        $html .=  '</select>';
        $html .= '</td>';
        
        
        if ($addInheritCheckbox) {
            $id = $element->getHtmlId();
            $options = $element->getValues();
            $namePrefix = preg_replace('#\[value\](\[\])?$#', '', $element->getName());
            $defText = $element->getDefaultValue();
            if ($options) {
                $defTextArr = array();
                foreach ($options as $k=>$v) {
                    if ($isMultiple) {
                        if (is_array($v['value']) && in_array($k, $v['value'])) {
                            $defTextArr[] = $v['label'];
                        }
                    } elseif ($v['value']==$defText) {
                        $defTextArr[] = $v['label'];
                        break;
                    }
                }
                $defText = join(', ', $defTextArr);
            }

            // default value
            $html.= '<td class="use-default">';
            $html.= '<input id="' . $id . '_inherit" name="'
                . $namePrefix . '[inherit]" type="checkbox" value="1" class="checkbox config-inherit" '
                . $inherit . ' onclick="toggleValueElements(this, Element.previous(this.parentNode))" /> ';
            $html.= '<label for="' . $id . '_inherit" class="inherit" title="'
                . htmlspecialchars($defText) . '">' . $checkboxLabel . '</label>';
            $html.= '</td>';
        }

        $html.= '<td class="scope-label">';
        if ($element->getScope()) {
            $html .= $element->getScopeLabel();
        }
        $html.= '</td>';

        $html.= '<td class="">';
        if ($element->getHint()) {
            $html.= '<div class="hint" >';
            $html.= '<div style="display: none;">' . $element->getHint() . '</div>';
            $html.= '</div>';
        }
        $html.= '</td></tr>';
        
        return $html;
        
    }
   
}
