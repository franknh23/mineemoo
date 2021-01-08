<?php
class Advanced_Delivery_Block_Adminhtml_Intervals_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {
    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        if (Mage::getSingleton('adminhtml/session')->getDeliveryData()) {
            $data = Mage::getSingleton('adminhtml/session')->getDeliveryData();
            Mage::getSingleton('adminhtml/session')->setDeliveryData(null);
        } elseif (Mage::registry('delivery_data')) {
            $data = Mage::registry('delivery_data')->getData();
        }
        $fieldset = $form->addFieldset('delivery_form', array(
            'legend' => Mage::helper('delivery')->__('Holiday information')
        ));
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'multiselect', array(
                'name' => 'stores[]',
                'label' => Mage::helper('delivery')->__('Store View'),
                'title' => Mage::helper('delivery')->__('Store View'),
                'required' => true,
                'values' => Mage::getSingleton('adminhtml/system_store')
                        ->getStoreValuesForForm(false, true),
            ));
        } else {
            $fieldset->addField('store_id', 'hidden', array(
                'name' => 'stores[]',
                'value' => Mage::app()->getStore(true)->getId()
            ));
        }    
        
        
           $field_open = array('name' => 'hourstart',
            'data' => isset($data['hourstart']) ? $data['hourstart'] : ''
        );
        $fieldset->addField('hourstart', 'note', array(
            'label' => Mage::helper('delivery')->__('Starting time'),
            'name' => 'hourstart',
            'required' => true,
            'after_element_html' => '<small> Hour:Minute</small>',
            'text' => $this->getLayout()->createBlock('delivery/adminhtml_time')->setData('field', $field_open)->setTemplate('delivery/time.phtml')->toHtml(),
        ));
        
       
          $field_close = array('name' => 'hourto',
            'data' => isset($data['hourto']) ? $data['hourto'] : ''
        );
        $fieldset->addField('hourto', 'note', array(
            'label' => Mage::helper('delivery')->__('Ending time'),
            'name' => 'hourto',
            'required' => true,
            'after_element_html' => '<small> Hour:Minute</small>',
            'text' => $this->getLayout()->createBlock('delivery/adminhtml_time')->setData('field', $field_close)->setTemplate('delivery/time.phtml')->toHtml(),
        ));
            $fieldset->addField('status', 'select', array(
            'label'        => Mage::helper('delivery')->__('Status'),
            'name'        => 'status',
            'values'    => Mage::getSingleton('delivery/status')->getOptionHash(),
        ));

        $form->setValues($data);
        return parent::_prepareForm();
    }

}
