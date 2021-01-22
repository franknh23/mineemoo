<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Model_Resource_Mandate extends Mage_Core_Model_Mysql4_Abstract {

    protected $_helper;

    protected function _construct() {
        $this->_init('xonu_directdebit/mandate', 'entity_id');
    }

    public function getCollection() {
        return Mage::getResourceModel('xonu_directdebit/mandate_collection');
    }

    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_directdebit');
        return $this->_helper;
    }
}