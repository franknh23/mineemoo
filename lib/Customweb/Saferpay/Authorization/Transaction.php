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
//require_once 'Customweb/Core/Url.php';
//require_once 'Customweb/Saferpay/Authorization/TransactionCapture.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Payment/Authorization/Moto/IAdapter.php';
//require_once 'Customweb/Saferpay/Util.php';
//require_once 'Customweb/Payment/Authorization/DefaultTransaction.php';

class Customweb_Saferpay_Authorization_Transaction extends Customweb_Payment_Authorization_DefaultTransaction {
	private $ownerName;
	private $cardExpiryDate;
	private $cardExpiryMonth;
	private $cardExpiryYear;
	private $nextRedirectUrl;
	private $truncatedPAN;
	private $cardRefId = null;
	private $key = null;
	private $motoAuthorizationMethodName = null;
	private $effectivePaymentMethodMachineName = null;
	private $paymentInformation;
	private $initToken = null;
	private $jsonAuthorizationParameters = array();
	private $recurringFlagUsed = false;
	private $aliasId = null;
	private $capturePaymentInformation = array();
	/**
	 *
	 * @var Customweb_Saferpay_PaymentInformation_IProvider[]
	 */
	private $paymentInformationProviders = array();

	public function __construct(Customweb_Payment_Authorization_ITransactionContext $transactionContext){
		parent::__construct($transactionContext);
		$this->recurringFlagUsed = true;
	}

	public function getOwnerName(){
		return $this->ownerName;
	}

	public function getCardExpiryDate(){
		return $this->cardExpiryDate;
	}

	public function getCardExpiryMonth(){
		return $this->cardExpiryMonth;
	}

	public function getCardExpiryYear(){
		return $this->cardExpiryYear;
	}

	public function isCaptureClosable(){
		// We support only one capture per transaction, hence the first capture 
		// closes the transaction.       	    	 			    	 
		return false;
	}

	public function getAuthorizationParameters(){
		$parameters = parent::getAuthorizationParameters();
		if (!is_array($parameters)) {
			return array();
		}
		return $parameters;
	}

	public function getTransactionSpecificLabels(){
		$labels = array_merge($this->getLegacyLabels(), $this->getJsonApiLabels());
		$labels['authorization_method'] = array(
			'label' => Customweb_I18n_Translation::__('Authorization Method'),
			'value' => $this->getAuthorizationMethod() 
		);
		
		if ($this->isMoto()) {
			$labels['moto'] = array(
				'label' => Customweb_I18n_Translation::__('Mail Order / Telephone Order (MoTo)'),
				'value' => Customweb_I18n_Translation::__('Yes') 
			);
		}
		
		return $labels;
	}

	public function getFailedUrl(){
		$baseUrl = "";
		if ($this->isMoto()) {
			$baseUrl = $this->getTransactionContext()->getBackendFailedUrl();
		}
		else {
			$baseUrl = $this->getTransactionContext()->getFailedUrl();
		}
		return Customweb_Core_Url::_($baseUrl)->appendQueryParameters($this->getTransactionContext()->getCustomParameters())->toString();
	}

	public function getSuccessUrl(){
		$baseUrl = "";
		if ($this->isMoto()) {
			$baseUrl = $this->getTransactionContext()->getBackendSuccessUrl();
		}
		else {
			$baseUrl = $this->getTransactionContext()->getSuccessUrl();
		}
		return Customweb_Core_Url::_($baseUrl)->appendQueryParameters($this->getTransactionContext()->getCustomParameters())->toString();
	}

	/**
	 * This methods saves the redirecrect url for performance reasons
	 * so we don't have tho call the Saferpay service
	 * multiple times.
	 */
	public function setRedirectUrl($redirectUrl){
		// We store the URL only in a temporary storage, we do not need to persist it.
		if (!isset($GLOBALS['saferpay_redirection_urls'])) {
			$GLOBALS['saferpay_redirection_urls'] = array();
		}
		
		$GLOBALS['saferpay_redirection_urls'][$this->getExternalTransactionId()] = $redirectUrl;
		
		return $this;
	}

