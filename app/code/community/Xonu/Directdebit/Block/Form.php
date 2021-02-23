<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Block_Form extends Mage_Payment_Block_Form {


    protected $_template = 'xonu/directdebit/form.phtml';

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate($this->_template);
    }

    protected function getHolder() {
        $address = $this->getAddress();
        return $address->getData('firstname').' '.$address->getData('lastname');
    }

    protected function getAddress() {
        return Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress();
    }
}