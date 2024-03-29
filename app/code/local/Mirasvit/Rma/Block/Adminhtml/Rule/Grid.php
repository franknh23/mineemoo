<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   RMA
 * @version   2.1.0-beta
 * @build     1359
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Rma_Block_Adminhtml_Rule_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('grid');
        $this->setDefaultSort('rule_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('rma/rule')
            ->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('rule_id', array(
            'header' => Mage::helper('rma')->__('ID'),
            'index' => 'rule_id',
            'filter_index' => 'main_table.rule_id',
            )
        );
        $this->addColumn('name', array(
            'header' => Mage::helper('rma')->__('Rule Name'),
            'index' => 'name',
            'filter_index' => 'main_table.name',
            )
        );
        $this->addColumn('is_active', array(
            'header' => Mage::helper('rma')->__('Is Active'),
            'index' => 'is_active',
            'filter_index' => 'main_table.is_active',
            'type' => 'options',
            'options' => array(
                0 => $this->__('No'),
                1 => $this->__('Yes'),
            ),
            )
        );
        $this->addColumn('sort_order', array(
            'header' => Mage::helper('rma')->__('Priority'),
            'index' => 'sort_order',
            'filter_index' => 'main_table.sort_order',
            )
        );
        $this->addColumn('is_stop_processing', array(
            'header' => Mage::helper('rma')->__('Is Stop Processing'),
            'index' => 'is_stop_processing',
            'filter_index' => 'main_table.is_stop_processing',
            'type' => 'options',
            'options' => array(
                0 => $this->__('No'),
                1 => $this->__('Yes'),
            ),
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('rule_id');
        $this->getMassactionBlock()->setFormFieldName('rule_id');
        $statuses = array(
                array('label' => '', 'value' => ''),
                array('label' => $this->__('Disabled'), 'value' => 0),
                array('label' => $this->__('Enabled'), 'value' => 1),
        );
        $this->getMassactionBlock()->addItem('is_active', array(
             'label' => Mage::helper('rma')->__('Change status'),
             'url' => $this->getUrl('*/*/massChange', array('_current' => true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'is_active',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('rma')->__('Status'),
                         'values' => $statuses,
                     ),
             ),
        ));
        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('rma')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('rma')->__('Are you sure?'),
        ));

        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /************************/
}
