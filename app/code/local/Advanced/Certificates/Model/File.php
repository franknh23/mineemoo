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
class Advanced_Certificates_Model_File {

    function writeFile($data, $file) {

        $dom = new DOMDocument();
        $dom->formatOutput = true;
        $license = $dom->createElement("license");
        $dom->appendChild($license);
        
                $productName = $dom->createElement("product_name");
                $productName->appendChild(
                $dom->createTextNode($data['data']['product_name']));
        $license->appendChild($productName);
        
                $licenseText = $dom->createElement("license_text");
                $licenseText->appendChild($dom->createTextNode($data['data']['license']));
        $license->appendChild($licenseText);
        
                $domain = $dom->createElement("domain");
                $domain->appendChild($dom->createTextNode($data['data']['domain']));
        $license->appendChild($domain);
        
                $email = $dom->createElement("email");
                $email->appendChild($dom->createTextNode($data['data']['email']));
        $license->appendChild($email);
        
                $type = $dom->createElement("type");
                $type->appendChild($dom->createTextNode($data['type']));
        $license->appendChild($type);
        
                $devDdomain = $dom->createElement("dev_domain");
                $devDdomain->appendChild($dom->createTextNode($data['data']['dev_domain']));
        $license->appendChild($devDdomain);
        
                $regisDate = $dom->createElement("register_date");
                $regisDate->appendChild($dom->createTextNode($data['data']['update_time']));
        $license->appendChild($regisDate);

        $dom->saveXML();
        $_file = fopen($file, 'w');

        fwrite($_file, $dom->saveXML());

        fclose($_file);
        
    }

    function readFile($file) {

        $dom = new DOMDocument();

        $dom->load($file);
     
        if($dom->hasChildNodes()){
            $licenseNode = $dom->getElementsByTagName("license")->item(0);

            $domain = $licenseNode->getElementsByTagName("domain")->item(0)->nodeValue;
            $license = $licenseNode->getElementsByTagName("license_text")->item(0)->nodeValue;
            $productName = $licenseNode->getElementsByTagName("product_name")->item(0)->nodeValue;
            $devDomain = $licenseNode->getElementsByTagName("dev_domain")->item(0)->nodeValue;
            $type = $licenseNode->getElementsByTagName("type")->item(0)->nodeValue;
            $email = $licenseNode->getElementsByTagName("email")->item(0)->nodeValue;
            $datetime = $licenseNode->getElementsByTagName("register_date")->item(0)->nodeValue;

            $result = array('domain' => $domain,
                'license' => $license,
                'product_name' => $productName,
                'dev_domain' => $devDomain,
                'type' => $type,
                'email' => $email,
                'register_date' => $datetime,
              );
            return $result;
        }else{
            return false;
        }
    }
    
    

}
