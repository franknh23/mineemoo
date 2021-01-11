<?php
class Micropayment_Mcpay_Model_Payio extends Mage_Core_Model_Abstract
{
  public function _construct()
  {
    parent::_construct();
    $this->_init('mcpayio/payio');
  }
}
?>