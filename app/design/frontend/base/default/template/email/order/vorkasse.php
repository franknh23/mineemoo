<?php
$order = $this->getData('order');
if(is_object($order) && $order->getPayment()->getMethodInstance()->getCode() == "banktransfer") {
    echo "Bank IBAN : DE000 0000 0000 0000"
} 