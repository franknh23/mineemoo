<?php
/**
 * Stabeaddon
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Stabeaddon.com license that is
 * available through the world-wide-web at this URL:
 * http://www.stabeaddon.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category     Stabeaddon
 * @package     Stabeaddon_Bannerrules
 * @copyright     Copyright (c) 2012 Stabeaddon (http://www.stabeaddon.com/)
 * @license     http://www.stabeaddon.com/license-agreement.html
 */

/**
 * Bannerrules Edit Form Block
 * 
 * @category    Stabeaddon
 * @package     Stabeaddon_Bannerrules
 * @author      Stabeaddon Developer
 */
class Advanced_Bannerrules_Block_Adminhtml_Bannerrules_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare form's information for block
     *
     * @return Advanced_Bannerrules_Block_Adminhtml_Bannerrules_Edit_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array(
                'id'    => $this->getRequest()->getParam('id'),
            )),
            'method'    => 'post',
            'enctype'   => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}