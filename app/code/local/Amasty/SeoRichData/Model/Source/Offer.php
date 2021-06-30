<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_SeoRichData
 */


class Amasty_SeoRichData_Model_Source_Offer extends Varien_Object
{
    const CONFIGURABLE = 0;
    const LIST_OF_SIMPLES = 1;
    const AGGREGATE = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('amseorichdata');

        return array(
            array(
                'value' => self::CONFIGURABLE,
                'label' => $helper->__('Main Offer')
            ),
            array(
                'value' => self::LIST_OF_SIMPLES,
                'label' => $helper->__('List of Associated Products Offers')
            ),
            array(
                'value' => self::AGGREGATE,
                'label' => $helper->__('Aggregate Offer')
            )
        );
    }
}
