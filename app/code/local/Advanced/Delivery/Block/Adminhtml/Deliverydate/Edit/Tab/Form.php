<?php

class Advanced_Delivery_Block_Adminhtml_Deliverydate_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareLayout()
    {
        $return = parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        return $return;
    }
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
            'legend' => Mage::helper('delivery')->__('Deliverydate information')
        ));

        $fieldset->addField('increment_id', 'text', array(
            'label' => Mage::helper('delivery')->__('Order#'),
            'class' => 'required-entry',  
          'readonly' => true,
            'name' => 'increment_id',
        ));
        
        $fieldset->addField('delivery_date', 'date', array(
            'label' => Mage::helper('delivery')->__('Delivery date'),
            'tabindex' => 1,
            'required' => true,
            'name' => 'delivery_date',
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
        ));
        
        $fieldset->addField('description', 'textarea', array(
            'label' => Mage::helper('delivery')->__('Comment'),
            'required' => false,
            'name' => 'description',
        ));

        $fieldset->addField('hourstart', 'select', array(
            'label' => Mage::helper('delivery')->__('Delivery time'),
            'required' => false,
            'name' => 'hourstart',
            'values' => Mage::helper('delivery')->getHour(),
        ));

        $fieldset = $form->addFieldset('deliveryemail_form', array('legend' => Mage::helper('delivery')->__('Response')));
        $fieldset->addField('subject', 'text', array(
            'label' => Mage::helper('delivery')->__('Subject'),
            'name' => 'subject',
        ));

              $config = Mage::getSingleton('cms/wysiwyg_config')->getConfig()->setAddWidgets(false)->setAddImages(false)->setPlugins(array());
	$fieldset->addField('contents', 'editor',
                        array (
                        'name' => 'contents',
                        'label' => Mage::helper('delivery')->__('Content'),
                        'title' => Mage::helper('delivery')->__('Content'),
                        'style' => 'height:36em;width:500px',
                            'wysiwyg'   => true,
                        'config'    => $config,
                        'required' => true ));
        $form->setValues($data);
        return parent::_prepareForm();
    }

}
