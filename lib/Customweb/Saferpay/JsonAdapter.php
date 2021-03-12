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

//require_once 'Customweb/Saferpay/Container.php';
//require_once 'Customweb/Payment/Authorization/ErrorMessage.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Saferpay/Exception/RemoteErrorNameException.php';
//require_once 'Customweb/Payment/Exception/PaymentErrorException.php';
//require_once 'Customweb/Core/Http/Client/Factory.php';
//require_once 'Customweb/Core/Http/Authorization/Basic.php';
//require_once 'Customweb/Saferpay/IConstants.php';
//require_once 'Customweb/Payment/Authorization/DefaultTransactionHistoryItem.php';
//require_once 'Customweb/Core/Http/Request.php';

class Customweb_Saferpay_JsonAdapter {
	/**
	 * @var Customweb_Saferpay_Container
	 */
	protected $container = null;

	public function __construct(Customweb_DependencyInjection_IContainer $container){
		if (!($container instanceof Customweb_Saferpay_Container)) {
			$container = new Customweb_Saferpay_Container($container);
		}
		$this->container = $container;
	}

	/**
	 *
	 * @return Customweb_Saferpay_Container
	 */
	protected function getContainer(){
		return $this->container;
	}

	protected function getConfiguration(){
		return $this->getContainer()->getConfiguration();
	}

	/**
	 *
	 * @param String| Customweb_Core_Url $url
	 * @param array $body
	 * @return array
	 * @throws Exception | Customweb_Saferpay_Exception_RemoteErrorNameException
	 */
	public function sendRequest($url, array $body = array()){
		$request = new Customweb_Core_Http_Request($url);
		$request->setMethod('POST');
		$request->appendHeader('Content-type:application/json');
		$request->appendHeader('Accept:application/json');
		$request->setBody(json_encode($body));
		$request->setAuthorization(
				new Customweb_Core_Http_Authorization_Basic($this->getConfiguration()->getJsonUsername(), $this->getConfiguration()->getJsonPassword()));
		$response = Customweb_Core_Http_Client_Factory::createClient()->send($request);
		
		$responseArray = $this->converToArrayAndCheckForError($response);
		return $responseArray;
	}

