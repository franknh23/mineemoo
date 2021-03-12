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

//require_once 'Customweb/Saferpay/Authorization/Transaction.php';
//require_once 'Customweb/Core/Exception/CastException.php';
//require_once 'Customweb/Payment/Authorization/ErrorMessage.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Payment/Authorization/Server/IAdapter.php';
//require_once 'Customweb/Saferpay/ParameterBuilder/Authorization/ServerForm.php';
//require_once 'Customweb/Saferpay/ParameterBuilder/Authorization/ServerRedirect.php';
//require_once 'Customweb/Saferpay/IConstants.php';
//require_once 'Customweb/Core/Http/Response.php';
//require_once 'Customweb/Saferpay/Authorization/AbstractRedirectionAdapter.php';



/**
 *
 * @author Nico Eigenmann
 * @Bean
 *
 */
class Customweb_Saferpay_Authorization_Server_Adapter extends Customweb_Saferpay_Authorization_AbstractRedirectionAdapter implements 
		Customweb_Payment_Authorization_Server_IAdapter {

	public function getAuthorizationMethodName(){
		return self::AUTHORIZATION_METHOD_NAME;
	}

	public function getAdapterPriority(){
		return 500;
	}

	public function createTransaction(Customweb_Payment_Authorization_Server_ITransactionContext $transactionContext, $failedTransaction){
		return $this->createTransactionInner($transactionContext, $failedTransaction);
	}

	protected function getParameterBuilder($transaction, $formData){
		return new Customweb_Saferpay_ParameterBuilder_Authorization_ServerRedirect($this->getContainer(), $transaction, $formData);
	}

	protected function getInitUrl(){
		return $this->getConfiguration()->getJsonBaseUrl() . Customweb_Saferpay_IConstants::JSON_REDIRECT_PAYMENT;
	}

	protected function getAssertUrl(){
		return $this->getConfiguration()->getJsonBaseUrl() . Customweb_Saferpay_IConstants::JSON_REDIRECT_PAYMENT_ASSERT;
	}

	public function processAuthorization(Customweb_Payment_Authorization_ITransaction $transaction, array $parameters){
		if (!($transaction instanceof Customweb_Saferpay_Authorization_Transaction)) {
			throw new Customweb_Core_Exception_CastException('Customweb_Saferpay_Authorization_Transaction');
		}
		
		if ($transaction->isUseExistingAlias()) {
			$this->handleAliasAuthorization($transaction, $parameters);
			if ($transaction->isAuthorizationFailed()) {
				return Customweb_Core_Http_Response::redirect($transaction->getFailedUrl());
			}
			else {
				return Customweb_Core_Http_Response::redirect($transaction->getSuccessUrl());
			}
		}
		
		$paymentMethod = $this->getContainer()->getPaymentMethodByTransaction($transaction);
		$url = $transaction->getFailedUrl();
		
		switch ($paymentMethod->getServerFlow()) {
			case 'redirect':
				$url = $this->generateRedirectUrl($transaction, $parameters);
				break;
			case 'direct':
				$builder = new Customweb_Saferpay_ParameterBuilder_Authorization_ServerForm($this->getContainer(), $transaction, 
						$parameters);
				
				$targetUrl = $this->getConfiguration()->getJsonBaseUrl() . Customweb_Saferpay_IConstants::JSON_AUTHORIZE_DIRECT;
				try {
					$responseArray = $this->sendRequest($targetUrl, $builder->buildParameters());
					$this->checkPaymentResult($transaction, $responseArray);
					$url = $transaction->getSuccessUrl();
				}
				catch (Customweb_Payment_Exception_PaymentErrorException $pe) {
					$transaction->setAuthorizationFailed($pe->getErrorMessage());
				}
				catch (Exception $e) {
					$transaction->setAuthorizationFailed(
							new Customweb_Payment_Authorization_ErrorMessage(
									Customweb_I18n_Translation::__('Technical problem, please contact the merchant.'), $e->getMessage()));
				}
				break;
			
			default:
				$transaction->setAuthorizationFailed(Customweb_I18n_Translation::__('Not supported'));
				break;
		}
		
		return Customweb_Core_Http_Response::redirect($url);
	}
}