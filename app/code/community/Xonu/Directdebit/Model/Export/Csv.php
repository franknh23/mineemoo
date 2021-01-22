<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Model_Export_Csv {

    public function getFile($collection) {

        $helper = Mage::helper('xonu_directdebit');
        $filename = $helper->getExportFilename('csv');
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
        } catch(Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            return false;
        }

        $columns = array(
            'sepa_holder' => 'sepa_holder',
            'sepa_iban'   => 'sepa_iban',
            'sepa_bic'    => 'sepa_bic',
            'amount'      => 'base_amount_ordered',
            'currency'    => 'base_currency_code',
            'purpose'     => 'increment_id'
        );
        $file->streamWriteCsv(array_keys($columns));

        $errorIds = array();
        foreach($collection as $row) {
            $data = array();
            $error = false;
            foreach($columns as $target => $source) {
                $value = trim($row->getData($source));
                if($value == '') {
                    $error = true;
                    break;
                }
                $data[$target] = $row->getData($source);
            }
            if(!$error) {
                if($file->streamWriteCsv($data) === false) $errorIds[] = $row->getData('order_id');
            } else {
                $errorIds[] = $row->getData('order_id');
            }
        }
        $file->close();

        return array('file' => file_get_contents($path . DS . $filename), 'filename' => $filename, 'error_ids' => $errorIds);
    }


}