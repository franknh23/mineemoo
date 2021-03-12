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

class Customweb_Saferpay_ParameterBuilder_Authorization_Alias extends Customweb_Saferpay_ParameterBuilder_Authorization_AbstractBase {

	public function buildParameters(){
		$paymentMethod = $this->getContainer()->getPaymentMethodByTransaction($this->getTransaction());
		$parameters = array(
			'RequestHeader' => $this->getRequestHeaderParameters(),
			'TerminalId' => $this->getTerminalId(),
			'Payment' => $this->getPaymentParameters(),
			'Payer' => $this->getPayerParameters() 
		);
		
			
		$aliasId = $this->getTransaction()->getTransactionContext()->getAlias()->getAliasId();
		if (empty($aliasId)) {
			$aliasId = $this->getTransaction()->getTransactionContext()->getAlias()->getCardRefId();
		}
		//We set the used aliasid on the transaction so it is available for the transaction labels
		$this->getTransaction()->setAliasId($aliasId);
		
		$parameters['PaymentMeans'] = array(
			'Alias' => array(
				'Id' => $aliasId 
			) 
		);

		return array_merge_recursive($parameters, $paymentMethod->getPaymentMeanParameter(), 
				$paymentMethod->getAuthorizationParameters($this->getTransaction(), $this->getFormData()));
	}
	
	protected function getTerminalId(){
		return $this->getContainer()->getConfiguration()->getMoToTerminalId();
	}
}