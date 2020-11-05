<?php
class Stork_Shipcloud_Block_Customer_Address_Book extends Mage_Customer_Block_Address_Book
{
    public function getAddressHtml($address)
    {
      	$_pAddsses = Mage::getSingleton('customer/session')->getCustomer()->getDefaultShipping();
      	if(Mage::getSingleton('customer/session')->getCustomer()->getAddressById($_pAddsses) == $address){
      		if(Mage::getSingleton('customer/session')->isLoggedIn()) {
      		     	$customerData = Mage::getSingleton('customer/session')->getCustomer();
      		     	$customerId = $customerData->getId();
                $pakadooCollection = Mage::getModel('shipcloud/pakadoo')->getCollection();
                $pakadooCollection->addFieldToFilter('customer_id',array('eq' => $customerId));
                $pakadooCollection->getFirstItem();

          			if(!empty($pakadooCollection->getSize()) && !empty($pakadooCollection->getPakadooId()) && (trim($pakadooCollection->getPakadooId()) != '')){
          				$pakadooId = $pakadooCollection->getPakadooId();
          				$labelModel = Mage::getModel('shipcloud/shipcloud');
          				$responsePakadooAddress = $labelModel->createPakadooAddress($pakadooId);

          				if (empty($responsePakadooAddress['error'])) {
          					$arrFullShipping = array();
          					$arrFullName = array();
          					$addressData = $address->getData();

          					if(isset($addressData['firstname']) && trim($addressData['firstname']) != ''){
          						$arrFullName[] = $addressData['firstname'];
          					}
          					if(isset($addressData['middlename']) && trim($addressData['middlename']) != ''){
          						$arrFullName[] = $addressData['middlename'];
          					}
          					if(isset($addressData['lastname']) && trim($addressData['lastname']) != ''){
          						$arrFullName[] = $addressData['lastname'];
          					}

          					if(!empty($arrFullName)){
          						$arrFullShipping[] = implode(' ', $arrFullName);
          					}


          					if(isset($responsePakadooAddress['response']['company']) && trim($responsePakadooAddress['response']['company']) != ''){
          						$arrFullShipping[] = $responsePakadooAddress['response']['company'];
          					}

          					$arrStreet = array();
          					if(isset($responsePakadooAddress['response']['street']) && trim($responsePakadooAddress['response']['street']) != ''){
          						$arrStreet[] = $responsePakadooAddress['response']['street'];
          					}
          					if(isset($responsePakadooAddress['response']['street_no']) && trim($responsePakadooAddress['response']['street_no']) != ''){
          						$arrStreet[] = $responsePakadooAddress['response']['street_no'];
          					}

          					if(!empty($arrStreet)){
          						$arrFullShipping[] = implode(', ', $arrStreet);
          					}

          					$arrAddr = array();
          					if(isset($responsePakadooAddress['response']['city']) && trim($responsePakadooAddress['response']['city']) != ''){
          						$arrAddr[] = $responsePakadooAddress['response']['city'];
          					}
          					if(isset($responsePakadooAddress['response']['zip_code']) && trim($responsePakadooAddress['response']['zip_code']) != ''){
          						$arrAddr[] = $responsePakadooAddress['response']['zip_code'];
          					}

          					if(!empty($arrAddr)){
          						$arrFullShipping[] = implode(', ', $arrAddr);
          					}

          					if(isset($responsePakadooAddress['response']['country']) && trim($responsePakadooAddress['response']['country']) != ''){
          						$arrFullShipping[] = $responsePakadooAddress['response']['country'];
          					}

          					if(isset($addressData['telephone']) && trim($addressData['telephone']) != ''){
          						$arrFullShipping[] = 'T: '.$addressData['telephone'];
          					}

          					if(isset($addressData['fax']) && trim($addressData['fax']) != ''){
          						$arrFullShipping[] = 'F: '.$addressData['fax'];
          					}

          					if(!empty($arrFullShipping)){
          						return implode('<br>', $arrFullShipping);
          					}
    			       }
  			      }
  		    }
      	}
       return parent::getAddressHtml($address);
    }
}
