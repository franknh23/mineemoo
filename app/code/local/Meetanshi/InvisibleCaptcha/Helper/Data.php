<?php

class Meetanshi_InvisibleCaptcha_Helper_Data extends Mage_Core_Helper_Abstract
{
    const GOOGLE_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';
    const CONFIG_PATH_GENERAL_ENABLE_MODULE = 'recaptcha/general/enabled';
    const CONFIG_PATH_GENERAL_SITE_KEY = 'recaptcha/general/sitekey';
    const CONFIG_PATH_GENERAL_SECRET_KEY = 'recaptcha/general/sitesecret';
    const CONFIG_PATH_ADVANCED_URLS = 'recaptcha/general/urls';
    const CONFIG_PATH_ADVANCED_SELECTORS = 'recaptcha/general/selectors';

    public function stringValidationAndConvertToArray($string)
    {
        $validate = function ($urls) {
            return preg_split('|\s*[\r\n]+\s*|', $urls, -1, PREG_SPLIT_NO_EMPTY);
        };

        return $validate($string);
    }
    public function isEnabled()
    {
        return Mage::getStoreConfig(self::CONFIG_PATH_GENERAL_ENABLE_MODULE);
    }
    public function getSiteKey()
    {
        return Mage::getStoreConfig(self::CONFIG_PATH_GENERAL_SITE_KEY);
    }

    public function getSelectorsJson()
    {
        $selectors = trim(Mage::getStoreConfig(self::CONFIG_PATH_ADVANCED_SELECTORS));
        $selectors = $selectors ? $this->stringValidationAndConvertToArray($selectors) : [];
        return \Zend_Json::encode(array_merge($selectors));
    }
    public function getUrls()
    {
        $urls = trim(Mage::getStoreConfig(self::CONFIG_PATH_ADVANCED_URLS));
        $urls = $urls ? $this->stringValidationAndConvertToArray($urls) : [];
        $newUrl=array();
        foreach($urls as $url)
        {
            array_push($newUrl,Mage::getBaseUrl().$url);
        }
        return $newUrl;
    }

    public function verify($token)
    {
        $verification = array(
            'success' => false,
            'error' => ''
        );
        if ($token) {
            try {
                $secret = Mage::getStoreConfig(self::CONFIG_PATH_GENERAL_SECRET_KEY);
                $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' .
                    $secret . '&response=' . $token . '&remoteip=' . $_SERVER["REMOTE_ADDR"];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $response = curl_exec($ch);

                $result = json_decode($response, true);
                if ($result['success']) {
                    $verification['success'] = true;
                } elseif (array_key_exists('error-codes', $result)) {
                    $verification['error'] = $this->getErrorMessage($result['error-codes'][0]);
                }
            } catch (Exception $e) {
                $verification['error'] = $e->getMessage();
            }
        }

        return $verification;
    }

    public function getErrorMessage($errorCode)
    {
        $errorCodesGoogle = [
            'missing-input-secret' => __('The secret parameter is missing.'),
            'invalid-input-secret' => __('The secret parameter is invalid or malformed.'),
            'missing-input-response' => __('The response parameter is missing.'),
            'invalid-input-response' => __('The response parameter is invalid or malformed.'),
            'bad-request' => __('The request is invalid or malformed.')
        ];

        if (array_key_exists($errorCode, $errorCodesGoogle)) {
            return $errorCodesGoogle[$errorCode];
        }

        return 'Something is wrong.';
    }
}
