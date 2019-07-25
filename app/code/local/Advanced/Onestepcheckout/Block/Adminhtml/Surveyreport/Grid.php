<?php

/**
 * Advanced
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the AdvancedCheckout.com license that is
 * available through the world-wide-web at this URL:
 * http://www.AdvancedCheckout.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Advanced
 * @package     Advanced_Delivery
 * @copyright   Copyright (c) 2012 Advanced (http://www.AdvancedCheckout.com/)
 * @license     http://www.AdvancedCheckout.com/license-agreement.html
 */

/**
 * Delivery Grid Block
 * 
 * @category    Advanced
 * @package     Advanced_Delivery
 * @author      Advanced Developer
 */
class Advanced_Onestepcheckout_Block_Adminhtml_Surveyreport_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('surveyreportGrid');
        $this->setDefaultSort('survey_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(false);
    }

    /**
     * prepare collection for block to display
     *
     * @return Advanced_Delivery_Block_Adminhtml_Delivery_Grid
     */
    protected function _prepareCollection(){
        $collection = Mage::getModel('onestepcheckout/survey')->getCollection();
        $collection->join(
                    array('sales_order' => 'sales/order'), 'sales_order.entity_id = main_table.order_id', array('sales_order.increment_id'));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare columns for this grid
     *
     * @return Advanced_Delivery_Block_Adminhtml_Delivery_Grid
     */
    protected function _prepareColumns() {

        $this->addColumn('survey_id', array(
            'header' => Mage::helper('onestepcheckout')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'name' => 'survey_id',
            'index' => 'survey_id',
        ));



        $this->addColumn('question', array(
            'header' => Mage::helper('onestepcheckout')->__('Question'),
            'align' => 'left',
            'index' => 'question',
            'type' => 'text',
        ));

    

        $this->addColumn('answer', array(
            'header' => Mage::helper('onestepcheckout')->__('Answer'),
            'align' => 'left',
            'index' => 'answer',
            'type' => 'text',
        ));

        $this->addColumn('increment_id', array(
            'header' => Mage::helper('onestepcheckout')->__('Order#'),
            'align' => 'left',
            'width' => '100px',
            'store' => 'title',
            'index' => 'increment_id',
            'type' => 'text',
            'filter_condition_callback' => array($this, 'filterOrder')
        ));
        
          

        $this->addExportType('*/*/exportCsv', Mage::helper('onestepcheckout')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('onestepcheckout')->__('XML'));

        return parent::_prepareColumns();
    }

    /**
     * prepare mass action for this grid
     *
     * @return Advanced_Delivery_Block_Adminhtml_Delivery_Grid
     */

       protected function _prepareMassaction() {
        $this->setMassactionIdField('survey_id');
        $this->getMassactionBlock()->setFormFieldName('survey');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('onestepcheckout')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('onestepcheckout')->__('Are you sure?')
        ));

        return $this;
    }

    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row) {
        return false;
    }

    public function filterOrder($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $collection->addFieldToFilter('sales_order.increment_id',array('like' =>'%'.$value.'%'));
        return $this;
    }
    
}
