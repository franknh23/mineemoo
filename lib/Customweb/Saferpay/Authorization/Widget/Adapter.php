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

//require_once 'Customweb/Payment/Authorization/Widget/IAdapter.php';
//require_once 'Customweb/Saferpay/ParameterBuilder/Authorization/Redirection.php';
//require_once 'Customweb/Saferpay/Authorization/AbstractRedirectionAdapter.php';



/**
 *
 * @author Nico Eigenmann
 * @Bean
 *
 */
class Customweb_Saferpay_Authorization_Widget_Adapter extends Customweb_Saferpay_Authorization_AbstractRedirectionAdapter implements 
		Customweb_Payment_Authorization_Widget_IAdapter {

	public function getAuthorizationMethodName(){
		return self::AUTHORIZATION_METHOD_NAME;
	}

	public function getAdapterPriority(){
		return 100;
	}
	
	public function createTransaction(Customweb_Payment_Authorization_Widget_ITransactionContext $transactionContext, $failedTransaction){
		return $this->createTransactionInner($transactionContext, $failedTransaction);
	}
	
	
	public function getWidgetHTML(Customweb_Payment_Authorization_ITransaction $transaction, array $formData){
		$url = $this->generateRedirectUrl($transaction, $formData);
		if($transaction->isAuthorizationFailed() || $transaction->isAuthorized()){
			return '<script type="text/javascript">
						window.location.replace("'.$url.'");
					</script>';
		}
		$html = '<iframe src="'.$url.'"  style="height:800px; width:100%;" id="saferpaycw-iframe"  onload="document.getElementById(\'saferpaycw-iframe\').scrollIntoView(true)"></iframe>';
		
		$script= '<script type="text/javascript">
				function resizeCwIframe(event) {
					switch (event.data.message) {
						case "css":
							var element = document.getElementById("saferpaycw-iframe");
							var height = event.data.height;
							if(height < 50) {
								height = 650;
							}
							else{
								height = height + 40;
							}
							element.style.height = height + "px";
							
					}
				}
				window.addEventListener("message", resizeCwIframe, false);
			</script>';
		return $html.$script;
	}
	
	protected function getParameterBuilder($transaction, $formData){
		return new Customweb_Saferpay_ParameterBuilder_Authorization_Redirection($this->getContainer(), $transaction, $formData);
	}
}