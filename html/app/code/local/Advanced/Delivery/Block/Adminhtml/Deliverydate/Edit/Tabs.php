<?php
class Advanced_Delivery_Block_Adminhtml_Deliverydate_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('deliverydate_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('delivery')->__('Deliverydate'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('delivery')->__('Deliverydate'),
            'title'     => Mage::helper('delivery')->__('Deliverydate'),
            'content'   => $this->getLayout()
                                ->createBlock('delivery/adminhtml_deliverydate_edit_tab_form')
                                ->toHtml(),
        ));
        
   
        return parent::_beforeToHtml();
    }
}