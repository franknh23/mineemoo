<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Model_Resource_History_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    protected function _construct()
    {
        $this->_init('xonu_directdebit/history');
    }
}