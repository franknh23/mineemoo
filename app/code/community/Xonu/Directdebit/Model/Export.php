<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Model_Export extends Mage_Core_Model_Abstract {

    protected $_helper;

    protected function _construct() {
        $this->_init('xonu_directdebit/export');
    }

    public function export($format = 'xml') {
        switch($format) {
            case 'xml':
            case 'csv':
            case 'dta':
                break;
            default:
                return array('error' => 'unknown format');
        }

        $history = Mage::getModel('xonu_directdebit/history');
        $history->setStartedAt($this->_helper()->getCurrentDateTime());

        $validOrderStatus = explode(',', Mage::getStoreConfig('xonu_directdebit/export/valid_status'));
        $orderLimitPerRequest = Mage::getStoreConfig('xonu_directdebit/export/order_limit');

        /* @var $collection Mage_Sales_Model_Resource_Order_Grid_Collection */
        $collection = Mage::getResourceModel('sales/order_collection')
            ->join(array('p' => 'sales/order_payment'), 'parent_id=main_table.entity_id',
                array('sepa_mandate_id' => 'sepa_mandate_id',
                      'sepa_holder' => 'sepa_holder',
                      'sepa_iban' => 'sepa_iban',
                      'sepa_bic' => 'sepa_bic',
                      'base_amount_ordered' => 'base_amount_ordered'))
            ->join(array('e' => 'xonu_directdebit/export'), 'order_id=main_table.entity_id')
            ->join(array('m' => 'xonu_directdebit/mandate'), 'mandate_identifier=sepa_mandate_id',
                array('sepa_mandate_date' => 'created_at',
                      'recurrent' => 'recurrent'))
           // ->addFieldToFilter('p.method', array('eq' => $code)) // redundant because of the export table
            ->addFieldToFilter('e.exported', array('eq' => 0))
            ->addFieldToFilter('main_table.status', array('in' => $validOrderStatus))
        ;

        // limit per request for busy shops
        if($orderLimitPerRequest > 0) {
            $collection->setPageSize($orderLimitPerRequest)->setCurPage(1);
            $exportedIds = array();
            foreach($collection as $row) $exportedIds[] = $row->getData('order_id');
        } else {
            $exportedIds = $collection->getAllIds();
        }

        // admin user
        if(Mage::app()->getFrontController()->getRequest()->getRouteName() == 'adminhtml') {
            $userId = Mage::getSingleton('admin/session')->getUser()->getUserId();
            $history->setUserId($userId);
        } else {
            $history->setExternal(true);
        }

        // empty result
        if(!$collection->count()) {
            $history->setEndedAt($this->_helper()->getCurrentDateTime());
            $history->setEmpty(1);
            $history->save();
            return false;
        } else {
            $history->setCount($collection->count());
            $history->setEmpty(0);
        }

        $data = Mage::getModel('xonu_directdebit/export_'.$format)->getFile($collection);
        if(!$data) return array('error' => true); // nothing to export

        if($format == 'csv') {
            $filename = $data['filename'];
        } else {
            $data['format'] = $format;
            $filename = $this->saveToFile($data);
            if(!$filename) return array('error' => true);
        }

        // set exported flags
        $exportedIds = array_diff($exportedIds, $data['error_ids']);
        $exportedFlags = Mage::getResourceModel('xonu_directdebit/export_collection')
            ->addFieldToFilter('order_id', array('in' => $exportedIds))
            ->load()
        ;
        $timestamp = $this->_helper()->getCurrentDateTime();
        foreach($exportedFlags as $exportFlag) {
            $exportFlag->setExported(true);
            $exportFlag->setExportedAt($timestamp);
        }
        $exportedFlags->save();

        $history->setEndedAt($this->_helper()->getCurrentDateTime());
        $history->setFilename($filename);
        $history->save();

        return array('filename' => $filename, 'data' => $data['file'], 'error' => false);
    }

    protected function saveToFile($data) {
        $helper = Mage::helper('xonu_directdebit');
        $filename = $helper->getExportFilename($data['format']);
        $path = $helper->getExportDir();

        if(!file_exists($path)) {
            Mage::getSingleton('adminhtml/session')->addError(
                sprintf(Mage::helper('xonu_directdebit')->__('Export directory "%s" does not exist.'), $path)
            );
            return false;
        }

        try {
            $file = new Varien_Io_File();
            $file->open(array('path' => $path));
            $file->streamOpen($filename);
            $file->streamWrite($data['file']);
            $file->streamClose();
        } catch(Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            return false;
        }

        return $filename;
    }

    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_directdebit');
        return $this->_helper;
    }
}
