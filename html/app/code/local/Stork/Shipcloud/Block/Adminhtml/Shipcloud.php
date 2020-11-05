<?php
class Stork_Shipcloud_Block_Adminhtml_Shipcloud extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_shipcloud';
    $this->_blockGroup = 'shipcloud';
    $this->_headerText = Mage::helper('shipcloud')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('shipcloud')->__('Add Item');
    parent::__construct();
    $this->_removeButton("add");
  }

  public function _prepareLayout()
  {
      $head = $this->getLayout()->getBlock('head');
      $head->addJs('lib/jquery/jquery-1.10.2.min.js');
      $head->addJs('lib/jquery/noconflict.js');
      $link = $this->getUrl('*/*/*/display/empty');
      $btnlabel = 'Show empty trucknumbers';
      if($display = $this->getRequest()->getParam('display')){
        if ($display == 'empty'){
            $link = $this->getUrl('*/*/*/display/index');
            $btnlabel = 'Show all';
        }
      }
      $this->_addButton('show_empty_trucknumbers', array(
          'label'   => Mage::helper('catalog')->__($btnlabel),
          'onclick' => "setLocation('{$link}')",
          'class'   => ''
      ));

      $this->setChild('grid', $this->getLayout()->createBlock('adminhtml/catalog_product_grid', 'product.grid'));

      parent::_prepareLayout();
  }
}
