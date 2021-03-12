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
//require_once 'Customweb/Core/DateTime.php';
//require_once 'Customweb/Payment/Authorization/IAdapter.php';
//require_once 'Customweb/Saferpay/JsonAdapter.php';



/**
 *
 * @author Nico Eigenmann
 *
 */
abstract class Customweb_Saferpay_Authorization_AbstractAdapter extends Customweb_Saferpay_JsonAdapter implements 
		Customweb_Payment_Authorization_IAdapter {

	public function isAuthorizationMethodSupported(Customweb_Payment_Authorization_IOrderContext $orderContext){
		return true;
	}

	public function isDeferredCapturingSupported(Customweb_Payment_Authorization_IOrderContext $orderContext, Customweb_Payment_Authorization_IPaymentCustomerContext $paymentContext){
		return $orderContext->getPaymentMethod()->existsPaymentMethodConfigurationValue('capturing');
	}

	public function preValidate(Customweb_Payment_Authorization_IOrderContext $orderContext, Customweb_Payment_Authorization_IPaymentCustomerContext $paymentContext){
		$this->getContainer()->getPaymentMethod($orderContext->getPaymentMethod(), $this->getAuthorizationMethodName())->preValidate($orderContext, 
				$paymentContext);
	}

	public function validate(Customweb_Payment_Authorization_IOrderContext $orderContext, Customweb_Payment_Authorization_IPaymentCustomerContext $paymentContext, array $formData){}

	protected function createTransactionInner(Customweb_Payment_Authorization_ITransactionContext $transactionContext, $failedTransaction){
		$transaction = new Customweb_Saferpay_Authorization_Transaction($transactionContext);
		$transaction->setAuthorizationMethod($this->getAuthorizationMethodName());
		$transaction->setLiveTransaction(!$this->getConfiguration()->isTestMode());
		$transaction->setUpdateExecutionDate(Customweb_Core_DateTime::_()->addMinutes(90));
		$paymentMethod = $this->getContainer()->getPaymentMethod($transactionContext->getOrderContext()->getPaymentMethod(), $this->getAuthorizationMethodName());
		$paymentInformationProvider = $paymentMethod->getPaymentInformationProvider();
		if ($paymentInformationProvider !== null) {
			$transaction->registerPaymentInformationProvider($paymentInformationProvider);
		}		
		return $transaction;
	}
		
}