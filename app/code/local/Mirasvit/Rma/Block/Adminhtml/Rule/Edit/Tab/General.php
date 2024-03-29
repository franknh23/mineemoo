<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   RMA
 * @version   2.1.0-beta
 * @build     1359
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Rma_Block_Adminhtml_Rule_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        /** @var Mirasvit_Rma_Model_Rule $rule */
        $rule = Mage::registry('current_rule');

        $fieldset = $form->addFieldset('edit_fieldset', array('legend' => Mage::helper('rma')->__('General Information')));
        if ($rule->getId()) {
            $fieldset->addField('rule_id', 'hidden', array(
                'name' => 'rule_id',
                'value' => $rule->getId(),
            ));
        }
        $fieldset->addField('name', 'text', array(
            'label' => Mage::helper('rma')->__('Rule Name'),
            'required' => true,
            'name' => 'name',
            'value' => $rule->getName(),
        ));
        $fieldset->addField('is_active', 'select', array(
            'label' => Mage::helper('rma')->__('Is Active'),
            'required' => true,
            'name' => 'is_active',
            'value' => $rule->getIsActive(),
            'values' => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));
        $fieldset->addField('sort_order', 'text', array(
            'label' => Mage::helper('rma')->__('Priority'),
            'name' => 'sort_order',
            'value' => $rule->getSortOrder(),
            'note' => Mage::helper('rma')->__('Arranged in the ascending order. 0 is the highest.'),
        ));
        $fieldset->addField('is_stop_processing', 'select', array(
            'label' => Mage::helper('rma')->__('Stop Further Rules Processing'),
            'name' => 'is_stop_processing',
            'value' => $rule->getIsStopProcessing(),
            'values' => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        return parent::_prepareForm();
    }

    /************************/
}
