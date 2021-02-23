<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2014 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */

require_once 'abstract.php';

class Xonu_Directdebit_Export extends Mage_Shell_Abstract
{
    public function run()
    {
        $export = Mage::getModel('xonu_directdebit/export');
        $result = $export->export('xml');

        if($result) return $result['filename'];
        else return false;
    }

}

$shell = new Xonu_Directdebit_Export();
$shell->run(); // returns the filename

/*
// direct call demo without Mage_Shell_Abstract

require_once '../app/Mage.php';
Mage::app();
$export = Mage::getModel('xonu_directdebit/export');
$result = $export->export('xml');
if($result) print $result['filename'];
*/