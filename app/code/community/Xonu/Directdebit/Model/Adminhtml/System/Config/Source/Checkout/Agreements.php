<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Model_Adminhtml_System_Config_Source_Checkout_Agreements {

    protected $_options;

    public function toOptionArray()
    {
        if (is_null($this->_options)) {
            $this->_options = array();
            /* @var $agreements Mage_Checkout_Model_Resource_Agreement_Collection */
            // $currentConfigurationScope = Mage::app()->getStore(Mage::app()->getRequest()->getParam('store'))->getId();
            $agreements = Mage::getModel('checkout/agreement')->getCollection()
                //->addStoreFilter($currentConfigurationScope)
                //->addFieldToFilter('is_active', 1)
            ;

            $this->_options[] = array(
                'label' => Mage::helper('adminhtml')->__('-- Please Select an Agreement --'),
                'value' => ''
            );

            foreach ($agreements as $_a)
            {
                $this->_options[] = array(
                    'value' => $_a->getId(),
                    // 'label' => $_a->getName().' ('.$_a->getCheckboxText().')',
                    'label' => $_a->getName(),
                );
            }
        }
        return $this->_options;
    }

}