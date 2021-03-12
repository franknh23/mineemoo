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

//require_once 'Customweb/Saferpay/ParameterBuilder/Authorization/AbstractBase.php';

class Customweb_Saferpay_ParameterBuilder_Authorization_RecurringLegacy extends Customweb_Saferpay_ParameterBuilder_Authorization_AbstractBase {

	public function buildParameters(){
		$parameters = array(
			'RequestHeader' => $this->getRequestHeaderParameters(),
			'TerminalId' => $this->getTerminalId(),
			'Payment' => $this->getPaymentParameters(),
			'PaymentMeans' => $this->getPaymentMeansParameters() 
		);
		
		return $parameters;
	}

	protected function getTerminalId(){
		return $this->getContainer()->getConfiguration()->getMoToTerminalId();
	}

	public function getPaymentMeansParameters(){
		$initialTransaction = $this->getTransaction()->getTransactionContext()->getInitialTransaction();
		$cardRefId = $initialTransaction->getCardRefId();
		
		return array(
			'Alias' => array(
				'Id' => $cardRefId 
			) 
		);
	}
}