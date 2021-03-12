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

//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Saferpay/Method/Default.php';


/**
 *
 * @author Nico Eigenmann
 * @Method(paymentMethods={'mastercard','visa','americanexpress','diners','jcb','saferpaytestcard','bonuscard','maestro','myone', 'bcmc','creditcard', 'vpay'})
 */
class Customweb_Saferpay_Method_CreditCard extends Customweb_Saferpay_Method_Default {
	
	public function isSupportingAliasInitialization() {
		return true;
	}
	
	public function getPaymentMeanParameter(){
		if ($this->existsPaymentMethodConfigurationValue('credit_card_brands')) {
			$map = $this->getPaymentInformationMap();
			$means = array();
			$cards = $this->getPaymentMethodConfigurationValue('credit_card_brands');
			
			foreach ($cards as $card) {
				$means[] = $map[strtolower($card)]['parameters']['mean'];
			}
			
			return array(
				'PaymentMethods' => $means 
			);
		}
		else {
			return parent::getPaymentMeanParameter();
		}
	}

	public function getAuthorizationParameters(Customweb_Saferpay_Authorization_Transaction $transaction, array $formData){
		$parameters = parent::getAuthorizationParameters($transaction, $formData);
		if (!$transaction->isUseExistingAlias()) {
			$parameters['CardForm'] = array(
				'HolderName' => $this->getHolderName() 
			);
		}
		return $parameters;
	}
	
	private function getHolderName() {
		if($this->existsPaymentMethodConfigurationValue('cardform_holdername')) {
			$holder = $this->getPaymentMethodConfigurationValue('cardform_holdername');
			if(empty($holder)) {
				throw new Exception(Customweb_I18n_Translation::__("You must configure a setting value for 'Card Form - Holder Name'."));
			}
			return $holder;
		}
		throw new Exception(Customweb_I18n_Translation::__("Setting 'Card Form - Holder Name' does not exist for payment method Credit Card."));
	}
}
