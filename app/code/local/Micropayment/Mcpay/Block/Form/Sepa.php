<?php

class Micropayment_Mcpay_Block_Form_Sepa extends Micropayment_Mcpay_Block_Form_Default
{
  protected function _construct()
  {
    $pm = Mage::getModel('mcpay/sepa');
    $html = $pm->getFrontendLogo();
    $this->setTemplate('micropayment/form/sepa.phtml')->setMethodLabelAfterHtml($html);
  }

  function getStyle()
  {
    $pm = Mage::getModel('mcpay/sepa');
    $output = '<link href="' . $pm->getStyle() . '" rel="stylesheet" />';
    return $output;
  }

  function getMyForm()
  {
    $pm = Mage::getModel('mcpay/sepa');
    return $pm->getMyForm();
  }

}