	/**
	 *
	 * @param Customweb_Core_Http_IResponse $response
	 * @throws Customweb_Saferpay_Exception_RemoteErrorNameException
	 */
	private function converToArrayAndCheckForError(Customweb_Core_Http_IResponse $response){
		$frontendErrorMessage = Customweb_I18n_Translation::__('Technical problem, please contact the merchant.');
		$backendErrorMessage = Customweb_I18n_Translation::__('Received malformed response');
		
		$responseArray = json_decode($response->getBody(), true);
		if ($responseArray === false) {
			$backendErrorMessage = Customweb_I18n_Translation::__('Received malformed response.');
			throw new Customweb_Saferpay_Exception_RemoteErrorNameException(
					new Customweb_Payment_Authorization_ErrorMessage($frontendErrorMessage, $backendErrorMessage), 
					Customweb_Saferpay_IConstants::ERROR_NAME_NOT_RETURNED);
		}
		
		if ($response->getStatusCode() != 200) {
			$backendErrorMessage = Customweb_I18n_Translation::__('Error server responded with status code @status', 
					array(
						'@status' => $response->getStatusCode() 
					));
			
			$errorName = Customweb_Saferpay_IConstants::ERROR_NAME_NOT_RETURNED;
			if (isset($responseArray['ErrorName'])) {
				$errorName = $responseArray['ErrorName'];
				switch ($responseArray['ErrorName']) {
					case Customweb_Saferpay_IConstants::ERROR_NAME_AUTHENTICATION_FAILED:
						$backendErrorMessage = Customweb_I18n_Translation::__('Authentication failed, please check the configuration');
						break;
					case Customweb_Saferpay_IConstants::ERROR_NAME_ALIAS_INVALID:
						$backendErrorMessage = Customweb_I18n_Translation::__('Unkown alias or already used.');
						break;
					case Customweb_Saferpay_IConstants::ERROR_NAME_BLOCKED_BY_RISK_MANAGEMENT:
						$frontendErrorMessage = Customweb_I18n_Translation::__('Transaction blocked by risk management.');
						$backendErrorMessage = Customweb_I18n_Translation::__('Action blocked by risk management');
						break;
					case Customweb_Saferpay_IConstants::ERROR_NAME_CARD_CHECK_FAILED:
					case Customweb_Saferpay_IConstants::ERROR_NAME_CARD_CVC_INVALID:
					case Customweb_Saferpay_IConstants::ERROR_NAME_CARD_CVC_REQUIRED:
						$frontendErrorMessage = Customweb_I18n_Translation::__('Please check your card details.');
						$backendErrorMessage = Customweb_I18n_Translation::__('Customer entered invalid card details');
						break;
					case Customweb_Saferpay_IConstants::ERROR_NAME_CARD_EXPIRED:
						$frontendErrorMessage = Customweb_I18n_Translation::__('Your card is expired.');
						$backendErrorMessage = Customweb_I18n_Translation::__('Customer used expried card.');
						break;
					case Customweb_Saferpay_IConstants::ERROR_NAME_PAYMENTMEANS_INVALID:
						$frontendErrorMessage = Customweb_I18n_Translation::__('Please check your payment details.');
						$backendErrorMessage = Customweb_I18n_Translation::__('Invalid payment means.');
						break;
					case Customweb_Saferpay_IConstants::ERROR_NAME_INTERNAL_ERROR:
						$frontendErrorMessage = Customweb_I18n_Translation::__('Technical error, please try again.');
						$backendErrorMessage = Customweb_I18n_Translation::__('Internal error at ____paymentServiceProvider____.');
						break;
					case Customweb_Saferpay_IConstants::ERROR_NAME_3DS_AUTHENTICATION_FAILED:
						$frontendErrorMessage = Customweb_I18n_Translation::__('Your 3ds check failed.');
						$backendErrorMessage = Customweb_I18n_Translation::__('3d secure failed.');
						break;
					case Customweb_Saferpay_IConstants::ERROR_NAME_NO_CONTRACT:
						$backendErrorMessage = Customweb_I18n_Translation::__('Payment method not available.');
						break;
					case Customweb_Saferpay_IConstants::ERROR_NAME_NO_CREDITS_AVAILABLE:
						$frontendErrorMessage = Customweb_I18n_Translation::__('Credit limit exceeded.');
						$backendErrorMessage = Customweb_I18n_Translation::__('Credit limit exceeded.');
						break;
					case Customweb_Saferpay_IConstants::ERROR_NAME_PERMISSION_DENIED:
						$backendErrorMessage = Customweb_I18n_Translation::__('Missing permissions for this action.');
						break;
					case Customweb_Saferpay_IConstants::ERROR_NAME_TRANSACTION_DECLINED:
						$frontendErrorMessage = Customweb_I18n_Translation::__('Transaction declined.');
						$backendErrorMessage = Customweb_I18n_Translation::__('Transaction declined.');
						break;
					case Customweb_Saferpay_IConstants::ERROR_NAME_VALIDATION_FAILED:
						$backendErrorMessage = Customweb_I18n_Translation::__('Request Validation failed.');
						break;
					case Customweb_Saferpay_IConstants::ERROR_NAME_AMOUNT_INVALID:
						$backendErrorMessage = Customweb_I18n_Translation::__('Invalid amount.');
						break;
					case Customweb_Saferpay_IConstants::ERROR_NAME_CURRENCY_INVALID:
						$backendErrorMessage = Customweb_I18n_Translation::__('Invalid Currency.');
						break;
					case Customweb_Saferpay_IConstants::ERROR_NAME_COMMUNICATION_FAILED:
						$frontendErrorMessage = Customweb_I18n_Translation::__('Technical error, please try again.');
						$backendErrorMessage = Customweb_I18n_Translation::__('Communication with the processor failed.');
						break;
					case Customweb_Saferpay_IConstants::ERROR_NAME_COMMUNICATION_TIMEOUT:
						$backendErrorMessage = Customweb_I18n_Translation::__(
								'Communication with the processor timed out. Please check with aquirer.');
						break;
					case Customweb_Saferpay_IConstants::ERROR_NAME_TOKEN_EXPIRED:
						$frontendErrorMessage = Customweb_I18n_Translation::__('Session timed out, please try again.');
						$backendErrorMessage = Customweb_I18n_Translation::__('Expired Token.');
						break;
					case Customweb_Saferpay_IConstants::ERROR_NAME_TOKEN_INVALID:
						$backendErrorMessage = Customweb_I18n_Translation::__('Invalid Token.');
						break;
					case Customweb_Saferpay_IConstants::ERROR_NAME_TRANSACTION_IN_WRONG_STATE:
						$backendErrorMessage = Customweb_I18n_Translation::__('Transaction in wrong state.');
						break;
					case Customweb_Saferpay_IConstants::ERROR_NAME_ACTION_NOT_SUPPORTED:
						$backendErrorMessage = Customweb_I18n_Translation::__('Action not supported.');
						break;
					case Customweb_Saferpay_IConstants::ERROR_NAME_TRANSACTION_NOT_FOUND:
						$backendErrorMessage = Customweb_I18n_Translation::__('Transaction not found.');
						break;
					case Customweb_Saferpay_IConstants::ERROR_NAME_CONDITION_NOT_SATISFIED:
						$backendErrorMessage = Customweb_I18n_Translation::__('Condition not staisfied.');
						break;
					case Customweb_Saferpay_IConstants::ERROR_NAME_TRANSACTION_NOT_STARTED:
						$backendErrorMessage = Customweb_I18n_Translation::__('Transaction not started by the customer.');
						break;
					case Customweb_Saferpay_IConstants::ERROR_NAME_TRANSACTION_ABORTED:
						$frontendErrorMessage = Customweb_I18n_Translation::__('Transaction aborted');
						$backendErrorMessage = Customweb_I18n_Translation::__('Transaction aborted by the customer.');
						break;
					case Customweb_Saferpay_IConstants::ERROR_NAME_GENERAL_DECLINED:
						$frontendErrorMessage = Customweb_I18n_Translation::__('Transaction declined.');
						$backendErrorMessage = Customweb_I18n_Translation::__('Transaction declined.');
					default:
						$backendErrorMessage = Customweb_I18n_Translation::__('Failed with error: !name', 
								array(
									'!name' => $responseArray['ErrorName'] 
								));
				}
				
			}
			if (isset($responseArray['ErrorMessage'])) {
				$backendErrorMessage .= " " . $responseArray['ErrorMessage'];
			}
			if (isset($responseArray['ErrorDetail']) && is_array($responseArray['ErrorDetail'])) {
				$backendErrorMessage .= " " . Customweb_I18n_Translation::__("Details: !details", 
						array(
							'!details' => implode(" ", $responseArray['ErrorDetail']) 
						));
			}
			elseif (isset($responseArray['ErrorDetail']) && is_string($responseArray['ErrorDetail'])) {
				$backendErrorMessage .= " " . Customweb_I18n_Translation::__("Details: !details", $responseArray['ErrorDetail']);
			}
			$errorMessage = new Customweb_Payment_Authorization_ErrorMessage($frontendErrorMessage, $backendErrorMessage);
			throw new Customweb_Saferpay_Exception_RemoteErrorNameException($errorMessage, $errorName);
		}
		return $responseArray;
	}

