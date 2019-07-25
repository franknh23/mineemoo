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



class Mirasvit_Rma_Model_Resource_Fedex_Label_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /*
     * Constructs collection model for FedEx labels
     */
    protected function _construct()
    {
        $this->_init('rma/fedex_label');
    }

    /*
     * Constructs collection model for FedEx labels
     *
     * @param bool - if true, additional element "Please select" is added.
     *
     * @return array
     */
    public function toOptionArray($emptyOption = false)
    {
        $arr = array();
        if ($emptyOption) {
            $arr[0] = array('value' => 0, 'label' => Mage::helper('helpdesk')->__('-- Please Select --'));
        }
        foreach ($this as $item) {
            $arr[] = array('value' => $item->getId(), 'label' => $item->getName());
        }

        return $arr;
    }

    /*
     * Constructs collection model for FedEx labels
     *
     * @param bool - if true, additional element "Please select" is added.
     *
     * @return array
     */
    public function getOptionArray($emptyOption = false)
    {
        $arr = array();
        if ($emptyOption) {
            $arr[0] = Mage::helper('helpdesk')->__('-- Please Select --');
        }
        foreach ($this as $item) {
            $arr[$item->getId()] = $item->getName();
        }

        return $arr;
    }
}
