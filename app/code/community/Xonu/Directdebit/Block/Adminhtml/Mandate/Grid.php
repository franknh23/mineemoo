<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Block_Adminhtml_Mandate_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    protected $_helper;

    public function __construct()
    {
        parent::__construct();
        $this->setId('mandateGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _getCollectionClass()
    {
        return 'xonu_directdebit/mandate_collection';
    }

    protected function _prepareCollection()
    {
        /* @var $collection Mage_Sales_Model_Resource_Order_Grid_Collection */
        $collection = Mage::getResourceModel($this->_getCollectionClass())
            ->join(array('order' => 'sales/order'), 'order.entity_id = main_table.last_order_id',
                   array(
                       'increment_id' => 'increment_id',
                       'order_id' => 'entity_id'
                   ));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('mandate_identifier', array(
            'header' => $this->_helper()->__('Mandate Identifier'),
            'index' => 'mandate_identifier',
        ));

        $this->addColumn('recurrent', array(
            'header' => $this->_helper()->__('Recurrent'),
            'index' => 'recurrent',
            'type'  => 'options',
            'width' => '30px',
            'options' => array(1 => $this->_helper()->__('Yes'), 0 => $this->_helper()->__('No')),
        ));

        $this->addColumn('revoked', array(
            'header' => $this->_helper()->__('Revoked'),
            'index' => 'revoked',
            'type'  => 'options',
            'width' => '30px',
            'options' => array(1 => $this->_helper()->__('Yes'), 0 => $this->_helper()->__('No')),
        ));


        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => $this->_helper()->__('Created In'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
                // 'display_deleted' => true,
                'width' => '100px',
            ));
        }

        $this->addColumn('created_at', array(
            'header' => $this->_helper()->__('Created At'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '150px',
        ));

        $this->addColumn('updated_at', array(
            'header' => $this->_helper()->__('Last Update'),
            'index' => 'updated_at',
            'type' => 'datetime',
            'width' => '150px',
        ));

        $this->addColumn('customer_email', array(
            'header'=> $this->_helper()->__('E-Mail'),
            'width' => '180px',
            'type'  => 'text',
            'index' => 'customer_email',
        ));

        $this->addColumn('customer_firstname', array(
            'header'=> $this->_helper()->__('First Name'),
            'width' => '180px',
            'type'  => 'text',
            'index' => 'customer_firstname',
        ));

        $this->addColumn('customer_lastname', array(
            'header'=> $this->_helper()->__('Last Name'),
            'width' => '180px',
            'type'  => 'text',
            'index' => 'customer_lastname',
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('increment_id', array(
                'header' => $this->_helper()->__('Last Order Id'),
                'width' => '80px',
                'index' => 'increment_id',
                'renderer' => 'xonu_directdebit/adminhtml_renderer_vieworder',
            ));
        } else {
            $this->addColumn('increment_id', array(
                'header'=> $this->_helper()->__('Last Order Id'),
                'width' => '80px',
                'type'  => 'text',
                'index' => 'increment_id',
            ));
        }

        $this->addColumn('last_order_created_at', array(
            'header' => $this->_helper()->__('Last Order Date'),
            'index' => 'last_order_created_at',
            'type' => 'datetime',
            'width' => '150px',
        ));

        /*
        $this->addColumn('state', array(
            'header' => $this->_helper()->__('State'),
            'index' => 'state',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStates(),
        ));
        */

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/xonu_directdebit/view') || 1) {
            return $this->getUrl('*/*/edit', array('id' => $row->getId()));
        } else {
            return false;
        }
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_directdebit');
        return $this->_helper;
    }

    protected function _prepareMassaction()
    {
//        $this->setMassactionIdField('entity_id');
//        $this->getMassactionBlock()->setFormFieldName('orders');
//
//        $this->getMassactionBlock()->addItem('exported', array(
//            'label'=> $this->_helper()->__('Set Exported'),
//            'url'  => $this->getUrl('*/*/massFlag'),
//            // 'confirm' => Mage::helper('catalog')->__('Are you sure?')
//            'additional' => array(
//                'visibility' => array(
//                    'name' => 'exported',
//                    'type' => 'select',
//                    'class' => 'required-entry',
//                    'label' => $this->_helper()->__('Exported'),
//                    'values' => array(1 => $this->_helper()->__('Yes'), 0 => $this->_helper()->__('No'))
//                )
//            )
//        ));
//
//         Mage::dispatchEvent('adminhtml_xonu_directdebit_prepare_massaction', array('block' => $this));

        return $this;
    }

}