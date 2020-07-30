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
class Advanced_Delivery_Block_Adminhtml_Delivery_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('deliveryGrid');
        $this->setDefaultSort('delivery_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * prepare collection for block to display
     *
     * @return Advanced_Delivery_Block_Adminhtml_Delivery_Grid
     */
    protected function _prepareCollection(){
    $collection = Mage::getModel('delivery/holiday')->getCollection();
    foreach($collection as $link){
        if($link->getStoreId() && $link->getStoreId() != 0 ){
            $link->setStoreId(explode(',',$link->getStoreId()));
        }
        else{
            $link->setStoreId(array('0'));
        }
    }
    $this->setCollection($collection);
    return parent::_prepareCollection();
}


protected function _filterStoreCondition($collection, $column){
    if (!$value = $column->getFilter()->getValue()) {
        return;
    }
    $this->getCollection()->addStoreFilter($value);
}
    /**
     * prepare columns for this grid
     *
     * @return Advanced_Delivery_Block_Adminhtml_Delivery_Grid
     */
    protected function _prepareColumns() {

        $this->addColumn('delivery_id', array(
            'header' => Mage::helper('delivery')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'name' => 'delivery_id',
            'index' => 'delivery_id',
        ));

       if (!Mage::app()->isSingleStoreMode()) {
    $this->addColumn('store_id', array(
        'header'        => Mage::helper('delivery')->__('Store View'),
        'index'         => 'store_id',
        'type'          => 'store',
        'store_all'     => true,
        'store_view'    => true,
        'sortable'      => true,
        'filter_condition_callback' => array($this,
            '_filterStoreCondition'),
    ));
}


        $this->addColumn('datefrom', array(
            'header' => Mage::helper('delivery')->__('Date From'),
            'align' => 'left',
            'index' => 'datefrom',
            'type' => 'date',
        ));

    

        $this->addColumn('dateto', array(
            'header' => Mage::helper('delivery')->__('Date To'),
            'align' => 'left',
            'index' => 'dateto',
            'type' => 'date',
        ));

        $this->addColumn('description', array(
            'header' => Mage::helper('delivery')->__('Description'),
            'align' => 'left',
            'store' => 'title',
            'index' => 'description',
            'type' => 'text'
        ));
        
               $this->addColumn('status', array(
            'header'    => Mage::helper('delivery')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'status',
            'type'        => 'options',
            'options'    => array(
                            1=>'Enlaled',
                            2=>'Disaled'
                )
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('delivery')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('delivery')->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                )),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('delivery')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('delivery')->__('XML'));

        return parent::_prepareColumns();
    }

    /**
     * prepare mass action for this grid
     *
     * @return Advanced_Delivery_Block_Adminhtml_Delivery_Grid
     */

       protected function _prepareMassaction()
    {
     $this->setMassactionIdField('delivery_id');
        $this->getMassactionBlock()->setFormFieldName('delivery');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('delivery')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('delivery')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('delivery/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
            'label'=> Mage::helper('delivery')->__('Change status'),
            'url'    => $this->getUrl('*/*/massStatus', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                    'name'    => 'status',
                    'type'    => 'select',
                    'class'    => 'required-entry',
                    'label'    => Mage::helper('delivery')->__('Status'),
                    'values'=> $statuses
                ))
        ));
        return $this;
    }

    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
