<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Sepaone_Block_Adminhtml_Export_Edit_Tab_History extends Mage_Adminhtml_Block_Widget_Grid {

    protected $_helper;

    public function __construct()
    {
        parent::__construct();
        $this->setId('historyGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('started_at');
        $this->setDefaultDir('DESC');
        $this->setDefaultFilter(array(
            'empty' => 0,
            'test' => Mage::getStoreConfigFlag('xonu_directdebit/sepaone/testmode_active')
        ));
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('xonu_sepaone/history_collection');
        $collection->getSelect()
            // ->columns(array('empty' => new Zend_Db_Expr('IF(count > 0, 0, 1)')))
//            ->joinLeft(
//                array('user' => $collection->getTable('admin/user')), 'user.user_id = main_table.user_id',
//                array('username' => 'username'))
        ;

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        /*
        $this->addColumn('user_id', array(
            'header' => $this->_helper()->__('User ID'),
            'index' => 'user_id',
            'type' => 'range',
            'width' => '50px',
        ));

        $this->addColumn('username', array(
            'header' => $this->_helper()->__('User Name'),
            'index' => 'username',
            'width' => '200px',
        ));
        */

        $this->addColumn('started_at', array(
            'header' => $this->_helper()->__('Started At'),
            'index' => 'started_at',
            'type' => 'datetime',
            'width' => '150px',
        ));

        $this->addColumn('ended_at', array(
            'header' => $this->_helper()->__('Ended At'),
            'index' => 'ended_at',
            'type' => 'datetime',
            'width' => '150px',
        ));

        $this->addColumn('processing_time', array(
            'header' => $this->_helper()->__('Processing Time'),
            'index' => 'processing_time',
            'type' => 'range',
            'width' => '50px',
        ));

        $this->addColumn('count_transactions', array(
            'header' => $this->_helper()->__('Transactions'),
            'index' => 'count_transactions',
            'type' => 'range',
            'width' => '50px',
        ));

        $this->addColumn('count_errors', array(
            'header' => $this->_helper()->__('Errors'),
            'index' => 'count_errors',
            'type' => 'range',
            'width' => '50px',
        ));

        $this->addColumn('empty', array(
            'header' => $this->_helper()->__('Empty'),
            'index' => 'empty',
            'width' => '50px',
            'type'  => 'options',
            'options' => array(1 => $this->_helper()->__('Yes'), 0 => $this->_helper()->__('No'))
        ));

        $this->addColumn('test', array(
            'header' => $this->_helper()->__('Test Mode'),
            'index' => 'test',
            'width' => '50px',
            'type'  => 'options',
            'options' => array(1 => $this->_helper()->__('Yes'), 0 => $this->_helper()->__('No'))
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridhistory', array('_current'=>true));
    }

    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_sepaone');
        return $this->_helper;
    }
}