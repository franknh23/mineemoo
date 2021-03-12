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
//require_once 'Customweb/Payment/BackendOperation/Adapter/Service/ICancel.php';
//require_once 'Customweb/Saferpay/JsonAdapter.php';
//require_once 'Customweb/Saferpay/ParameterBuilder/BackendOperation/Cancel.php';
//require_once 'Customweb/Saferpay/IConstants.php';


/**
 * 
 * @author Thomas Hunziker
 * @Bean
 *
 */
class Customweb_Saferpay_BackendOperation_Adapter_CancelAdapter extends Customweb_Saferpay_JsonAdapter implements Customweb_Payment_BackendOperation_Adapter_Service_ICancel {


	public function cancel(Customweb_Payment_Authorization_ITransaction $transaction) {
		if (!($transaction instanceof Customweb_Saferpay_Authorization_Transaction)) {
			throw new Exception("The given transaction is not instanceof Customweb_Saferpay_Authorization_Transaction.");
		}
		$transaction->cancelDry();
		
		$builder = new Customweb_Saferpay_ParameterBuilder_BackendOperation_Cancel($this->getContainer(), $transaction);
		$url = $this->getConfiguration()->getJsonBaseUrl().Customweb_Saferpay_IConstants::JSON_CANCEL;
		$this->sendRequest($url, $builder->buildParameters());
	
		$transaction->cancel();
	}
			
}