	public function getRedirectUrl(){
		if (isset($GLOBALS['saferpay_redirection_urls'][$this->getExternalTransactionId()])) {
			return $GLOBALS['saferpay_redirection_urls'][$this->getExternalTransactionId()];
		}
		else {
			return null;
		}
	}

	public function isMoto(){
		return $this->getAuthorizationMethod() == Customweb_Payment_Authorization_Moto_IAdapter::AUTHORIZATION_METHOD_NAME;
	}

	public function getCardRefId(){
		return $this->cardRefId;
	}

	public function setCardRefId($cardRefId){
		$this->cardRefId = $cardRefId;
	}

	public function getTransactionData(){
		$parameters = array_merge($this->getAuthorizationParameters(), $this->getJsonAuthorizationParameters(), $this->getLegacyFromJsonParameters());
		$cardRefId = $this->getCardRefId();
		if (!empty($cardRefId)) {
			$parameters['cardRefId'] = $cardRefId;
			$parameters['CARDREFID'] = $cardRefId;
		}
		return $parameters;
	}
	
	
	
	private function getLegacyFromJsonParameters(){
		$legacy = array();
		$json = $this->getJsonAuthorizationParameters();
		
		if(isset($json['Transaction'])){
			$transaction  = $json['Transaction'];
			if(isset($transaction['Id'])){
				$legacy['ID'] = $transaction['Id'];
			}			
			if(isset($transaction['Amount']['Value'])){
				$legacy['AMOUNT'] = $transaction['Amount']['Value'];
			}
			if(isset($transaction['Amount']['CurrencyCode'])){
				$legacy['CURRENCY'] = $transaction['Amount']['CurrencyCode'];
			}
			if(isset($transaction['OrderId'])){
				$legacy['ORDERID'] = $transaction['OrderId'];
			}
			if(isset($transaction['AcquirerReference'])){
				$legacy['PROVIDERID'] = $transaction['AcquirerReference'];
			}
			if(isset($transaction['AcquirerName'])){
				$legacy['PROVIDERNAME'] = $transaction['AcquirerName'];
			}						
			if(isset($transaction['DirectDebit']['MandateId'])){
				$legacy['MANDATEID'] =$transaction['DirectDebit']['MandateId'];
			}
		}
		if(isset($json['Payer'])){
			$payer = $json['Payer'];
			if(isset($payer['IpAddress'])){
				$legacy['IP'] = $payer['IpAddress'];
			}
			if(isset($payer['IpLocation'])){
				$legacy['IPCOUNTRY'] = $payer['IpLocation'];
			}
		}
		if(isset($json['PaymentMeans'])){
			$paymentMeans  = $json['PaymentMeans'];
			if(isset($paymentMeans['Brand']['PaymentMethod'])){
				$legacy['CARDBRAND'] = $paymentMeans['Brand']['PaymentMethod'];
			}
			if(isset($paymentMeans['Card'])){
				$card = $paymentMeans['Card'];
				if(isset($card['MaskedNumber'])){
					$legacy['CARDMASK'] = $card['MaskedNumber'];
					$legacy['PAN'] = $card['MaskedNumber'];
				}
				if(isset($card['CountryCode'])){
					$legacy['CCCOUNTRY'] = $card['CountryCode'];
				}
				if(isset($card['ExpMonth'])){
					$legacy['EXPIRYMONTH'] = $card['ExpMonth'];
				}
				if(isset($card['ExpYear'])){
					$legacy['EXPIRYYEAR'] = substr($card['ExpYear'], -2);
				}
			}
			if(isset($paymentMeans['BankAccount'])){
				$bankAccount = $paymentMeans['BankAccount'];
				if(isset($bankAccount['IBAN'])){
					$legacy['IBAN'] = $bankAccount['IBAN'];
				}
				if(isset($bankAccount['CountryCode'])){
					$legacy['CCCOUNTRY'] = $bankAccount['CountryCode'];
				}
			}
		
		}
		if(isset($json['RegistrationResult']['Alias']['Lifetime'])){
			$legacy['LIFETIME'] = $json['RegistrationResult']['Alias']['Lifetime'];
		}
		if(isset($json['ThreeDs'])){
			$threeds = $json['ThreeDs'];
			if(isset($threeds['LiabilityShift'])){
				if($threeds['LiabilityShift'] == '1'){
					$legacy['MPI_LIABILITYSHIFT'] = 'yes';
				}
				else{
					$legacy['MPI_LIABILITYSHIFT'] = 'no';
				}
			}
			if(isset($threeds['Xid'])){
				$legacy['MPI_XID'] = $threeds['Xid'];
				$legacy['XID'] = $threeds['Xid'];
			}
			if(isset($threeds['VerificationValue'])){
				$legacy['MPI_TX_CAVV'] = $threeds['VerificationValue'];
				$legacy['CAVV'] = $threeds['VerificationValue'];
			}
		}
		if(isset($json['Liability'])){
			$liability = $json['Liability'];
			if(isset($liability['ThreeDs'])){
				$threeds = $liability['ThreeDs'];
				if(isset($threeds['LiabilityShift'])){
					if($threeds['LiabilityShift'] == '1'){
						$legacy['MPI_LIABILITYSHIFT'] = 'yes';
					}
					else{
						$legacy['MPI_LIABILITYSHIFT'] = 'no';
					}
				}				
				if(isset($threeds['Xid'])){
					$legacy['MPI_XID'] = $threeds['Xid'];
					$legacy['XID'] = $threeds['Xid'];
				}
				if(isset($threeds['VerificationValue'])){
					$legacy['MPI_TX_CAVV'] = $threeds['VerificationValue'];
					$legacy['CAVV'] = $threeds['VerificationValue'];
				}
			}
			
		}
		return $legacy;
	}

