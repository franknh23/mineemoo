<?php

class Advanced_Delivery_Block_Adminhtml_Intervals_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('intervalsGrid');
        $this->setDefaultSort('intervals_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('delivery/intervals')->getCollection();
        foreach ($collection as $link) {
            if ($link->getStoreId() && $link->getStoreId() != 0) {
                $link->setStoreId(explode(',', $link->getStoreId()));
            } else {
                $link->setStoreId(array('0'));
            }
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _filterStoreCondition($collection, $column) {
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

        $this->addColumn('intervals_id', array(
            'header' => Mage::helper('delivery')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'name' => 'intervals_id',
            'index' => 'intervals_id',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header' => Mage::helper('delivery')->__('Store View'),
                'index' => 'store_id',
                'type' => 'store',
                'store_all' => true,
                'store_view' => true,
                'sortable' => true,
                'filter_condition_callback' => array($this,
                    '_filterStoreCondition'),
            ));
        }
        $this->addColumn('hourstart', array(
            'header' => Mage::helper('delivery')->__('Starting time'),
            'align' => 'left',
            'index' => 'hourstart',
            'type' => 'time',
            'onclick' => "",
            'onchange' => "",
            'disabled' => false,
            'readonly' => false
        ));
        $this->addColumn('hourto', array(
            'header' => Mage::helper('delivery')->__('Ending time'),
            'align' => 'left',
            'index' => 'hourto',
            'type' => 'time',
            'onclick' => "",
            'onchange' => "",
            'disabled' => false,
            'readonly' => false
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('delivery')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                1 => 'Enlaled',
                2 => 'Disaled'
            )
        ));

        return parent::_prepareColumns();
    }

    /**
     * prepare mass action for this grid
     *
     * @return Advanced_Delivery_Block_Adminhtml_Delivery_Grid
     */
    protected function _prepareMassaction() {
        $this->setMassactionIdField('intervals_id');
        $this->getMassactionBlock()->setFormFieldName('intervals');

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
