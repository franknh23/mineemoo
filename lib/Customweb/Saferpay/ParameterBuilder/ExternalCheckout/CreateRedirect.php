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
//require_once 'Customweb/Saferpay/Util.php';
//require_once 'Customweb/Payment/Util.php';
//require_once 'Customweb/Util/Invoice.php';

class Customweb_Saferpay_ParameterBuilder_ExternalCheckout_CreateRedirect extends Customweb_Saferpay_ParameterBuilder_AbstractBase {
	private $context = null;
	private $token = null;

	public function __construct(Customweb_DependencyInjection_IContainer $container, Customweb_Payment_ExternalCheckout_IContext $context, $token){
		parent::__construct($container);
		$this->context = $context;
		$this->token = $token;
	}

	/**
	 *
	 * @return Customweb_Payment_ExternalCheckout_IContext
	 */
	protected function getCheckoutContext(){
		return $this->context;
	}
	
	protected function getToken(){
		return $this->token;
	}

	public function buildParameters(){
		$parameters = array(
			'RequestHeader' => $this->getRequestHeaderParameters(),
			'TerminalId' => $this->getTerminalId(),
			'Payment' => $this->getPaymentParameters(),
			'Payer' => $this->getPayerParameters(),
			'ReturnUrls' => $this->getReturnUrlParameters(),
			'Styling' => $this->getStylingParameters(),
			'Wallet' => $this->getWalletParameters(),
		);
		
		return $parameters;
	}

	protected function getPaymentParameters(){
		
		$description = $this->getContainer()->getConfiguration()->getPaymentDescription($this->getCheckoutContext()->getLanguage());
		$id = $this->getCheckoutContext()->getContextId();
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
			'OrderId' => preg_replace('/[^A-Za-z0-9.:\-_]/', '', Customweb_Payment_Util::applyOrderSchemaImproved($this->getContainer()->getConfiguration()->getOrderIdSchema(), $id, 80)),
			'Description' => $description 
		);
		
		return $parameters;
	}
	
	protected function getAmountParameters(){
		$currency = $this->getCheckoutContext()->getCurrencyCode();
		$amount = Customweb_Util_Invoice::getTotalAmountIncludingTax($this->getCheckoutContext()->getInvoiceItems());
		return array(
			'Value' => Customweb_Util_Currency::formatAmount($amount, $currency, '', ''),
			'CurrencyCode' => $currency
		);
	}
	
	protected function getPayerParameters(){
		$parameters = array(
			'LanguageCode' => Customweb_Saferpay_Util::getCleanLanguageCode($this->getCheckoutContext()->getLanguage()->getIetfCode()) 
		);
		return $parameters;
	}
	
	protected function getWalletParameters(){
		$parameters = array(
			'Type' => 'MASTERPASS',
			'RequestDeliveryAddress' => true,
			'EnableAmountAdjustment' => true			
		);		
		return $parameters;
	}

	protected function getReturnUrlParameters(){
		return array(
			'Success' => $this->createUrl('masterpass', 'update-context'),
			'Fail' => $this->createUrl('masterpass', 'fail'),
			'Abort' => $this->createUrl('masterpass', 'cancel'),
		);
	}
	
	protected function createUrl($controller, $action){
		return $this->getContainer()->getEndpointAdapter()->getUrl($controller, $action, 
				array(
					'context-id' => $this->getCheckoutContext()->getContextId(),
					'token' => $this->getToken() 
				));
	}
}