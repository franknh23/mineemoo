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

//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Saferpay/Method/Wallet.php';


/**
 *
 * @author Sebastian Bossert
 * @Method(paymentMethods={'ApplePay'})
 */
class Customweb_Saferpay_Method_ApplePay extends Customweb_Saferpay_Method_Wallet {

	public function preValidate(Customweb_Payment_Authorization_IOrderContext $orderContext, Customweb_Payment_Authorization_IPaymentCustomerContext $paymentContext){
		parent::preValidate($orderContext, $paymentContext);
		$this->validateBrowser($this->getContainer()->getHttpRequest());
	}

	private function validateBrowser(Customweb_Core_Http_IRequest $request){
		$headers = $request->getParsedHeaders();
		foreach ($headers as $key => $value) {
			if (strtolower($key) === 'user-agent') {
				$userAgent = strtolower($value);
				if (strpos($userAgent, 'safari') !== false && strpos($userAgent, 'chrome') === false) {
					return;
				}
			}
		}
		throw new Exception(Customweb_I18n_Translation::__("ApplePay requires the Safari Browser."));
	}
}