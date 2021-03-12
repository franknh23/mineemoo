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

//require_once 'Customweb/Saferpay/Authorization/AbstractAdapter.php';
//require_once 'Customweb/Saferpay/ParameterBuilder/Authorization/Confirmation.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Saferpay/IConstants.php';
//require_once 'Customweb/Saferpay/ParameterBuilder/Authorization/AliasInitialization.php';


/**
 * @Bean
 * @author Sebastian Bossert
 *
 */
class Customweb_Saferpay_Authorization_AliasAdapter extends Customweb_Saferpay_Authorization_AbstractAdapter {

	public function generateRedirectUrl(Customweb_Saferpay_Authorization_Transaction $transaction, array $formData){
		$builder = new Customweb_Saferpay_ParameterBuilder_Authorization_AliasInitialization($this->getContainer(), $transaction, $formData);
		$parameters = $builder->buildParameters();
		$url = $this->getConfiguration()->getJsonBaseUrl() . Customweb_Saferpay_IConstants::JSON_INITIALIZATION;
		$result = $this->sendRequest($url, $parameters);
		if ((!isset($result['RedirectRequired']) && !isset($result['Redirect'])) || !isset($result['Token'])) {
			throw new Exception("No redirectRequired, redirect url or token received.");
		}
		$transaction->setInitToken($result['Token']);
		if ($result['RedirectRequired']) {
			if ($result['Redirect']['PaymentMeansRequired']) {
				$transaction->setRedirectUrl($transaction->getFailedUrl());
				$transaction->setAuthorizationFailed(
						Customweb_I18n_Translation::__("Payment Means are required, but not supported. Please try another payment method."));
			}
			else {
				$transaction->setRedirectUrl($result['Redirect']['RedirectUrl']);
			}
		}
		else {
			$this->confirm($transaction, $formData);
			$transaction->setRedirectUrl($this->getResultUrl($transaction));
		}
		return $transaction->getRedirectUrl();
	}

	private function getResultUrl(Customweb_Saferpay_Authorization_Transaction $transaction){
		if ($transaction->isAuthorized()) {
			return $transaction->getSuccessUrl();
		}
		return $transaction->getFailedUrl();
	}

	public function confirm(Customweb_Saferpay_Authorization_Transaction $transaction, array $formData){
		$builder = new Customweb_Saferpay_ParameterBuilder_Authorization_Confirmation($this->getContainer(), $transaction, $formData);
		$parameters = $builder->buildParameters();
		$url = $this->getConfiguration()->getJsonBaseUrl() . Customweb_Saferpay_IConstants::JSON_AUTHORIZE;
		$result = $this->sendRequest($url, $parameters);
		$this->checkPaymentResult($transaction, $result);
	}

	public function getAdapterPriority(){
		return -100;
	}

	public function getAuthorizationMethodName(){
		return 'alias';
	}
}