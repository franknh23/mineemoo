<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */

class Xonu_Directdebit_Block_Adminhtml_Mandate_Edit_Tab_Orders extends Mage_Adminhtml_Block_Widget_Grid {

    protected $_helper;

    public function __construct() {
        parent::__construct();
        $this->setId('orders_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        // $this->setDefaultFilter(array('exported' => 0));
    }

    protected function _addColumnFilterToCollection($column) {

        if ($column->getId() == 'in_custom') {
            $bannerIds = $this->_getSelectedBanners();


            if (empty($bannerIds)) {
                $bannerIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                // show associated banners only
                $this->getCollection()->addFieldToFilter('banner_id', array(array('in' => $bannerIds)));
                // $this->getCollection()->getSelect()->orWhere('bannergroup_id = "" OR bannergroup_id IS NULL');
            } else {
                if ($bannerIds) {
                    // show banners not associated with any other group
                    $this->getCollection()->addFieldToFilter('banner_id', array('nin' => $bannerIds));
                    $this->getCollection()->getSelect()
                        ->Where('bannergroup_id = "" OR bannergroup_id IS NULL OR bannergroup_id = ?',
                          $this->getRequest()->getParam('id'));
                } else {

                }
            }
        } else {
             parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _getCollectionClass()
    {
        return 'sales/order_grid_collection';
    }

    protected function _prepareCollection() {

        $mandate = Mage::registry('xonu_directdebit_mandate');
        $mandateId = $mandate->getMandateIdentifier();

        /* @var $collection Mage_Sales_Model_Resource_Order_Grid_Collection */
        $collection = Mage::getResourceModel($this->_getCollectionClass())
            ->join('sales/order_payment', 'parent_id=main_table.entity_id',
                array(
                    'sepa_mandate_id' => 'sepa_mandate_id'
                ))
            ->join('xonu_directdebit/export', 'order_id=main_table.entity_id',
                array(
                    'exported' => 'exported',
                    'exported_at' => 'exported_at',
                    'order_id' => 'main_table.entity_id'
                ))
            ->addFieldToFilter('sepa_mandate_id', $mandateId)
        ;

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
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

        /*
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
        */

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
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        $this->addColumn('exported', array(
            'header' => $this->_helper()->__('Exported'),
            'index' => 'exported',
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

    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_directdebit');
        return $this->_helper;
    }

    public function getGridUrl() {
        return $this->getData('grid_url') ? $this->getData('grid_url') :
            $this->getUrl('*/*/ordergrid', array('_current' => true, 'id' => $this->getRequest()->getParam('id')));
    }

    public function getRowUrl($row) {
        return '';
    }

    public function getBannerIdsFromOtherGroups() {
        $tm_id = $this->getRequest()->getParam('id');
        if (!isset($tm_id)) {
            return array();
        }

        // find all banners associated with a group which is not this one
        $collection = Mage::getModel('bannermanager/banner')->getCollection();
        $collection->addFieldToFilter('bannergroup_id', array('neq' => $tm_id))
                   ->addFieldToFilter('bannergroup_id', array('notnull' => ''))
                   ->addFieldToFilter('bannergroup_id', array('neq' => ''))
            ;
        $bannerIds = array();
        foreach ($collection as $obj) {
            $bannerIds[] = $obj->getId();
        }
        return $bannerIds;
    }


    public function getSelectedSliderBanners() {

        $tm_id = $this->getRequest()->getParam('id');
        if (!isset($tm_id)) {
            return array();
        }
        $collection = Mage::getModel('bannermanager/banner')->getCollection();
        $collection->addFieldToFilter('bannergroup_id', $tm_id);

        $bannerIds = array();
        foreach ($collection as $obj) {
            $bannerIds[$obj->getId()] = array('order_banner_slider' => $obj->getOrderBanner());
        }
        return $bannerIds;
    }

    protected function _getSelectedBanners() {
        $banners = $this->getRequest()->getParam('banner');
        if (!is_array($banners)) {
            $banners = array_keys($this->getSelectedSliderBanners());
        }
        return $banners;
    }

}