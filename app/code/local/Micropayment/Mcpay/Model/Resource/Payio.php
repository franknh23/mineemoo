<?php
class Micropayment_Mcpay_Model_Resource_Payio extends Mage_Core_Model_Resource_Db_Abstract
{
  public function _construct()
  {
    $this->_init('mcpayio/payio', 'paydata_id');
  }
}
?>