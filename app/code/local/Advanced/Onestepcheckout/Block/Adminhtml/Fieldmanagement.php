<?php

class Advanced_Onestepcheckout_Block_Adminhtml_Fieldmanagement extends Mage_Adminhtml_Block_Template {

    const SCOPE_DEFAULT = 'default';
    const SCOPE_WEBSITES = 'websites';
    const SCOPE_STORES = 'stores';
    var $_scopeId = 0;
    var $_scope = 'default';

    public function __construct() {
        $this->_controller = 'onestepcheckoutadmin_fieldmanagement';
        $this->_blockGroup = 'onestepcheckout';
        $this->_headerText = Mage::helper('onestepcheckout')->__('Manager');
        $this->_addButtonLabel = Mage::helper('onestepcheckout')->__('Add New Staff');
        parent::__construct();
        $this->setTemplate('onestepcheckout/fieldmanagement.phtml');
    }

    public function getFields() {
        $storecode = Mage::app()->getRequest()->getParam('store');
        $website = Mage::app()->getRequest()->getParam('website');
        $helper = Mage::helper('onestepcheckout');

        $scopeId = 0;
        $scope = 'default';

        $fields = array();

        if ($storecode) {
            $scope = 'stores';
            $scopeId = (int) Mage::getConfig()->getNode('stores/' . $storecode . '/system/store/id');

            $checkFieldExit = Mage::getModel('onestepcheckout/fieldsposition')->getCollection()
                            ->addFieldToFilter('scope', $scope)
                            ->addFieldToFilter('scope_id', $scopeId)->getFirstItem();

            if ($checkFieldExit->getId()) {
                $fields = $helper->getFieldData($scope, $scopeId);
            } else {
                $checkFieldExit = Mage::getModel('onestepcheckout/fieldsposition')->getCollection()
                                ->addFieldToFilter('scope', 'websites')
                                ->addFieldToFilter('scope_id', (int) Mage::getConfig()->getNode('websites/' . $website . '/system/website/id'))->getFirstItem();
                if ($checkFieldExit->getId()) {
                    $fields = $helper->getFieldData('websites', (int) Mage::getConfig()->getNode('websites/' . $website . '/system/website/id'));
                } else {
                    $fields = $helper->getFieldData('default', 0);
                }
            }
        } elseif ($website) {
            $scope = 'websites';
            $scopeId = (int) Mage::getConfig()->getNode('websites/' . $website . '/system/website/id');
            $checkFieldExit = Mage::getModel('onestepcheckout/fieldsposition')->getCollection()
                            ->addFieldToFilter('scope', $scope)
                            ->addFieldToFilter('scope_id', $scopeId)->getFirstItem();
            if ($checkFieldExit->getId()) {
                $fields = $helper->getFieldData($scope, $scopeId);
            } else {
                $fields = $helper->getFieldData('default', 0);
            }
        } else {
            $scope = 'default';
            $scopeId = 0;
            $fields = $helper->getFieldData($scope, $scopeId);
        }
        
        
        if ($storecode) {

            if (!isset($fields['stores']) && !isset($fields['websites'])) {
                $_fields = $fields['default'];
                $_fields['use_default'] = 1;
            }
            if (!isset($fields['stores']) && isset($fields['websites'])) {
                $_fields = $fields['websites'];
                $_fields['use_default'] = 1;
            }
            if (isset($fields['stores'])) {
                $_fields = $fields['stores'];
                $_fields['use_default'] = 0;
            }
        } elseif ($website) {
            if (!isset($fields['websites'])) {
                $_fields = $fields['default'];
                $_fields['use_default'] = 1;
            }
            if (isset($fields['websites'])) {
                $_fields = $fields['websites'];
                $_fields['use_default'] = 0;
            }
        } else {
            $_fields = $fields['default'];
            $_fields['use_default'] = $fields['use_default']['default'];
        }
        
        
        
        $this->_scope = $scope;
        $this->_useDefault = $_fields['use_default'];
        
        return $_fields;
    }
    
    public function _getFieldHtml() {
        
        $scope = $this->_scope;
        $useDefault = $this->_useDefault;
        $html = '<div style="width:96%; float:left">';
        $showInput = false;

        if ($scope == self::SCOPE_STORES) {
            $label = Mage::helper('onestepcheckout')->__('Use Website');
            $showInput = true;
        } else if ($scope == self::SCOPE_WEBSITES) {
            $label = Mage::helper('onestepcheckout')->__('Use Default');
            $showInput = true;
        } else {
            $label = '';
        }
        $checked = 'checked';
        if ($useDefault == 0)
            $checked = '';
        if ($showInput)
            $html .= '<input onclick="showStoreEdit()" style="margin-right:5px" type="checkbox" ' . $checked . ' class="checkbox config-inherit" value="1" name="groups[position][fields][manage][inherit]" id="onestepcheckout_position_manage_inherit" />';


        $html .= $label . '<span style="color: #6f8992; font-size: 0.9em; margin-left:5px">[' . Mage::helper('onestepcheckout')->__('STORE VIEW') . ']</span>';
        $html .= '</div>';

        return $html;
    }

}
