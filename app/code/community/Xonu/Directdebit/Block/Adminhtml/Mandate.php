<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Block_Adminhtml_Mandate extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct()
    {
        $this->_blockGroup = 'xonu_directdebit';
        $this->_controller = 'adminhtml_mandate';
        $this->_headerText = Mage::helper('xonu_directdebit')->__('SEPA Direct Debit Mandates');
        parent::__construct();

        $this->_removeButton('add');

        if (Mage::getSingleton('admin/session')->isAllowed('system/convert/xonu_directdebit')) {
            if(Mage::helper('xonu_directdebit/sepaone')->isAvailable())
            $this->_addButton('sepaone', Mage::helper('xonu_sepaone/mandate')->getExportButton());
            $this->_addButton('export', array(
                'label' => Mage::helper('xonu_directdebit')->__('SEPA-XML Export'),
                'class'     => 'scalable go',
                'onclick'   => 'setLocation(\'' . $this->getExportUrl() .'\')',
            ));
        }
    }

    protected function getExportUrl() {
        return Mage::helper("adminhtml")->getUrl('adminhtml/directdebit_export/index');
    }

}