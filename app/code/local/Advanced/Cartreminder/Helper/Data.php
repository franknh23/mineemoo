<?php

class Advanced_Cartreminder_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function generateLicense() {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $alphabet .= '0123456789';
        $key = '';
        
        $key = $this->getRandomString(30, $alphabet);
        
        
        $checkLicenseExist = Mage::getModel('cartreminder/reminder')->load($key, 'key');
        
        if ($checkLicenseExist->getId()) {
            $key = $this->generateLicense();            
        }
        
        return $key;
    }

  
    public function getRandomString($len, $chars = null) {
        if (is_null($chars)) {
            $chars = 'abcdefghijklmnopqrstuvwxyz' . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . '0123456789';
        }
        mt_srand(10000000 * (double) microtime());
        for ($i = 0, $str = '', $lc = strlen($chars) - 1; $i < $len; $i++) {
            $str .= $chars[mt_rand(0, $lc)];
        }
        return $str;
    }
}