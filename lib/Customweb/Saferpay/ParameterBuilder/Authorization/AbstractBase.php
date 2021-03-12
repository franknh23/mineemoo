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

//require_once 'Customweb/Saferpay/ParameterBuilder/AbstractBase.php';
//require_once 'Customweb/Util/Currency.php';
//require_once 'Customweb/Filter/Input/String.php';
//require_once 'Customweb/Saferpay/IConstants.php';
//require_once 'Customweb/Saferpay/Util.php';
//require_once 'Customweb/Payment/Util.php';

abstract class Customweb_Saferpay_ParameterBuilder_Authorization_AbstractBase extends Customweb_Saferpay_ParameterBuilder_AbstractBase {
	private $transaction = null;
	private $formData = array();

	abstract public function buildParameters();

	public function __construct(Customweb_DependencyInjection_IContainer $container, Customweb_Saferpay_Authorization_Transaction $transaction, array $formData){
		parent::__construct($container);
		$this->transaction = $transaction;
		$this->formData = $formData;
	}

	protected function getFormData(){
		return $this->formData;
	}

	/**
	 *
	 * @return Customweb_Saferpay_Authorization_Transaction
	 */
	protected function getTransaction(){
		return $this->transaction;
	}

	/**
	 *
	 * @return Customweb_Payment_Authorization_IOrderContext
	 */
	protected function getOrderContext(){
		return $this->getTransaction()->getTransactionContext()->getOrderContext();
	}

	protected function getPaymentParameters(){
		$description = $this->getContainer()->getConfiguration()->getPaymentDescription($this->getOrderContext()->getLanguage());
		$id = $this->getTransaction()->getExternalTransactionId();
		if (stristr($description, '{id}') !== false) {
			$description = str_ireplace('{id}', $id, $description);
		}
		$description = trim($description);
		if (empty($description)) {
			$description = $id;
		}
		$description = Customweb_Filter_Input_String::_($description, 1000)->filter();
		
		$parameters = array(
			'Amount' => $this->getAmountParameters(),
			'OrderId' => $this->getTransactionAppliedSchema(),
			'Description' => $description 
		);
		
		if ($this->getTransaction()->getTransactionContext()->createRecurringAlias()) {
			$parameters['Recurring'] = array(
				'Initial' => true 
			);
		}
		
		return $parameters;
	}

	protected function getAmountParameters(){
		$currency = $this->getTransaction()->getCurrencyCode();
		$amount = $this->getTransaction()->getAuthorizationAmount();
		return array(
			'Value' => Customweb_Util_Currency::formatAmount($amount, $currency, '', ''),
			'CurrencyCode' => $currency 
		);
	}

	protected function getPayerParameters(){
		$parameters = array(
			'LanguageCode' => Customweb_Saferpay_Util::getCleanLanguageCode($this->getOrderContext()->getLanguage()->getIetfCode()) 
		);
		$paymentMethod = $this->getContainer()->getPaymentMethodByTransaction($this->getTransaction());
		$mode = $paymentMethod->getAddressBehavior();
		if ($mode == Customweb_Saferpay_IConstants::SEND_ADDRESS_MODE_BOTH ||
				 $mode == Customweb_Saferpay_IConstants::SEND_ADDRESS_MODE_BILLING) {
			$billingAddress = $this->getAddressParameters($this->getOrderContext()->getBillingAddress());
			if (!empty($billingAddress)) {
				$parameters['BillingAddress'] = $billingAddress;
			}
		}
		if ($mode == Customweb_Saferpay_IConstants::SEND_ADDRESS_MODE_BOTH ||
				 $mode == Customweb_Saferpay_IConstants::SEND_ADDRESS_MODE_DELIVERY) {
			$shippingAddress = $this->getAddressParameters($this->getOrderContext()->getShippingAddress());
			if (!empty($shippingAddress)) {
				$parameters['DeliveryAddress'] = $shippingAddress;
			}
		}
		
		return $parameters;
	}

	/**
	 *
	 * @return string
	 */
	protected function getTransactionAppliedSchema(){
		$schema = $this->getContainer()->getConfiguration()->getOrderIdSchema();
		$id = $this->getTransaction()->getExternalTransactionId();
		
		$paymentMethod = $this->getContainer()->getPaymentMethodByTransaction($this->getTransaction());
		$maxLength = $paymentMethod->getMaxOrderIdLength();
		
		$applied = Customweb_Payment_Util::applyOrderSchemaImproved($schema, $id, $maxLength);
		$cleaned = preg_replace('/[^A-Za-z0-9.:\-_]/', '', $applied);
		return $cleaned;
	}
}