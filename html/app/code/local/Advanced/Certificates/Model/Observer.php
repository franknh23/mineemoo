<?php

/**
 * Advanced Checkout
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Onestepcheckout.com license that is
 * available through the world-wide-web at this URL:
 * http://www.advancedcheckout.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category 	Advanced Checkout
 * @package 	Advanced_Onestepcheckout
 * @copyright 	Copyright (c) 2012 Advanced Checkout (http://www.advancedcheckout.com/)
 * @license 	http://www.advancedcheckout.com/license-agreement.html
 */

/**
 * Onestepcheckout Block
 * 
 * @category 	Onestepcheckout
 * @package 	Advanced_Onestepcheckout
 * @author  	Onestepcheckout Developer
 */
class Advanced_Certificates_Model_Observer {

    const SCOPE_DEFAULT = 'default';
    const SCOPE_WEBSITES = 'websites';
    const SCOPE_STORES = 'stores';

    /**
     * process admin_system_config_section_save_after event
     *
     * @return Advanced_Certificates_Model_Observer
     */
    public function updateLicense($observer) {
      
        $params = Mage::app()->getRequest()->getParams();
        $section = $observer->getEvent()->getSection();
        if($section!='onestepcheckout'){
			return;
		}
        $extensions = $params['groups']['license']['fields'];
        
   
        $proxy = new SoapClient('http://www.advancedcheckout.com/api/soap/?wsdl');
        $sessionId = $proxy->login('checkinglicense', 'ael@4fE3@!5^');        
        
        foreach($extensions as $sku => $extension){
           if(!$extension['value'])
               continue;
            if($_SERVER['HTTP_HOST']){
                $domain = $_SERVER['HTTP_HOST'];
            }else{
                $domain = $_SERVER['SERVER_NAME'];
            }

            $ip = gethostbyname($domain);

            $filters = array(
                'license' => $extension['value'],
                'domain' => $domain,
                'ip' => $ip,
                'sku-extension' => $sku
            );

            $license = $proxy->call($sessionId, 'mwlicense_checking.info', array($filters));
            if(!$license['success']){
                Mage::getSingleton('adminhtml/session')->addError($license['message']);
            }else{
                $file = Mage::getBaseDir().DS.'app'.DS.'code'.DS.'local'.DS.'Advanced'.DS.'Certificates'.DS.$sku.'.xml';            
                try{
                    Mage::getModel('certificates/file')->writeFile($license,$file);
                }catch(Exception $e)
                {

                }
            }
        }
      
    }

}
