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

//require_once 'Customweb/Saferpay/Container.php';
//require_once 'Customweb/Payment/Authorization/Widget/IAdapter.php';
//require_once 'Customweb/Payment/Update/IAdapter.php';
//require_once 'Customweb/Payment/Authorization/Server/IAdapter.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Payment/Authorization/PaymentPage/IAdapter.php';
//require_once 'Customweb/Payment/Update/Util.php';
//require_once 'Customweb/Payment/ExternalCheckout/IProviderService.php';
//require_once 'Customweb/Payment/Authorization/Moto/IAdapter.php';
//require_once 'Customweb/Payment/Authorization/Recurring/IAdapter.php';


/**
 * 
 * @author Nico Eigenmann
 * @Bean
 *
 */
class Customweb_Saferpay_Update_Adapter implements Customweb_Payment_Update_IAdapter
{
	const TRANSACTION_TIMEOUT = 7200;
	const TRANSACTION_UPDATE_INTERVAL = 600;
	
	/**
	 * @var Customweb_Saferpay_Container
	 */
	private $container = null;
	
	public function __construct(Customweb_DependencyInjection_IContainer $container){
		if (!($container instanceof Customweb_Saferpay_Container)) {
			$container = new Customweb_Saferpay_Container($container);
		}
		$this->container = $container;
	}
	
	/**
	 * 
	 * @return Customweb_Saferpay_Container
	 */
	protected function getContainer(){
		return $this->container;
	}
	
	/**
	 * @return Customweb_Payment_Authorization_IAdapterFactory
	 */
	protected function getAdapterFactory(){
		return $this->getContainer()->getBean('Customweb_Payment_Authorization_IAdapterFactory');
	}
	
	public function updateTransaction(Customweb_Payment_Authorization_ITransaction $transaction) {
		/**
		 * @var Customweb_Saferpay_Authorization_Transaction $transaction
		 */	
		if ($transaction->isAuthorized() || $transaction->isAuthorizationFailed()) {
			return;
		}
		$adapter = $this->getAdapterFactory()->getAuthorizationAdapterByName($transaction->getAuthorizationMethod());
		switch($transaction->getAuthorizationMethod()){
			case Customweb_Payment_Authorization_PaymentPage_IAdapter::AUTHORIZATION_METHOD_NAME:
			case Customweb_Payment_Authorization_Widget_IAdapter::AUTHORIZATION_METHOD_NAME:
			case Customweb_Payment_Authorization_Moto_IAdapter::AUTHORIZATION_METHOD_NAME:
				$adapter->getResultAndUpdate($transaction);
				break;
			case Customweb_Payment_Authorization_Server_IAdapter::AUTHORIZATION_METHOD_NAME:
				$paymentMethod = $this->getContainer()->getPaymentMethodByTransaction($transaction);
				if($paymentMethod->getServerFlow() == 'redirect'){
					$adapter->getResultAndUpdate($transaction);
				}
				else{
					$transaction->setAuthorizationFailed(Customweb_I18n_Translation::__("The payment failed."));					
				}
				break;
			case  Customweb_Payment_Authorization_Recurring_IAdapter::AUTHORIZATION_METHOD_NAME:
			case  Customweb_Payment_ExternalCheckout_IProviderService::AUTHORIZATION_METHOD_NAME:
				$transaction->setAuthorizationFailed(Customweb_I18n_Translation::__("The payment failed."));
				break;			
		}
		if(!$transaction->isAuthorizationFailed() && !$transaction->isAuthorized()) {
			Customweb_Payment_Update_Util::handlePendingTransaction($transaction, self::TRANSACTION_TIMEOUT, self::TRANSACTION_UPDATE_INTERVAL);
		}
	}
}