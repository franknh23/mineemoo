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
//require_once 'Customweb/Saferpay/ParameterBuilder/BackendOperation/Refund.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Payment/BackendOperation/Adapter/Service/IRefund.php';
//require_once 'Customweb/Saferpay/JsonAdapter.php';
//require_once 'Customweb/Saferpay/IConstants.php';
//require_once 'Customweb/Util/Invoice.php';



/**
 *
 * @author Thomas Hunziker
 * @Bean
 *
 */
class Customweb_Saferpay_BackendOperation_Adapter_RefundAdapter extends Customweb_Saferpay_JsonAdapter implements 
		Customweb_Payment_BackendOperation_Adapter_Service_IRefund {

	public function refund(Customweb_Payment_Authorization_ITransaction $transaction){
		if (!($transaction instanceof Customweb_Saferpay_Authorization_Transaction)) {
			throw new Exception("The given transaction is not instanceof Customweb_Saferpay_Authorization_Transaction.");
		}
		$items = $transaction->getNonRefundedLineItems();
		return $this->partialRefund($transaction, $items, true);
	}

	public function partialRefund(Customweb_Payment_Authorization_ITransaction $transaction, $items, $close){
		if (!($transaction instanceof Customweb_Saferpay_Authorization_Transaction)) {
			throw new Exception("The given transaction is not instanceof Customweb_Saferpay_Authorization_Transaction.");
		}
		$amount = Customweb_Util_Invoice::getTotalAmountIncludingTax($items);
		
		$transaction->refundByLineItemsDry($items, $close);
		
		$builder = new Customweb_Saferpay_ParameterBuilder_BackendOperation_Refund($this->getContainer(), $transaction);
		$url = $this->getConfiguration()->getJsonBaseUrl() . Customweb_Saferpay_IConstants::JSON_REFUND_REFERENCED;
		
		$responseArray = $this->sendRequest($url, $builder->buildParameters($amount));
		if ($responseArray['Transaction']['Type'] != 'REFUND') {
			throw new Exception(
					Customweb_I18n_Translation::__('Received wrong type: !type', 
							array(
								'!state' => $responseArray['Transaction']['Type'] 
							)));
		}
		$refundId = $responseArray['Transaction']['Id'];
		$state = $responseArray['Transaction']['Status'];
		switch ($state) {
			case 'AUTHORIZED':
				$this->bookRefund($transaction, $amount, $refundId);
				$refundItem = $transaction->refundByLineItems($items, $close);
				$refundItem->setRefundId($refundId);
				break;
			
			case 'CAPTURED':
				$refundItem = $transaction->refundByLineItems($items, $close);
				$refundItem->setRefundId($refundId);
				break;
			default:
				throw new Exception(Customweb_I18n_Translation::__('Received unkown refund state: !state', array(
					'!state' => $state 
				)));
		}
	}

	protected function bookRefund($transaction, $amount, $refundId){
		$builder = new Customweb_Saferpay_ParameterBuilder_BackendOperation_Refund($this->getContainer(), $transaction);
		$url = $this->getConfiguration()->getJsonBaseUrl() . Customweb_Saferpay_IConstants::JSON_CAPTURE;
		$this->sendRequest($url, $builder->buildBookingParameters($refundId, $amount));
	}
}