	/**
	 *
	 * @param Customweb_Saferpay_Authorization_Transaction $transaction
	 * @param array $result
	 */
	public function checkPaymentResult(Customweb_Saferpay_Authorization_Transaction $transaction, array $result){
		$transaction->setJsonAuthorizationParameters($result);
		if ($result['Transaction']['Type'] != 'PAYMENT') {
			throw new Customweb_Payment_Exception_PaymentErrorException(
					new Customweb_Payment_Authorization_ErrorMessage(Customweb_I18n_Translation::__('Technical problem, please contact the merchant.'), 
							Customweb_I18n_Translation::__('Received wrong transaction type: !type', 
									array(
										'!state' => $result['Transaction']['Type'] 
									))));
		}
		$transaction->setPaymentId($result['Transaction']['Id']);
		$state = $result['Transaction']['Status'];
		switch ($state) {
			case 'AUTHORIZED':
				$transaction->authorize();
				break;
			case 'CAPTURED':
				$transaction->authorize()->capture();
				break;
			default:
				throw new Customweb_Payment_Exception_PaymentErrorException(
						new Customweb_Payment_Authorization_ErrorMessage(
								Customweb_I18n_Translation::__('Technical problem, please contact the merchant.'), 
								Customweb_I18n_Translation::__('Received unkown transaction state: !state', 
										array(
											'!state' => $state 
										))));
		}
		
		if (isset($result['Liability']['ThreeDs']['LiabilityShift'])) {
			if (!$result['Liability']['ThreeDs']['LiabilityShift'] && $this->getConfiguration()->isMarkLiabilityShiftTransactions()) {
				$transaction->setAuthorizationUncertain();
			}
		}
		
		if (isset($result['RegistrationResult'])) {
			if ($result['RegistrationResult']['Success']) {
				$aliasId = $result['RegistrationResult']['Alias']['Id'];
				$transaction->setAliasId($aliasId);
				$transaction->setCardRefId($aliasId);
				$transaction->setAliasForDisplay($result['PaymentMeans']['DisplayText']);
			}
		}
		
		$paymentMethod = $this->getContainer()->getPaymentMethodByTransaction($transaction);
		if ($transaction->isCapturePossible() && $paymentMethod->existsPaymentMethodConfigurationValue('capturing') &&
				 $paymentMethod->getPaymentMethodConfigurationValue('capturing') == 'direct') {
			try {
				$captureAdapter = $this->getContainer()->getBean('Customweb_Saferpay_BackendOperation_Adapter_CaptureAdapter');
				$captureAdapter->capture($transaction);
			}
			
			catch (Exception $e) {
				$history = new Customweb_Payment_Authorization_DefaultTransactionHistoryItem(Customweb_I18n_Translation::__('Direct Capture failed.') . " " . $e->getMessage(),'capture');
				$transaction->addHistoryItem($history);
			}
		}
	}
}