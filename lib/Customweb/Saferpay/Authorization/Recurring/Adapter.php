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

//require_once 'Customweb/Saferpay/ParameterBuilder/Authorization/Recurring.php';
//require_once 'Customweb/Saferpay/Authorization/AbstractAdapter.php';
//require_once 'Customweb/Payment/Authorization/ErrorMessage.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Payment/Exception/RecurringPaymentErrorException.php';
//require_once 'Customweb/Saferpay/ParameterBuilder/Authorization/RecurringLegacy.php';
//require_once 'Customweb/Saferpay/IConstants.php';
//require_once 'Customweb/Payment/Authorization/Recurring/IAdapter.php';


/**
 *
 * @author Nico Eigenmann
 * @Bean
 */
class Customweb_Saferpay_Authorization_Recurring_Adapter extends Customweb_Saferpay_Authorization_AbstractAdapter implements 
		Customweb_Payment_Authorization_Recurring_IAdapter {

	public function getAdapterPriority(){
		return 1000;
	}

	public function getAuthorizationMethodName(){
		return self::AUTHORIZATION_METHOD_NAME;
	}

	public function isPaymentMethodSupportingRecurring(Customweb_Payment_Authorization_IPaymentMethod $paymentMethod){
		try {
			return $this->getContainer()->getPaymentMethod($paymentMethod, $this->getAuthorizationMethodName())->isRecurringPaymentSupported();
		}
		catch (Exception $e) {
			return false;
		}
	}

	public function createTransaction(Customweb_Payment_Authorization_Recurring_ITransactionContext $transactionContext){
		return $this->createTransactionInner($transactionContext, null);
	}

	public function process(Customweb_Payment_Authorization_ITransaction $transaction){
		try {
			$initTransaction = $transaction->getTransactionContext()->getInitialTransaction();
			if ($initTransaction->isRecurringFlagUsed()) {
				$builder = new Customweb_Saferpay_ParameterBuilder_Authorization_Recurring($this->getContainer(), $transaction, array());
				$url = $this->getConfiguration()->getJsonBaseUrl() . Customweb_Saferpay_IConstants::JSON_AUTHORIZE_REFERENECED;
				$responseArray = $this->sendRequest($url, $builder->buildParameters());
				$this->checkPaymentResult($transaction, $responseArray);
			}
			else {
				//Legacy Recurring method
				$builder = new Customweb_Saferpay_ParameterBuilder_Authorization_RecurringLegacy($this->getContainer(), $transaction,
						array());
				$url = $this->getConfiguration()->getJsonBaseUrl() . Customweb_Saferpay_IConstants::JSON_AUTHORIZE_DIRECT;
				$responseArray = $this->sendRequest($url, $builder->buildParameters());
				$this->checkPaymentResult($transaction, $responseArray);
			}
		}
		catch (Customweb_Payment_Exception_PaymentErrorException $pe) {
			$transaction->setAuthorizationFailed($pe->getErrorMessage());
			throw new Customweb_Payment_Exception_RecurringPaymentErrorException($pe->getMessage());
		}
		catch (Exception $e) {
			$transaction->setAuthorizationFailed(
					new Customweb_Payment_Authorization_ErrorMessage(Customweb_I18n_Translation::__('Technical problem, please contact the merchant.'),
							$e->getMessage()));
			throw new Customweb_Payment_Exception_RecurringPaymentErrorException($e->getMessage());
		}
	}
}