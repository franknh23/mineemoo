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

//require_once 'Customweb/Saferpay/Authorization/TransactionCapture.php';
//require_once 'Customweb/Saferpay/ParameterBuilder/BackendOperation/AbstractBase.php';
//require_once 'Customweb/Payment/Util.php';

class Customweb_Saferpay_ParameterBuilder_BackendOperation_Refund extends Customweb_Saferpay_ParameterBuilder_BackendOperation_AbstractBase {

	public function buildParameters($amount){
		return array(
			'RequestHeader' => $this->getRequestHeaderParameters(),
			'Refund' => array(
				'Amount' => $this->getAmountParameters($amount),
				'OrderId' => $this->getTransactionAppliedSchema()
			),
			'CaptureReference' => $this->getTransactionReferenceParamters(),
		);
	}

	public function buildBookingParameters($refundId){
		return array(
			'RequestHeader' => $this->getRequestHeaderParameters(),
			'TransactionReference' => $this->getRefundReferenceParameters($refundId) 
		);
	}
	
	protected function getRefundReferenceParameters($refundId){
		return array(
			'TransactionId' => $refundId
		);
	}
	
	
	protected function getTransactionReferenceParamters(){
		$captures =$this->getTransaction()->getCaptures();
		$capture = current($captures);
		
		if($capture instanceof Customweb_Saferpay_Authorization_TransactionCapture){
			if($capture->getAPIVersion() != null && version_compare('1.11', $capture->getAPIVersion()) <= 0){
				return array(
					'CaptureId' => $capture->getCaptureId()
				);
			}
		}		
		return array(
			'TransactionId' => $this->getTransaction()->getPaymentId()
		);
	}
	
	
	/**
	 *
	 * @return string
	 */
	protected function getTransactionAppliedSchema(){
		$schema = $this->getContainer()->getConfiguration()->getOrderIdSchema();
		$id = $this->getTransaction()->getExternalTransactionId().'-R'.(count($this->getTransaction()->getRefunds())+1);
		
		$paymentMethod = $this->getContainer()->getPaymentMethodByTransaction($this->getTransaction());
		$maxLength = $paymentMethod->getMaxOrderIdLength();
		
		$applied = Customweb_Payment_Util::applyOrderSchemaImproved($schema, $id, $maxLength);
		$cleaned = preg_replace('/[^A-Za-z0-9.:\-_]/', '', $applied);
		return $cleaned;
	}
}