<?php

/**
 *  * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2018 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.sellxed.com/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.sellxed.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
 */

//require_once 'Customweb/Saferpay/Method/AbstractMethod.php';
//require_once 'Customweb/Saferpay/PaymentInformation/DefaultProvider.php';



/**
 *
 * @author Thomas Hunziker
 * @Method(paymentMethods={'ideal','eps', 'giropay', 'directebanking', 'directdebits', 'paydirekt', 'twint', 'ChinaUnionpay'})
 */
class Customweb_Saferpay_Method_Default extends Customweb_Saferpay_Method_AbstractMethod {
	

	public function getVisibleFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction, $failedTransaction){
		$elements = array();
		return $elements;
	}


	public function getAdditionalCaptureParameters(){
		return array();
	}
	
	
	public function getServerFlow(){
		return 'none';
	}
	
	public function getAddressBehavior(){
		return $this->getPaymentMethodConfigurationValue('address_mode');
	}
	
	public function getAuthorizationParameters(Customweb_Saferpay_Authorization_Transaction $transaction, array $formData){
		return array();
	}
	
	public function isCustomerConfirmationEmailSentByPSP(){
		if($this->existsPaymentMethodConfigurationValue('customer_email') && $this->getPaymentMethodConfigurationValue('customer_email') == 'true'){
			return true;
		}
		return false;
	}
	
	public function getPaymentInformationProvider(){
		return new Customweb_Saferpay_PaymentInformation_DefaultProvider();
	}
	
}