	public function getEffectivePaymentMethodMachineName(){
		return $this->effectivePaymentMethodMachineName;
	}

	public function getMotoAuthorizationMethodName(){
		return $this->motoAuthorizationMethodName;
	}

	public function getPaymentInformation(){
		$output = '';
		
		$paymentInformationRaw = array();
		
		$jsonParameters = $this->getJsonAuthorizationParameters();
		if (isset($jsonParameters['Transaction']['Invoice'])) {
			$paymentInformationRaw = Customweb_Saferpay_Util::convertPaymentInformationArray($jsonParameters['Transaction']['Invoice'],
					$this->getAuthorizationAmount(), $this->getCurrencyCode());
		}
		if (!empty($this->capturePaymentInformation)) {
			$paymentInformationRaw = Customweb_Saferpay_Util::convertPaymentInformationArray($this->capturePaymentInformation,
					$this->getCapturedAmount(), $this->getCurrencyCode());
		}
		
		//Only for legacy reasons
		if (!empty($this->paymentInformation)) {
			$paymentInformationRaw = $this->paymentInformation;
		}
		
		if (empty($this->paymentInformationProviders)) {
			$output = $this->formatPaymentInformation($paymentInformationRaw);
		}
		else {
			if (!empty($paymentInformationRaw)) {
				foreach ($this->paymentInformationProviders as $provider) {
					$output .= $provider->getPaymentInformation($paymentInformationRaw);
				}
			}
		}
		if (empty($output)) {
			return null;
		}
		return $output;
	}

	private function formatPaymentInformation($information){
		if (!is_array($information)) {
			return '';
		}
		$formatted = '';
		foreach ($information as $entry) {
			$formatted .= '<b>' . $entry['label'] . ':</b> ' . $entry['value'] . '<br/>';
		}
		return $formatted;
	}

	public function setInitToken($token){
		$this->initToken = $token;
		return $this;
	}

	public function getInitToken(){
		return $this->initToken;
	}

	public function setJsonAuthorizationParameters(array $parameters){
		$this->jsonAuthorizationParameters = $parameters;
		return $this;
	}

	public function getJsonAuthorizationParameters(){
		return $this->jsonAuthorizationParameters;
	}

	public function isRecurringFlagUsed(){
		return $this->recurringFlagUsed;
	}

	public function isUseExistingAlias(){
		$alias = $this->getTransactionContext()->getAlias();
		return $alias !== null && $alias != 'new';
	}

	public function setAliasId($aliasId){
		$this->aliasId = $aliasId;
		return $this;
	}

