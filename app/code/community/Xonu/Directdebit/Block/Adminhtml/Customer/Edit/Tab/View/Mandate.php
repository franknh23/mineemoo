<?php

class Xonu_Directdebit_Block_Adminhtml_Customer_Edit_Tab_View_Mandate extends Mage_Adminhtml_Block_Template
{
    protected $_customer;
    protected $_customerLog;
    protected $_helper;

    public function getMandate()
    {
        return Mage::getModel('xonu_directdebit/mandate')->loadByCustomerId($this->getCustomer()->getId());
    }


    public function getCustomer()
    {
        if (!$this->_customer) {
            $this->_customer = Mage::registry('current_customer');
        }
        return $this->_customer;
    }

    /**
     * Load Customer Log model
     *
     * @return Mage_Log_Model_Customer
     */
    public function getCustomerLog()
    {
        if (!$this->_customerLog) {
            $this->_customerLog = Mage::getModel('log/customer')
                ->loadByCustomer($this->getCustomer()->getId());
        }
        return $this->_customerLog;
    }

    protected function _helper() {
        if(!isset($this->_helper)) $this->_helper = Mage::helper('xonu_directdebit');
        return $this->_helper;
    }
}