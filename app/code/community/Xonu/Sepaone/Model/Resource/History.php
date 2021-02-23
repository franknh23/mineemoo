<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Sepaone_Model_Resource_History extends Mage_Core_Model_Mysql4_Abstract {

    protected $_helper;

    protected function _construct() {
        $this->_init('xonu_sepaone/history', 'entity_id');
    }

    public function getCollection() {
        return Mage::getResourceModel('xonu_sepaone/history_collection');
    }

    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_sepaone');
        return $this->_helper;
    }
}