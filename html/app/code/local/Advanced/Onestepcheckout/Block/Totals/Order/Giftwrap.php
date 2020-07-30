<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Advanced_Onestepcheckout_Block_Totals_Order_Giftwrap extends Mage_Core_Block_Template
{
    public function initTotals()
    {        
        $totalsBlock = $this->getParentBlock();
        $order = $totalsBlock->getOrder();
        
        if ($order->getOnestepcheckoutGiftwrap()) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'  => 'onestepcheckout_giftwrap',
                'label' => $this->__('Gift Wrap'),
                'value' => $order->getOnestepcheckoutGiftwrap(),
            )), 'subtotal');
        }
    }
}
