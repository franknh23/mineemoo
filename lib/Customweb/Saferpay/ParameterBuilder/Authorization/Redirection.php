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
//require_once 'Customweb/Saferpay/IConstants.php';

class Customweb_Saferpay_ParameterBuilder_Authorization_Redirection extends Customweb_Saferpay_ParameterBuilder_Authorization_AbstractBase {

	public function buildParameters(){
		$paymentMethod = $this->getContainer()->getPaymentMethodByTransaction($this->getTransaction());
		$parameters = array(
			'RequestHeader' => $this->getRequestHeaderParameters(),
			'TerminalId' => $this->getTerminalId(),
			'Payment' => $this->getPaymentParameters(),
			'Payer' => $this->getPayerParameters(),
			'ReturnUrls' => $this->getReturnUrlParameters(),
			'Notification' => $this->getNotificationParameters(),
			'Styling' => $this->getStylingParameters() 
		);
		
		if ($this->getTransaction()->getTransactionContext()->getAlias() == 'new') {
			$parameters['RegisterAlias'] = array(
				'IdGenerator' => 'RANDOM_UNIQUE',
				'Lifetime' => 1600 
			);
		}
		$ppConfigurationName = $this->getContainer()->getConfiguration()->getPaymentPageConfiguration();
		$ppConfigurationName = preg_replace('/[^\w\d:\.\-]/', '', trim($ppConfigurationName));
		if(!empty($ppConfigurationName) && strlen($ppConfigurationName) <= 20){
			$parameters['ConfigSet'] = $ppConfigurationName;
		}
		
		return array_merge_recursive($parameters, $paymentMethod->getPaymentMeanParameter(), 
				$paymentMethod->getAuthorizationParameters($this->getTransaction(), $this->getFormData()));
	}

	protected function getNotificationParameters(){
		$parameters = array(
			'NotifyUrl' => $this->getSecuredUrl('process', 'json') 
		);
		
		$merchantEmail = $this->getContainer()->getConfiguration()->getMerchantEmail();
		if(!empty($merchantEmail)){
			$parameters['MerchantEmails'] = array($merchantEmail);
		}
		
		$paymentMethod = $this->getContainer()->getPaymentMethodByTransaction($this->getTransaction());
		if($paymentMethod->isCustomerConfirmationEmailSentByPSP()){
			$parameters['PayerEmail'] = $this->getOrderContext()->getCustomerEMailAddress();
		}
		
		return $parameters;
	}

	protected function getSuccessUrl(){
		return $this->getSecuredUrl('process', 'success');
	}

	protected function getFailureUrl(){
		return $this->getSecuredUrl('process', 'fail');
	}
	
	protected function getCancelUrl(){
		return $this->getSecuredUrl('process', 'fail', array('cancelled' => 'true'));
	}

	protected function getReturnUrlParameters(){
		return array(
			'Success' => $this->getSuccessUrl(),
			'Fail' => $this->getFailureUrl(),
			'Abort' => $this->getCancelUrl() 
		);
	}

	protected function getSecuredUrl($controller, $action, array $parameters = array()){
		$signature = $this->getTransaction()->getSecuritySignature($controller . '/' . $action);
		return $this->getContainer()->getEndpointAdapter()->getUrl($controller, $action, 
				array_merge($parameters, 
						array(
							'cw_transaction_id' => $this->getTransaction()->getExternalTransactionId(),
							Customweb_Saferpay_IConstants::PARAMETER_SIGNATURE => $signature 
						)));
	}
}