	public function getAliasId(){
		return $this->aliasId;
	}

	public function setCapturePaymentInformation(array $information){
		$this->capturePaymentInformation = $information;
		return $this;
	}

	public function registerPaymentInformationProvider(Customweb_Saferpay_PaymentInformation_IProvider $provider){
		$this->paymentInformationProviders[] = $provider;
		return $this;
	}

	private function getLegacyLabels(){
		$labels = array();
		
		$params = $this->getAuthorizationParameters();
		if (isset($params['PAN']) && !isset($params['IBAN'])) {
			$labels['cardnumber'] = array(
				'label' => Customweb_I18n_Translation::__('Card Number'),
				'value' => $params['PAN'] 
			);
		}
		if (isset($params['IBAN'])) {
			$labels['iban'] = array(
				'label' => Customweb_I18n_Translation::__('IBAN'),
				'value' => $params['IBAN'] 
			);
		}
		
		if ($this->cardExpiryMonth !== null) {
			$labels['card_expiry'] = array(
				'label' => Customweb_I18n_Translation::__('Card Expiry Date'),
				'value' => $this->getCardExpiryMonth() . '/' . $this->getCardExpiryYear() 
			);
		}
		
		if (isset($params['PROVIDERNAME'])) {
			$labels['card_type'] = array(
				'label' => Customweb_I18n_Translation::__('Card Type'),
				'value' => $params['PROVIDERNAME'] 
			);
		}
		
		$cardRefId = $this->getCardRefId();
		if (!empty($cardRefId)) {
			$labels['card_ref_id'] = array(
				'label' => Customweb_I18n_Translation::__('Card Reference ID'),
				'value' => $cardRefId 
			);
		}
		
		if ($this->getEffectivePaymentMethodMachineName() !== null) {
			$labels['effective_method'] = array(
				'label' => Customweb_I18n_Translation::__('Effective Payment Method Name'),
				'value' => $this->getEffectivePaymentMethodMachineName(),
				'description' => Customweb_I18n_Translation::__(
						'In some cases the customer switch the payment method or select a more specific one. This label shows the effective one.') 
			);
		}
		return $labels;
	}

