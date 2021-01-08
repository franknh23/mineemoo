<?php

class Advanced_Certificates_Block_Config_License extends Mage_Adminhtml_Block_System_Config_Form_Field {

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element) {

        $params = Mage::app()->getRequest()->getParams();



        $modules = Mage::getConfig()->getNode('modules')->children();
        $html = '';
        foreach ($modules as $moduleName => $moduleData) {
            $array = explode('_', $moduleName);
            if ($array[0] != 'Advanced') {
                continue;
            }
            
            if(isset($moduleData->nonechecklicense) && $moduleData->nonechecklicense){
                continue;                
            }
            $section = strtolower($array[1]);
            $file = Mage::getBaseDir() . DS . 'app' . DS . 'code' . DS . 'local' . DS . 'Advanced' . DS . 'Certificates' . DS . $section . '.xml';

            try {
                $data = Mage::getModel('certificates/file')->readFile($file);
                
            } catch (Exception $e) {
                $data = false;
            }
            
            $id = $section;
            $html .='<tr>';
            if (!$data) {
                $html .= '<td class="label"><label for="' . $id . '">License Key</label></td>';

                //$isDefault = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
                $isMultiple = $element->getExtType() === 'multiple';

                // replace [value] with [inherit]
                $namePrefix = preg_replace('#\[value\](\[\])?$#', '', $section);
                $element->setName('groups[license][fields]['.$section.'][value]');
                //$element->setName($section);
                
                $element->setValue(Mage::getStoreConfig('certificates/license/'.$section));
                $options = $element->getValues();

                $addInheritCheckbox = false;
                if ($element->getCanUseWebsiteValue()) {
                    $addInheritCheckbox = true;
                    $checkboxLabel = Mage::helper('adminhtml')->__('Use Website');
                } elseif ($element->getCanUseDefaultValue()) {
                    $addInheritCheckbox = true;
                    $checkboxLabel = Mage::helper('adminhtml')->__('Use Default');
                }

                if ($addInheritCheckbox) {
                    $inherit = $element->getInherit() == 1 ? 'checked="checked"' : '';
                    if ($inherit) {
                        $element->setDisabled(true);
                    }
                }

                if ($element->getTooltip()) {
                    $html .= '<td class="value with-tooltip">';
                    $html .= $this->_getElementHtml($element);
                    $html .= '<div class="field-tooltip"><div>' . $element->getTooltip() . '</div></div>';
                } else {
                    $html .= '<td class="value">';
                    $html .= $this->_getElementHtml($element);
                };
                if ($element->getComment()) {
                    $html.= '<p class="note"><span>' . $element->getComment() . '</span></p>';
                }
                $html.= '</td>';

                if ($addInheritCheckbox) {

                    $defText = $element->getDefaultValue();
                    if ($options) {
                        $defTextArr = array();
                        foreach ($options as $k => $v) {
                            if ($isMultiple) {
                                if (is_array($v['value']) && in_array($k, $v['value'])) {
                                    $defTextArr[] = $v['label'];
                                }
                            } elseif (isset($v['value']) && $v['value'] == $defText) {
                                $defTextArr[] = $v['label'];
                                break;
                            }
                        }
                        $defText = join(', ', $defTextArr);
                    }

                    // default value
                    $html.= '<td class="use-default">';
                    $html.= '<input id="' . $id . '_inherit" name="' . $namePrefix . '[inherit]" type="checkbox" value="1" class="checkbox config-inherit" ' . $inherit . ' onclick="toggleValueElements(this, Element.previous(this.parentNode))" /> ';
                    $html.= '<label for="' . $id . '_inherit" class="inherit" title="' . htmlspecialchars($defText) . '">' . $checkboxLabel . '</label>';
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
                $html.= '</td>';
            } else {
                
//             $data['register_date'] = date('Y-M-d',$data['register_date']);
              
                $html.= '<td colspan="2" style="padding: 10px 0;">';
                $date = new Zend_Date();
                $date->setLocale(Mage::app()->getLocale()->getLocaleCode());
                $date = $date->setDate($data['register_date'], 'yyyy/MM/dd');

                $registeredDate = Mage::helper('core')->formatDate($date, 'medium', false);


                $html .= '<strong>' . Mage::helper('certificates')->__('Product') . ':</strong> ' . $data['product_name'];
                $html .= '<br/><strong>' . Mage::helper('certificates')->__('Version') . ':</strong> ' .Mage::getConfig()->getNode()->modules->Advanced_Onestepcheckout->version;
                $html .= '<br/><strong>' . Mage::helper('certificates')->__('License') . ':</strong> ' . $data['license'];
                $html .= '<br/><strong>' . Mage::helper('certificates')->__('Activation Date') . ':</strong> ' . $registeredDate;
//            if($data['type']=='live'){
                $html .= '<br/><strong>' . Mage::helper('certificates')->__('Domains Registered') . ':</strong> ' . (($data['domain']) ? $data['domain'] : Mage::helper('certificates')->__('You have not registered domain for the license certificate.'));
//            }else{
                $html .= '<br/><strong>' . Mage::helper('certificates')->__('Development Domains Registered') . ':</strong> ' . (($data['dev_domain']) ? $data['dev_domain'] : Mage::helper('certificates')->__('You have not registered development domain for the license certificate.'));
//            }

                $html .= '<br/><strong>' . Mage::helper('certificates')->__('Purchase Email') . ':</strong> ' . $data['email'];
                $html .= '<br/><a href="http://www.advancedcheckout.com/support.html">Click here</a> if you need help.';
                $html.= '</td>';
                
                }
            $html .='</tr>';
        }


        return $this->_decorateRowHtml($element, $html);
    }

}
