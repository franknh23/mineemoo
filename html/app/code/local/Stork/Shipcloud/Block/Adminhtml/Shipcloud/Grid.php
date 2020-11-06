<?php

class Stork_Shipcloud_Block_Adminhtml_Shipcloud_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('shipcloudGrid');
      $this->setDefaultSort('shipcloud_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('shipcloud/shipcloud')->getCollection();
      if($display = $this->getRequest()->getParam('display')){
        if ($display == 'empty'){
            $collection->addFieldToFilter('trackingnumber', array('eq' => ''));
        }
      }
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('shipcloud_id', array(
          'header'    => Mage::helper('shipcloud')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'shipcloud_id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('shipcloud')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));
      $this->addColumn('trackingnumber', array(
          'header'    => Mage::helper('shipcloud')->__('Trackingnumber'),
          'align'     =>'left',
          'index'     => 'trackingnumber',
      ));
      $this->addColumn('type', array(
          'header'    => Mage::helper('shipcloud')->__('type'),
          'align'     =>'left',
          'index'     => 'type',
      ));

      $this->addColumn('shipping_carrier', array(
          'header'    => Mage::helper('shipcloud')->__('Carrier'),
          'align'     =>'left',
          'index'     => 'shipping_carrier',
      ));
      $this->addColumn('status', array(
          'header'    => Mage::helper('shipcloud')->__('status'),
          'align'     =>'left',
          'index'     => 'status',
      ));


      $this->addColumn('status', array(
          'header'    => Mage::helper('shipcloud')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('shipcloud')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('shipcloud')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

		$this->addExportType('*/*/exportCsv', Mage::helper('shipcloud')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('shipcloud')->__('XML'));

      return parent::_prepareColumns();
  }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}
