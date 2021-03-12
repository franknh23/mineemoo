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

//require_once 'Customweb/Saferpay/ParameterBuilder/Authorization/Moto.php';
//require_once 'Customweb/Payment/Authorization/Moto/IAdapter.php';
//require_once 'Customweb/Saferpay/Authorization/AbstractRedirectionAdapter.php';



/**
 *
 * @author Nico Eigenmann
 * @Bean
 *
 */
class Customweb_Saferpay_Authorization_Moto_Adapter extends Customweb_Saferpay_Authorization_AbstractRedirectionAdapter implements 
		Customweb_Payment_Authorization_Moto_IAdapter {

	public function getAuthorizationMethodName(){
		return self::AUTHORIZATION_METHOD_NAME;
	}
	
	public function getAdapterPriority(){
		return 500;
	}

	public function createTransaction(Customweb_Payment_Authorization_Moto_ITransactionContext $transactionContext, $failedTransaction){
		return $this->createTransactionInner($transactionContext, $failedTransaction);
	}
	
	public function getParameters(Customweb_Payment_Authorization_ITransaction $transaction){
		return array();
	}
	
	
	public function getFormActionUrl(Customweb_Payment_Authorization_ITransaction $transaction){
		return $this->generateRedirectUrl($transaction, array());
	}
	
	protected function getParameterBuilder($transaction, $formData){
		return new Customweb_Saferpay_ParameterBuilder_Authorization_Moto($this->getContainer(), $transaction, $formData);
	}

}