	private function getJsonApiLabels(){
		$labels = array();
		$aliasId = $this->getAliasId();
		if (!empty($aliasId)) {
			$labels['aliasId'] = array(
				'label' => Customweb_I18n_Translation::__('Alias ID'),
				'value' => $aliasId 
			);
		}
		
		$jsonParameters = $this->getJsonAuthorizationParameters();
		if (isset($jsonParameters['PaymentMeans']['Brand']['Name'])) {
			$labels['effective_method'] = array(
				'label' => Customweb_I18n_Translation::__('Payment Method Name'),
				'value' => $jsonParameters['PaymentMeans']['Brand']['Name'] 
			);
		}
		if (isset($jsonParameters['PaymentMeans']['Wallet'])) {
			$labels['wallet_used'] = array(
				'label' => Customweb_I18n_Translation::__('Wallet used'),
				'value' => $jsonParameters['PaymentMeans']['Wallet'] 
			);
		}
		if (isset($jsonParameters['PaymentMeans']['DisplayText'])) {
			$labels['formatted_info'] = array(
				'label' => Customweb_I18n_Translation::__('Payment Details'),
				'value' => $jsonParameters['PaymentMeans']['DisplayText'] 
			);
		}
		
		if (isset($jsonParameters['PaymentMeans']['Card']['HolderName'])) {
			$labels['card_holder_name'] = array(
				'label' => Customweb_I18n_Translation::__('Card Holder Name'),
				'value' => $jsonParameters['PaymentMeans']['Card']['HolderName'] 
			);
		}
		
		if (isset($jsonParameters['PaymentMeans']['Card']['ExpYear']) && isset($jsonParameters['PaymentMeans']['Card']['ExpMonth'])) {
			$labels['card_expiry'] = array(
				'label' => Customweb_I18n_Translation::__('Card Expiry Date'),
				'value' => sprintf('%02d / %02d', $jsonParameters['PaymentMeans']['Card']['ExpMonth'],
						substr($jsonParameters['PaymentMeans']['Card']['ExpYear'], 2)) 
			);
		}
		
		if (isset($jsonParameters['PaymentMeans']['Card']['CountryCode'])) {
			$labels['card_country'] = array(
				'label' => Customweb_I18n_Translation::__('Card Country Origin'),
				'value' => $jsonParameters['PaymentMeans']['Card']['CountryCode'] 
			);
		}
		
		if (isset($jsonParameters['PaymentMeans']['BankAccount']['IBAN'])) {
			$labels['account_iban'] = array(
				'label' => Customweb_I18n_Translation::__('IBAN'),
				'value' => $jsonParameters['PaymentMeans']['BankAccount']['IBAN'] 
			);
		}
		
		if (isset($jsonParameters['PaymentMeans']['BankAccount']['HolderName'])) {
			$labels['account_holder_name'] = array(
				'label' => Customweb_I18n_Translation::__('Account Holder Name'),
				'value' => $jsonParameters['PaymentMeans']['BankAccount']['HolderName'] 
			);
		}
		
		if (isset($jsonParameters['PaymentMeans']['BankAccount']['BIC'])) {
			$labels['account_bic'] = array(
				'label' => Customweb_I18n_Translation::__('BIC'),
				'value' => $jsonParameters['PaymentMeans']['BankAccount']['BIC'] 
			);
		}
		
		if (isset($jsonParameters['PaymentMeans']['BankAccount']['BankName'])) {
			$labels['bank_name'] = array(
				'label' => Customweb_I18n_Translation::__('Bank Name'),
				'value' => $jsonParameters['PaymentMeans']['BankAccount']['BankName'] 
			);
		}
		
		if (isset($jsonParameters['Transaction']['AcquirerReference'])) {
			$labels['aquirer_reference'] = array(
				'label' => Customweb_I18n_Translation::__('Acquirer Reference'),
				'value' => $jsonParameters['Transaction']['AcquirerReference'] 
			);
		}
		
		if (isset($jsonParameters['ThreeDs']['Authenticated'])) {
			$labels['threeds_authenticated'] = array(
				'label' => Customweb_I18n_Translation::__('3ds Authentication'),
				'value' => $jsonParameters['ThreeDs']['Authenticated'] ? Customweb_I18n_Translation::__('Yes') : Customweb_I18n_Translation::__('No')
			);
		}
		if (isset($jsonParameters['Liability']['ThreeDs']['Authenticated'])) {
			$labels['threeds_authenticated'] = array(
				'label' => Customweb_I18n_Translation::__('3ds Authentication'),
				'value' => $jsonParameters['Liability']['ThreeDs']['Authenticated'] ? Customweb_I18n_Translation::__('Yes') : Customweb_I18n_Translation::__('No') 
			);
		}
		
		if (isset($jsonParameters['ThreeDs']['LiabilityShift'])) {
			$labels['threeds_liability_shit'] = array(
				'label' => Customweb_I18n_Translation::__('3ds Liability Shift'),
				'value' => $jsonParameters['ThreeDs']['LiabilityShift'] ? Customweb_I18n_Translation::__('Yes') : Customweb_I18n_Translation::__('No')
			);
		}
		if (isset($jsonParameters['Liability']['ThreeDs']['LiabilityShift'])) {
			$labels['threeds_liability_shit'] = array(
				'label' => Customweb_I18n_Translation::__('3ds Liability Shift'),
				'value' => $jsonParameters['Liability']['ThreeDs']['LiabilityShift'] ? Customweb_I18n_Translation::__('Yes') : Customweb_I18n_Translation::__('No') 
			);
		}
		return $labels;
	}
	
	protected function buildNewCaptureObject($captureId, $amount, $status = NULL) {
		return new Customweb_Saferpay_Authorization_TransactionCapture($captureId, $amount, $status);
	}
	
	public function authorize($additionalInformation = '', $paid = true) {
		parent::authorize($additionalInformation, $paid);
		$this->setUpdateExecutionDate(null);
		return  $this;
	}
	
	public function setAuthorizationFailed($reason) {
		parent::setAuthorizationFailed($reason);
		$this->setUpdateExecutionDate(null);
		return  $this;
	}
}