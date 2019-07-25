<?php
class Advanced_Delivery_Block_Adminhtml_Intervals_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('intervals_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('delivery')->__('Intervals'));
    }
    
    /**
     * prepare before render block to html
     *
     * @return Advanced_Delivery_Block_Adminhtml_Delivery_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('delivery')->__('Holidays'),
            'title'     => Mage::helper('delivery')->__('Holidays'),
            'content'   => $this->getLayout()
                                ->createBlock('delivery/adminhtml_intervals_edit_tab_form')
                                ->toHtml(),
        ));
        return parent::_beforeToHtml();
    }
}