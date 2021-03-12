<?php 
/**
  * You are allowed to use this API in your web application.
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
//require_once 'Customweb/Payment/BackendOperation/Adapter/Service/ICapture.php';
//require_once 'Customweb/Saferpay/JsonAdapter.php';
//require_once 'Customweb/Saferpay/ParameterBuilder/BackendOperation/Capture.php';
//require_once 'Customweb/Saferpay/IConstants.php';
//require_once 'Customweb/Util/Invoice.php';


/**
 * 
 * @author Thomas Hunziker
 * @Bean
 *
 */
class Customweb_Saferpay_BackendOperation_Adapter_CaptureAdapter extends Customweb_Saferpay_JsonAdapter implements Customweb_Payment_BackendOperation_Adapter_Service_ICapture {

	public function capture(Customweb_Payment_Authorization_ITransaction $transaction){
		if (!($transaction instanceof Customweb_Saferpay_Authorization_Transaction)) {
			throw new Exception("The given transaction is not instanceof Customweb_Saferpay_Authorization_Transaction.");
		}
		$items = $transaction->getUncapturedLineItems();
		$this->partialCapture($transaction, $items, true);
	}
	
	public function partialCapture(Customweb_Payment_Authorization_ITransaction $transaction, $items, $close){
			if (!($transaction instanceof Customweb_Saferpay_Authorization_Transaction)) {
			throw new Exception("The given transaction is not instanceof Customweb_Saferpay_Authorization_Transaction.");
		}
		$amount = Customweb_Util_Invoice::getTotalAmountIncludingTax($items);
		
		// Check the transaction state
		$transaction->partialCaptureByLineItemsDry($items, true);
		
		$builder = new Customweb_Saferpay_ParameterBuilder_BackendOperation_Capture($this->getContainer(), $transaction);
		$url = $this->getConfiguration()->getJsonBaseUrl().Customweb_Saferpay_IConstants::JSON_CAPTURE;
		$response = array();
		try{
			$response = $this->sendRequest($url, $builder->buildParameters($amount));
		}
		catch(Customweb_Saferpay_Exception_RemoteErrorNameException $e){
			if($e->getRemoteErrorName() != Customweb_Saferpay_IConstants::ERROR_NAME_TRANSACTION_ALREADY_CAPTURED){
				throw $e;
			}
			//We do not set the version and the capture id. The refund will then use the transcation id as reference
			$transaction->partialCaptureByLineItems($items, true);
			return;			
		}		
		if(isset($response['Invoice'])){
			$transaction->setCapturePaymentInformation($response['Invoice']);
		}
		
		$captureItem = $transaction->partialCaptureByLineItems($items, true);
		$captureItem->setCaptureId($response['CaptureId']);
		$captureItem->setAPIVersion(Customweb_Saferpay_IConstants::JSON_API_VERSION);
		
	}
		
}