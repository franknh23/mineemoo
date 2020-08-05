<?php

abstract class SendCloud_Plugin_Block_Adminhtml_System_Config_Form_AbstractButton
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected $_buttonLabel = '';
    protected $_scriptFunctionName = '';
    protected $_callbackName = '';

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('SendCloud/system/config/button.phtml');
    }

    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for button
     *
     * @return string
     */
    public function getAjaxCheckUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/adminhtml_autoconnect/'. $this->_callbackName);
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'     => $this->helper('adminhtml')->__($this->_buttonLabel),
                'onclick'   => 'javascript:' . $this->getScriptFunctionName() . '(); return false;'
            ));

        return $button->toHtml();
    }

    public function getScriptFunctionName()
    {
        return $this->_scriptFunctionName;
    }
}
