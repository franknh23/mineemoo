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


/**
 * 
 * @Bean
 */
class Customweb_Saferpay_Configuration {
	
	/**
	 *      	    	 			    	 
	 * @var Customweb_Payment_IConfigurationAdapter
	 */
	private $configurationAdapter = null;

	public function __construct(Customweb_Payment_IConfigurationAdapter $configurationAdapter) {
		$this->configurationAdapter = $configurationAdapter;
	}


	/**
	 * Returns whether the gateway is in test mode or in live mode.
	 *      	    	 			    	 
	 * @return boolean True if the system is in test mode. Else return false.
	 */
	public function isTestMode()
	{
		// We check against the 'live' value, because in some system the configuration
		// page is not stored and hence it contains nothing.
		// If the account id is set, the operation mode will be also set!
		return $this->getConfigurationValue('operation_mode') != 'live';
	}

	public function getOrderIdSchema(){
		return $this->getConfigurationValue('order_id_schema');
	}

	public function getPaymentPageConfiguration(){
		return $this->getConfigurationValue('payment_page_configuration_name');
	}
	
	public function getPaymentPageTheme(){
		return $this->getConfigurationValue('payment_page_theme');
	}
	
	public function getCssUrl() {
		return trim($this->getConfigurationValue('css_url'));
	}


	public function getCustomerId(){
		$customerId = '';
		if($this->isTestMode()){
			$customerId = $this->getConfigurationValue('customer_id_test');
		}
		else{
			$customerId = $this->getConfigurationValue('customer_id');
		}
		$customerId = trim($customerId);
		if(empty($customerId)){
			throw new Exception(Customweb_I18n_Translation::__("The given customer Id is empty. Please check the Saferpay settings."));
		}
		return $customerId;
	}
	

	public function getTerminalId(){
		$terminalId = '';
		if($this->isTestMode()){
			$terminalId = $this->getConfigurationValue('terminal_id_test');
		}
		else{
			$terminalId = $this->getConfigurationValue('terminal_id');
		}
		$terminalId = trim($terminalId);
		if(empty($terminalId)){
			throw new Exception(Customweb_I18n_Translation::__("The given terminal Id is empty. Please check the Saferpay settings."));
		}
		return $terminalId;
	}
	
	public function getMoToTerminalId(){
		$terminalId = '';
		if($this->isTestMode()){
			$terminalId = $this->getConfigurationValue('moto_terminal_id_test');
		}
		else{
			$terminalId = $this->getConfigurationValue('moto_terminal_id');
		}
		$terminalId = trim($terminalId);
		if(empty($terminalId)){
			throw new Exception(Customweb_I18n_Translation::__("The given MoTo terminal Id is empty. Please check the Saferpay settings."));
		}
		return $terminalId;
	}
	
	public function getJsonUsername(){
		$username = '';
		if($this->isTestMode()) {
			$username = $this->getConfigurationValue('json_username_test');
		}
		else {
			$username = $this->getConfigurationValue('json_username');
		}
		$username = trim($username);
		if(empty($username)){
			throw new Exception(Customweb_I18n_Translation::__("The given JSON username is empty. Please check the Saferpay settings."));
		}
		return $username;
	}
	
	public function getJsonPassword(){
		$password = '';
		if($this->isTestMode()){
			$password = $this->getConfigurationValue('json_password_test');
		}
		else{
			$password = $this->getConfigurationValue('json_password');
		}
		$password = trim($password);
		if(empty($password)){
			throw new Exception(Customweb_I18n_Translation::__("The given JSON password is empty. Please check the Saferpay settings."));
		}
		return $password;
	}

	
	/**
	 * Should transaction be marked as uncertain, which has no liability shift?
	 *
	 * @return boolean
	 */
	public function isMarkLiabilityShiftTransactions(){
		return strtolower($this->getConfigurationValue('liability_shift')) == 'uncertain';
	}

	public function getPaymentDescription($language){
		return $this->getConfigurationValue('description', $language);
	}
	
	public function getMerchantEmail(){
		return trim($this->getConfigurationValue('merchant_email'));
	}

	protected function getConfigurationValue($key, $language = null) {
		return $this->configurationAdapter->getConfigurationValue($key, $language);
	}

	/**
	 *
	 * @return Customweb_Payment_IConfigurationAdapter
	 */
	public function getConfigurationAdapter() {
		return $this->configurationAdapter;
	}
	
	public function getJsonBaseUrl(){
		if($this->isTestMode()){
			return 'https://test.saferpay.com/api';
		}
		return 'https://www.saferpay.com/api';
	}
}
