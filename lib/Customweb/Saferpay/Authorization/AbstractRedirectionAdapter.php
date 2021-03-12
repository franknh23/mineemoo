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

//require_once 'Customweb/Saferpay/Authorization/AbstractAdapter.php';
//require_once 'Customweb/Payment/Authorization/ErrorMessage.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Util/Address.php';
//require_once 'Customweb/Saferpay/ParameterBuilder/Authorization/Alias.php';
//require_once 'Customweb/Saferpay/ParameterBuilder/StatusCheck.php';
//require_once 'Customweb/Saferpay/IConstants.php';


/**
 *
 * @author Nico Eigenmann
 *
 */
abstract class Customweb_Saferpay_Authorization_AbstractRedirectionAdapter extends Customweb_Saferpay_Authorization_AbstractAdapter {

	abstract protected function getParameterBuilder($transaction, $formData);

	public function getVisibleFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction, $failedTransaction, $paymentCustomerContext){
		return $this->getContainer()->getPaymentMethod($orderContext->getPaymentMethod(), $this->getAuthorizationMethodName())->getVisibleFormFields(
				$orderContext, $aliasTransaction, $failedTransaction);
	}

	protected function generateRedirectUrl(Customweb_Saferpay_Authorization_Transaction $transaction, array $formData){
		if ($transaction->isUseExistingAlias() && Customweb_Util_Address::compareShippingAddresses(
				$transaction->getTransactionContext()->getOrderContext(),
				$transaction->getTransactionContext()->getAlias()->getTransactionContext()->getOrderContext())) {
			if ($this->getContainer()->getPaymentMethodByTransaction($transaction)->isSupportingAliasInitialization()) {
				return $this->getContainer()->getAliasAdapter()->generateRedirectUrl($transaction, $formData);
			}
			else {
				return $this->getContainer()->getEndpointAdapter()->getUrl('process', 'alias',
						array(
							'cw_transaction_id' => $transaction->getExternalTransactionId(),
							Customweb_Saferpay_IConstants::PARAMETER_SIGNATURE => $transaction->getSecuritySignature('process/alias') 
						));
			}
		}
		
		if ($transaction->getRedirectUrl() === null) {
			try {
				$builder = $this->getParameterBuilder($transaction, $formData);
				$parameters = $builder->buildParameters();
				$url = $this->getInitUrl();
				$result = $this->sendRequest($url, $parameters);
				if (!isset($result['RedirectUrl']) || !isset($result['Token'])) {
					throw new Exception(Customweb_I18n_Translation::__('No redirect url or token received.'));
				}
				$transaction->setRedirectUrl($result['RedirectUrl']);
				$transaction->setInitToken($result['Token']);
			}
			catch (Customweb_Payment_Exception_PaymentErrorException $pe) {
				$transaction->setAuthorizationFailed($pe->getErrorMessage());
				$transaction->setRedirectUrl($transaction->getFailedUrl());
			}
			catch (Exception $e) {
				$transaction->setAuthorizationFailed(
						new Customweb_Payment_Authorization_ErrorMessage(
								Customweb_I18n_Translation::__('Technical problem, please contact the merchant.'), $e->getMessage()));
				$transaction->setRedirectUrl($transaction->getFailedUrl());
			}
		}
		return $transaction->getRedirectUrl();
	}

	protected function getInitUrl(){
		return $this->getConfiguration()->getJsonBaseUrl() . Customweb_Saferpay_IConstants::JSON_PAYMENTPAGE_INIT;
	}

	protected function getAssertUrl(){
		return $this->getConfiguration()->getJsonBaseUrl() . Customweb_Saferpay_IConstants::JSON_PAYMENTPAGE_ASSERT;
	}

	public function getResultAndUpdate(Customweb_Saferpay_Authorization_Transaction $transaction){
		if ($transaction->isAuthorized() || $transaction->isAuthorizationFailed()) {
			return;
		}
		$builder = new Customweb_Saferpay_ParameterBuilder_StatusCheck($this->getContainer(), $transaction);
		$url = $this->getAssertUrl();
		
		// There exists an timing issue for some payment methods. 
		// The notification is sent before the customer is returned to the success page, the assert call will then return an error.
		// The PSP recommends to retry the assert call after a short delay.
		try {
			$attempt = 0;
			while (true) {
				try {
					$responseArray = $this->sendRequest($url, $builder->buildParameters());
					$this->checkPaymentResult($transaction, $responseArray);
					break;
				}
				catch (Customweb_Saferpay_Exception_RemoteErrorNameException $re) {
					
					if ($re->getRemoteErrorName() == Customweb_Saferpay_IConstants::ERROR_NAME_TRANSACTION_IN_WRONG_STATE) {
						if ($attempt > 2) {
							throw $re;
						}
						$attempt++;
						sleep(4);
					}
					else {
						throw $re;
					}
				}
			}
		}
		catch (Customweb_Payment_Exception_PaymentErrorException $pe) {
			$transaction->setAuthorizationFailed($pe->getErrorMessage());
		}
		catch (Exception $e) {
			$transaction->setAuthorizationFailed(
					new Customweb_Payment_Authorization_ErrorMessage(Customweb_I18n_Translation::__('Technical problem, please contact the merchant.'),
							$e->getMessage()));
		}
	}

	public function handleAliasAuthorization(Customweb_Saferpay_Authorization_Transaction $transaction, $formData){
		if ($transaction->getTransactionContext()->getAlias() == null || $transaction->getTransactionContext()->getAlias() == 'new') {
			$transaction->setAuthorizationFailed(Customweb_I18n_Translation::__('Not an alias transaction'));
			return;
		}
		
		if (!Customweb_Util_Address::compareShippingAddresses($transaction->getTransactionContext()->getOrderContext(),
				$transaction->getTransactionContext()->getAlias()->getTransactionContext()->getOrderContext())) {
			$transaction->setAuthorizationFailed(
					Customweb_I18n_Translation::__('Shipping address of this transaction and initial alias transaction do not match.'));
			return;
		}
		
		$builder = new Customweb_Saferpay_ParameterBuilder_Authorization_Alias($this->getContainer(), $transaction, $formData);
		$url = $this->getConfiguration()->getJsonBaseUrl() . Customweb_Saferpay_IConstants::JSON_AUTHORIZE_DIRECT;
		
		try {
			$responseArray = $this->sendRequest($url, $builder->buildParameters());
			$this->checkPaymentResult($transaction, $responseArray);
		}
		catch (Customweb_Payment_Exception_PaymentErrorException $pe) {
			$transaction->setAuthorizationFailed($pe->getErrorMessage());
		}
		catch (Exception $e) {
			$transaction->setAuthorizationFailed(
					new Customweb_Payment_Authorization_ErrorMessage(Customweb_I18n_Translation::__('Technical problem, please contact the merchant.'),
							$e->getMessage()));
		}
	}
}
	