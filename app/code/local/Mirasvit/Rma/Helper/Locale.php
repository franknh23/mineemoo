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



class Mirasvit_Rma_Helper_Locale
{
    public function setLocaleValue($object, $field, $value)
    {
        $storeId = (int) $object->getStoreId();
        $serializedValue = $object->getData($field);
        $arr = $this->unserialize($serializedValue);

        if ($storeId === 0) {
            $arr[0] = $value;
        } else {
            $arr[$storeId] = $value;
            if (!isset($arr[0])) {
                $arr[0] = $value;
            }
        }
        $object->setData($field, serialize($arr));
                       // pr($object->getData());die;
    }

    public function getLocaleValue($object, $field)
    {
        $storeId = ($object->getStoreId()) ? (int) $object->getStoreId() : Mage::app()->getStore()->getId();
        $serializedValue = $object->getData($field);
        $arr = $this->unserialize($serializedValue);
        // pr($arr);die;
        $defaultValue = null;
        if (isset($arr[0])) {
            $defaultValue = $arr[0];
        }

        if (isset($arr[$storeId])) {
            $localizedValue = $arr[$storeId];
        } else {
            $localizedValue = $defaultValue;
        }

        return $localizedValue;
    }

    public function unserialize($string)
    {
        if (strpos($string, 'a:') !== 0) {
            return array(0 => $string);
        }
        if (!$string) {
            return array();
        }
        try {
            return @unserialize($string);
        } catch (Exception $e) {
            return array(0 => $string);
        }
    }
}
