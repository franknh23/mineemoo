<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Sepaone_Block_Adminhtml_Export_Edit_Tab_Export extends Mage_Adminhtml_Block_Widget_Grid {

    protected $_helper;

    public function __construct()
    {
        parent::__construct();
        $this->setId('exportGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _getCollectionClass()
    {
        return 'sales/order_grid_collection';
    }

    protected function _prepareCollection()
    {
        // $code = Mage::getModel('xonu_directdebit/payment_method_config')->getCode();
        $validOrderStatus = explode(',', Mage::getStoreConfig('xonu_directdebit/sepaone/valid_status'));

        /* @var $collection Mage_Sales_Model_Resource_Order_Grid_Collection */
        $collection = Mage::getResourceModel($this->_getCollectionClass())
            ->join(array('payment' => 'sales/order_payment'), 'parent_id = main_table.entity_id',
                array(
                    'sepa_holder' => 'sepa_holder',
                    'sepa_bic' => 'sepa_bic',
                    'sepa_mandate_id' => 'sepa_mandate_id'
                ))
            ->join('xonu_sepaone/export', 'order_id = main_table.entity_id',
                array(
                    'last_transaction_status' => 'last_transaction_status',
                    'exported' => 'exported',
                    'errors' => 'errors',
                    'exported_at' => 'exported_at',
                    'order_id' => 'main_table.entity_id'
                ))
         // ->addFieldToFilter('status', array('in' => $validOrderStatus))
         // ->addFieldToFilter('method', array('eq' => $code)) // redundant because of the join with the export table
        ;

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('increment_id', array(
                'header' => Mage::helper('sales')->__('Order #'),
                'width' => '80px',
                'index' => 'increment_id',
                'renderer' => 'xonu_directdebit/adminhtml_renderer_vieworder',
            ));
        } else {
            $this->addColumn('increment_id', array(
                'header'=> Mage::helper('sales')->__('Order #'),
                'width' => '80px',
                'type'  => 'text',
                'index' => 'increment_id',
            ));
        }

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
                // 'display_deleted' => true,
                'width' => '100px',
            ));
        }

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '150px',
        ));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
        ));

        $this->addColumn('account_holder', array(
            'header' => $this->_helper()->__('Account Holder'),
            'index' => 'sepa_holder',
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('sales/xonu_directdebit/view')) {
            $this->addColumn('sepa_mandate_id', array(
                'header' => $this->_helper()->__('Mandate Identifier'),
                'index' => 'sepa_mandate_id',
                'renderer' => 'xonu_directdebit/adminhtml_renderer_viewmandate',
            ));
        } else {
            $this->addColumn('sepa_mandate_id', array(
                'header' => $this->_helper()->__('Mandate Identifier'),
                'index' => 'sepa_mandate_id'
            ));
        }

        $this->addColumn('sepa_bic', array(
            'header' => $this->_helper()->__('SWIFT-BIC'),
            'index' => 'sepa_bic',
        ));

        /*
        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
        ));
        */

        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));

        /*
        $this->addColumn('state', array(
            'header' => Mage::helper('sales')->__('State'),
            'index' => 'state',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStates(),
        ));
        */

        $this->addColumn('status', array(
            'header' => $this->_helper()->__('Order Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        $this->addColumn('last_transaction_status', array(
            'header' => $this->_helper()->__('Transaction Status'),
            'index' => 'last_transaction_status',
            'type'  => 'options',
            'width' => '100px',
            'options' => $this->_helper()->getTransactionStatusCodes(),
        ));

        $this->addColumn('exported', array(
            'header' => $this->_helper()->__('Exported'),
            'index' => 'exported',
            'type'  => 'options',
            'width' => '30px',
            'options' => array(1 => $this->_helper()->__('Yes'), 0 => $this->_helper()->__('No')),
        ));

        $this->addColumn('errors', array(
            'header' => $this->_helper()->__('Errors'),
            'index' => 'errors',
            'type'  => 'options',
            'width' => '30px',
            'options' => array(1 => $this->_helper()->__('Yes'), 0 => $this->_helper()->__('No')),
        ));

        $this->addColumn('exported_at', array(
            'header' => $this->_helper()->__('Last Export'),
            'index' => 'exported_at',
            'type' => 'datetime',
            'width' => '150px',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        /*
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getId()));
        }
        */
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridexport', array('_current'=>true));
    }

    protected function _prepareMassaction()
    {
        if (Mage::getSingleton('admin/session')->isAllowed('admin/system/api/xonu_sepaone/export')
            && Mage::getStoreConfigFlag('xonu_directdebit/sepaone/testmode_active'))
        {
            $this->setMassactionIdField('entity_id');
            $this->getMassactionBlock()->setFormFieldName('orders');

            $this->getMassactionBlock()->addItem('exported', array(
                'label'=> $this->_helper()->__('Set Exported'),
                'url'  => $this->getUrl('*/*/massFlag'),
                // 'confirm' => Mage::helper('catalog')->__('Are you sure?')
                'additional' => array(
                    'visibility' => array(
                        'name' => 'exported',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => $this->_helper()->__('Exported'),
                        'values' => array(1 => $this->_helper()->__('Yes'), 0 => $this->_helper()->__('No'))
                    )
                )
            ));

            Mage::dispatchEvent('adminhtml_xonu_sepaone_prepare_massaction', array('block' => $this));
        }

        return $this;
    }

    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_sepaone');
        return $this->_helper;
    }
}