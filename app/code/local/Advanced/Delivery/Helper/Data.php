<?php

class Advanced_Delivery_Helper_Data extends Mage_Core_Helper_Abstract {

    function createDateRangeArray($strDateFrom, $strDateTo) {
        $aryRange = array();
        $iDateFrom = mktime(1, 0, 0, substr($strDateFrom, 5, 2), substr($strDateFrom, 8, 2), substr($strDateFrom, 0, 4));
        $iDateTo = mktime(1, 0, 0, substr($strDateTo, 5, 2), substr($strDateTo, 8, 2), substr($strDateTo, 0, 4));
        if ($iDateTo >= $iDateFrom) {
            array_push($aryRange, date('Y-n-d', $iDateFrom)); // first entry
            while ($iDateFrom < $iDateTo) {
                $iDateFrom+=86400; // add 24 hours
                array_push($aryRange, date('Y-n-d', $iDateFrom));
            }
        }
        return $aryRange;
    }

    public function getDateFormat() {
        $date_format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $str = explode("/", $date_format);
        foreach ($str as $key) {
            if ($key == 'yyyy' || $key == 'yy')
                $sort[] = 'Y';
            else
                $sort[] = substr(strtolower($key), 0, 1);
        }


        $result = '%' . $sort[0] . '-%' . $sort[1] . '-%' . $sort[2];

        return $result;
    }

    public function getHour() {

        $storeId = Mage::app()->getStore()->getStoreId();
        $intervals = Mage::getModel('delivery/intervals')->getCollection()
                ->addFieldToFilter('store_id', array('finset' => $storeId));

        $options = array();
        foreach ($intervals as $interval) {
            if($interval->getStatus()=='1')
            {
              $options[] = array('value' => $interval->getHourstart() . ' - ' . $interval->getHourto(), 'label' => $interval->getHourstart() . ' - ' . $interval->getHourto());  
            }
            
        }
        return $options;
    }

}
