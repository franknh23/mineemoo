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



class Mirasvit_Rma_Block_Adminhtml_Rma_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'rma_id';
        $this->_controller = 'adminhtml_rma';
        $this->_blockGroup = 'rma';

        // $this->_updateButton('save', 'label', Mage::helper('rma')->__('Save'));
        $this->_removeButton('save');
        $this->_updateButton('delete', 'label', Mage::helper('rma')->__('Delete'));

        $this->_addButton('update_continue', array(
             'label' => Mage::helper('rma')->__('Update And Continue Edit'),
             'onclick' => 'saveAndContinueEdit()',
             'class' => 'save saveAndContinueRmaBtn',
         ), -100);

        $this->_addButton('update', array(
            'label' => Mage::helper('rma')->__('Update'),
            'onclick' => 'saveEdit()',
            'class' => 'save saveRmaBtn',
        ), -100);

        $this->_formScripts[] = "
            function saveEdit(){
                editForm.submit($('edit_form').action);
            }
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action + 'back/edit/');
            }
            function disableActionButton(button) {
                button.disabled = true;
                button.classList.add('disabled');
            }
        ";
        $rma = $this->getRma();
        if ($rma) {
            $this->_addButton('print', array(
                'label' => Mage::helper('sales')->__('Print'),
                'onclick' => 'var win = window.open(\''.$rma->getPrintUrl().'\', \'_blank\');win.focus();',
            ));

            $this->_addButton('order_exchange', array(
                'label' => Mage::helper('sales')->__('Exchange Order'),
                'onclick' => 'disableActionButton(this); var win = window.open(\''.$this->getCreateOrderUrl($rma).'\', \'_blank\');win.focus();',
            ));

//            $this->_addButton('order_replace', array(
//                'label' => Mage::helper('sales')->__('Replacement Order'),
//                'class' => ($rma->getExchangeOrderIds()) ? 'disabled' : '',
//                'disabled' => $rma->getExchangeOrderIds(),
//                'onclick' => 'disableActionButton(this); var win = window.open(\''.Mage::helper('adminhtml')->getUrl('adminhtml/rma_rma/createReplacement/', array('customer_id' => $rma->getCustomerId(), 'store_id' => $rma->getStoreId(), 'rma_id' => $rma->getId())).'\', \'_blank\');win.focus();',
//            ));
        }

        return $this;
    }

    /**
     * @param Mirasvit_Rma_Model_Rma $rma
     *
     * @return string
     */
    public function getCreateOrderUrl($rma)
    {
        return $this->getUrl('adminhtml/sales_order_create/index/', array('customer_id' => $rma->getCustomerId(), 'store_id' => $rma->getStoreId(), 'rma_id' => $rma->getId()));
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    public function getRma()
    {
        if (Mage::registry('current_rma') && Mage::registry('current_rma')->getId()) {
            return Mage::registry('current_rma');
        }
    }

    public function getHeaderText()
    {
        if ($rma = $this->getRma()) {
            return Mage::helper('rma')->__('RMA #%s - %s', $rma->getIncrementId(), $rma->getStatus()->getName());
        } else {
            return Mage::helper('rma')->__('Create New RMA');
        }
    }

    /************************/
}
