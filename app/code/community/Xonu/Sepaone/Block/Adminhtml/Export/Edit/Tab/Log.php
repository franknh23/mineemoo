<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Sepaone_Block_Adminhtml_Export_Edit_Tab_Log extends Mage_Adminhtml_Block_Widget_Grid {

    protected $_helper;

    public function __construct()
    {
        parent::__construct();
        $this->setId('logGrid');
        $this->setUseAjax(true);
        // $this->setDefaultSort('request_at');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        // $this->setDefaultFilter(array('empty' => 0));
        $this->setDefaultFilter(array('request_type' => 'transactions'));
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('xonu_sepaone/log_collection');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header' => $this->_helper()->__('Log ID'),
            'index' => 'entity_id',
            'type' => 'range',
            'width' => '50px',
        ));

        $this->addColumn('request_type', array(
            'header' => $this->_helper()->__('Request Type'),
            'index' => 'request_type',
            'width' => '50px',
            'type'  => 'options',
            'options' => array(
                'events' => 'events',
                'mandates' => 'mandates',
                'transactions' => 'transactions',
                'chargebacks' => 'chargebacks'
            ),
        ));

        $this->addColumn('order_increment_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'width' => '40px',
            'type'  => 'text',
            'index' => 'order_increment_id',
        ));

        $this->addColumn('mandate_id', array(
            'header'=> Mage::helper('sales')->__('Mandate Identifier'),
            'width' => '70px',
            'type'  => 'text',
            'index' => 'mandate_id',
        ));

        $this->addColumn('request_at', array(
            'header' => $this->_helper()->__('Request Timestamp'),
            'index' => 'request_at',
            'type' => 'datetime',
            'width' => '150px',
        ));

        $this->addColumn('request_body_length', array(
            'header' => $this->_helper()->__('Request Length'),
            'index' => 'request_body_length',
            'type' => 'range',
            'width' => '100px',
        ));


//        $this->addColumn('response_type', array(
//            'header' => $this->_helper()->__('Response Type'),
//            'index' => 'response_type',
//            'width' => '200px',
//        ));

        $this->addColumn('response_time', array(
            'header' => $this->_helper()->__('Response Time [Seconds]'),
            'index' => 'response_time',
            'type' => 'range',
            'width' => '20px',
        ));

//        $this->addColumn('response_at', array(
//            'header' => $this->_helper()->__('Response Timestamp'),
//            'index' => 'response_at',
//            'type' => 'datetime',
//            'width' => '150px',
//        ));

        $this->addColumn('response_code', array(
            'header' => $this->_helper()->__('Response Code'),
            'index' => 'response_code',
            // 'type' => 'range',
            'width' => '100px',
        ));

        $this->addColumn('response_body_length', array(
            'header' => $this->_helper()->__('Response Length'),
            'index' => 'response_body_length',
            'type' => 'range',
            'width' => '100px',
        ));

        $this->addColumn('remote_livemode', array(
            'header' => $this->_helper()->__('Test Mode'),
            'index' => 'remote_livemode',
            'width' => '50px',
            'type'  => 'options',
            'options' => array(0 => $this->_helper()->__('Yes'), 1 => $this->_helper()->__('No'))
        ));

        $this->addColumn('action',
            array(
                'header' => $this->_helper()->__('Details'),
                'width' => '100',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => $this->_helper()->__('View'),
                        'url' => array('base'=> '*/*/viewlog'),
                        'field' => 'id',
                        'onclick'  => "this.win = window.open(this.href, '',
                        'width=1400,height=1000,resizable=1,scrollbars=1'); this.win.focus(); return false;"
                    )),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        // return $this->getUrl('*/*/viewlog', array('id' => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridlog', array('_current'=>true));
    }

    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_sepaone');
        return $this->_helper;
    }
}