<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Sepaone_Model_Log extends Mage_Core_Model_Abstract {

    protected function _construct() {
        $this->_init('xonu_sepaone/log');
    }

    public function setResponseBody($data) {
        return parent::setResponseBody(base64_encode(gzcompress($data, 9)));
    }

    public function getResponseBody() {
        return gzuncompress(base64_decode(parent::getResponseBody()));
    }


    public function setRequestBody($data) {
        return parent::setRequestBody(base64_encode(gzcompress($data, 9)));
    }

    public function getRequestBody() {
        return gzuncompress(base64_decode(parent::getRequestBody()));
    }
}