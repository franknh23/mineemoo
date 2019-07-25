<?php
/**
 * Stabeaddon
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Stabeaddon.com license that is
 * available through the world-wide-web at this URL:
 * http://www.stabeaddon.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Stabeaddon
 * @package     Stabeaddon_Bannerrules
 * @copyright   Copyright (c) 2012 Stabeaddon (http://www.stabeaddon.com/)
 * @license     http://www.stabeaddon.com/license-agreement.html
 */

/**
 * Bannerrules Grid Block
 * 
 * @category    Stabeaddon
 * @package     Stabeaddon_Bannerrules
 * @author      Stabeaddon Developer
 */
class Advanced_Bannerrules_Block_Adminhtml_Bannerrules_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('bannerrulesGrid');
        $this->setDefaultSort('bannerrules_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }
    
    /**
     * prepare collection for block to display
     *
     * @return Advanced_Bannerrules_Block_Adminhtml_Bannerrules_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('bannerrules/bannerrules')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    /**
     * prepare columns for this grid
     *
     * @return Advanced_Bannerrules_Block_Adminhtml_Bannerrules_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('bannerrules_id', array(
            'header' => Mage::helper('bannerrules')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'bannerrules_id',
            'width' => '100',
        ));

        $this->addColumn('title', array(
            'header' => Mage::helper('bannerrules')->__('Name'),
            'align' => 'left',
            'index' => 'title',
            'width' => '400',
        ));
        $this->addColumn('from_date', array(
            'header' => Mage::helper('bannerrules')->__('Date Start'),
            'align' => 'left',
            'index' => 'from_date',
            'type' => 'datetime',
            'filter_index' => 'main_table.from_date',
            'width' => '150',
        ));
        $this->addColumn('to_date', array(
            'header' => Mage::helper('bannerrules')->__('Date Exprie'),
            'align' => 'left',
            'index' => 'to_date',
            'type' => 'datetime',
            'filter_index' => 'main_table.to_date',
            'width' => '150',
        ));
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website', array(
                'header' => Mage::helper('bannerrules')->__('Website'),
                'align' => 'left',
                'index' => 'website',
                'type' => 'options',
                'sortable' => false,
                'options' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(),
            ));
        }
        $this->addColumn('priority', array(
            'header' => Mage::helper('bannerrules')->__('Priority'),
            'index' => 'priority',
        ));


        $this->addColumn('status', array(
            'header' => Mage::helper('bannerrules')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                1 => 'Enabled',
                2 => 'Disabled',
            ),
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('bannerrules')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('bannerrules')->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                )),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('bannerrules')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('bannerrules')->__('XML'));

        return parent::_prepareColumns();
    }
    
    /**
     * prepare mass action for this grid
     *
     * @return Advanced_Bannerrules_Block_Adminhtml_Bannerrules_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('bannerrules_id');
        $this->getMassactionBlock()->setFormFieldName('bannerrules');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'        => Mage::helper('bannerrules')->__('Delete'),
            'url'        => $this->getUrl('*/*/massDelete'),
            'confirm'    => Mage::helper('bannerrules')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('bannerrules/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
            'label'=> Mage::helper('bannerrules')->__('Change status'),
            'url'    => $this->getUrl('*/*/massStatus', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                    'name'    => 'status',
                    'type'    => 'select',
                    'class'    => 'required-entry',
                    'label'    => Mage::helper('bannerrules')->__('Status'),
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
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}