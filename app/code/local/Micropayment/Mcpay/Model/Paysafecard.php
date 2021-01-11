<?php
class Micropayment_Mcpay_Model_Paysafecard extends Micropayment_Mcpay_Model_Standard
{
  protected $_code = 'paysafecard';
  protected $_formBlockType = 'mcpay/form_paysafecard';

  //protected $_isGateway = true;
  //protected $_canUseInternal = true;
  protected $_canRefund = true;
  protected $_canRefundInvoicePartial = true;

  // to show refund online just adding a transactionId to the invoice

}