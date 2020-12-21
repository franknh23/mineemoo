<?php

class SendCloud_Plugin_Block_Adminhtml_System_Config_Form_ConnectButton
    extends SendCloud_Plugin_Block_Adminhtml_System_Config_Form_AbstractButton
{
    protected $_buttonLabel = 'Connect with SendCloud';
    protected $_scriptFunctionName = 'connect';
    protected $_callbackName = 'connect';
}