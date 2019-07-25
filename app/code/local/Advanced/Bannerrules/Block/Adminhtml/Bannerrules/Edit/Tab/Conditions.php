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
 * @category    Stabeaddon
 * @package     Stabeaddon_Bannerrules
 * @copyright   Copyright (c) 2012 Stabeaddon (http://www.stabeaddon.com/)
 * @license     http://www.stabeaddon.com/license-agreement.html
 */

/**
 * Bannerrules Edit Form Content Tab Block
 * 
 * @category    Stabeaddon
 * @package     Stabeaddon_Bannerrules
 * @author      Stabeaddon Developer
 */
class Advanced_Bannerrules_Block_Adminhtml_Bannerrules_Edit_Tab_Conditions extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * prepare tab form's information
     *
     * @return Stableaddon_Giftcode_Block_Adminhtml_Giftcode_Edit_Tab_Form
     */
    protected function _prepareForm() {
       
        if (Mage::registry('bannerrules_data')) {
            $model = Mage::registry('bannerrules_data');
        } else {
            $model = Mage::getModel('bannerrules/bannerrules');
        }
        $data = $model->getData();
        $model->setData('conditions', $model->getData('conditions_serialized'));
        $form = new Varien_Data_Form();


        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
                ->setTemplate('promo/fieldset.phtml')
                ->setNewChildUrl($this->getUrl('adminhtml/promo_quote/newConditionHtml/form/bannerrules_conditions_fieldset'));
        $form->setHtmlIdPrefix('bannerrules_');
       
        $fieldset = $form->addFieldset('conditions_fieldset', array('legend' => Mage::helper('bannerrules')->__('Apply the rule only if the following conditions are met (leave blank for all order)')))->setRenderer($renderer);

        $fieldset->addField('conditions', 'text', array(
            'name' => 'conditions',
            'label' => Mage::helper('bannerrules')->__('Conditions'),
            'title' => Mage::helper('bannerrules')->__('Conditions'),
            'required' => true,
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));

        $form->setValues($data);
        $this->setForm($form);
        return parent::_prepareForm();
    }

}