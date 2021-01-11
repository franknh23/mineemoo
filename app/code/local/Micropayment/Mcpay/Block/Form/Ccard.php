<?php

class Micropayment_Mcpay_Block_Form_Ccard extends Micropayment_Mcpay_Block_Form_Default
{
  protected function _construct()
  {
    $pm = Mage::getModel('mcpay/ccard');
    $html = $pm->getFrontendLogo();
    $this->setTemplate('micropayment/form/ccard.phtml')->setMethodLabelAfterHtml($html);
  }

  function getStyle()
  {
    $pm = Mage::getModel('mcpay/ccard');
    $output = '<link href="' . $pm->getStyle() . '" rel="stylesheet" />';
    return $output;
  }

  function getMyForm()
  {
    $pm = Mage::getModel('mcpay/ccard');
    return $pm->getMyForm();
  }

}
