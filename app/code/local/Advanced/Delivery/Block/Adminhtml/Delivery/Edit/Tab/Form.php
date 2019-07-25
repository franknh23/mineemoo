<?php
class Advanced_Delivery_Block_Adminhtml_Delivery_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {
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
         $date_format = Mage::getStoreConfig('delivery/general/delivery_format');

                                        if ($date_format == '')
                                            $date_format = 'd/M/Y';
                                        else
                                            $date_format.=" ";
        
        $fieldset->addField('datefrom', 'date', array(
            'label' => Mage::helper('delivery')->__('Holiday date from'),
            'tabindex' => 1,
             'required' => true,
            'name' => 'datefrom',
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => 'yyyy-MM-dd',
        ));
        $fieldset->addField('dateto', 'date', array(
            'label' => Mage::helper('delivery')->__('Holiday date to'),
            'tabindex' => 1,
            'name' => 'dateto',
             'required' => true,
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => 'yyyy-MM-dd',
        ));
        $fieldset->addField('description', 'textarea', array(
            'label' => Mage::helper('delivery')->__('Description'),
            'required' => false,
            'name' => 'description',
           
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
