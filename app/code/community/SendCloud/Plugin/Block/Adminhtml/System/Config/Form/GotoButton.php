<?php

class SendCloud_Plugin_Block_Adminhtml_System_Config_Form_GotoButton
    extends SendCloud_Plugin_Block_Adminhtml_System_Config_Form_AbstractButton
{
    protected $_buttonLabel = 'Go to SendCloud';
    protected $_scriptFunctionName = 'goto';
    protected $_callbackName = 'goto';
}
