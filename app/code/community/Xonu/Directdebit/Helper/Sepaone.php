<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */

class Xonu_Directdebit_Helper_Sepaone {

    public function isAvailable() {
        return Mage::getConfig()->getNode('modules/Xonu_Sepaone') !== false;
    }

}