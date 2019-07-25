<?php

class Advanced_Delivery_Block_Adminhtml_Deliverydate_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('deliverydateGrid');
        $this->setDefaultSort('deliverydate_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('delivery/deliverydate')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('deliverydate_id', array(
            'header' => Mage::helper('delivery')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'name' => 'deliverydate_id',
            'index' => 'deliverydate_id',
        ));
        $this->addColumn('increment_id', array(
            'header' => Mage::helper('delivery')->__('Order#'),
            'align' => 'right',
            'width' => '50px',
            'name' => 'increment_id',
            'index' => 'increment_id',
        ));
        $this->addColumn('delivery_date', array(
            'header' => Mage::helper('delivery')->__('Delivery Date'),
            'align' => 'left',
            'index' => 'delivery_date',
            'type' => 'date',
        ));
        $this->addColumn('hourstart', array(
            'header' => Mage::helper('delivery')->__('Delivery Time'),
            'align' => 'left',
            'index' => 'hourstart',
        ));



        $this->addColumn('description', array(
            'header' => Mage::helper('delivery')->__('Comment'),
            'align' => 'left',
            'store' => 'title',
            'index' => 'description',
            'type' => 'text'
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('delivery')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                0 => 'Not Reply',
                1 => 'Replied'
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
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('deliverydate_id');
        $this->getMassactionBlock()->setFormFieldName('deliverydate');

      
        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('delivery')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('delivery')->__('Are you sure?')
        ));
        return $this;
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
