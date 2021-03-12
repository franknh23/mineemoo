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

//require_once 'Customweb/Saferpay/Container.php';
//require_once 'Customweb/Saferpay/ParameterBuilder/ExternalCheckout/QueryPaymentMeans.php';
//require_once 'Customweb/Mvc/Template/RenderContext.php';
//require_once 'Customweb/Payment/ExternalCheckout/AbstractCheckoutEndpoint.php';
//require_once 'Customweb/Payment/Authorization/OrderContext/Address/Default.php';
//require_once 'Customweb/Core/Http/Response.php';
//require_once 'Customweb/Saferpay/ParameterBuilder/ExternalCheckout/AdjustAmount.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Saferpay/JsonAdapter.php';
//require_once 'Customweb/Mvc/Template/SecurityPolicy.php';
//require_once 'Customweb/Mvc/Layout/RenderContext.php';
//require_once 'Customweb/Saferpay/IConstants.php';
//require_once 'Customweb/Saferpay/ParameterBuilder/ExternalCheckout/CreateRedirect.php';
//require_once 'Customweb/Saferpay/ParameterBuilder/ExternalCheckout/Authorize.php';



/**
 * @Controller("masterpass")
 */
class Customweb_Saferpay_ExternalCheckout_MasterPass_Endpoint extends Customweb_Payment_ExternalCheckout_AbstractCheckoutEndpoint {
	
	/**
	 *
	 * @var Customweb_Saferpay_Container
	 */
	private $container;

	public function __construct(Customweb_DependencyInjection_IContainer $container){
		parent::__construct($container);
		$this->container = new Customweb_Saferpay_Container($container);
	}

	/**
	 * @Action("redirect")
	 */
	public function redirectAction(Customweb_Core_Http_IRequest $request){
		$context = $this->loadContextFromRequest($request);
		try{
			$this->checkContextTokenInRequest($request, $context);
		}catch(Customweb_Payment_Exception_ExternalCheckoutTokenExpiredException $e) {
			$this->getCheckoutService()->markContextAsFailed($context, $e->getMessage());
			return Customweb_Core_Http_Response::redirect($context->getCartUrl());
		}
		try {
			// We set already here the payment method to be able to access the
			// setting data in the redirection parameter builder.
			$checkoutService = $this->container->getCheckoutService();
			foreach ($checkoutService->getPossiblePaymentMethods($context) as $method) {
				if (strtolower($method->getPaymentMethodName()) == 'masterpass') {
					$checkoutService->updatePaymentMethod($context, $method);
					break;
				}
			}
			
						
			$builder = new Customweb_Saferpay_ParameterBuilder_ExternalCheckout_CreateRedirect($this->getContainer(), $context, $this->getSecurityTokenFromRequest($request));
			$jsonAdapter = new Customweb_Saferpay_JsonAdapter($this->getContainer());
			$url = $this->container->getConfiguration()->getJsonBaseUrl() . Customweb_Saferpay_IConstants::JSON_INITIALIZATION;
			//TODO: Fix this
			$result = $jsonAdapter->sendRequest($url, $builder->buildParameters());
			if (!isset($result['Redirect']['RedirectUrl']) || !isset($result['Token'])) {
				throw new Exception(Customweb_I18n_Translation::__('No redirect url or token received.'));
			}
			$parameters = array(
				'redirectUrl' => $result['Redirect']['RedirectUrl'],
				'token' => $result['Token'],
				'expirationDate' => $result['Expiration'] 
			);
			$this->getCheckoutService()->updateProviderData($context, array_merge($context->getProviderData(), $parameters));
			
			return Customweb_Core_Http_Response::redirect($parameters['redirectUrl']);
		}
		catch (Exception $e) {
			$this->getCheckoutService()->markContextAsFailed($context, $e->getMessage());
			return Customweb_Core_Http_Response::redirect($context->getCartUrl());
		}
	}

	/**
	 * @Action("fail")
	 */
	public function failAction(Customweb_Core_Http_IRequest $request){
		
		$context = $this->loadContextFromRequest($request);
		try{
			$this->checkContextTokenInRequest($request, $context);
		}catch(Customweb_Payment_Exception_ExternalCheckoutTokenExpiredException $e) {
			//Ignore expired
		}
		$this->getCheckoutService()->markContextAsFailed($context, Customweb_I18n_Translation::__('Authentication failed'));
		return Customweb_Core_Http_Response::redirect($context->getCartUrl());
	}
	
	/**
	 * 
	 * @Action("cancel");
	 */
	public function cancelAction(Customweb_Core_Http_IRequest $request){
		
		$context = $this->loadContextFromRequest($request);
		try{
			$this->checkContextTokenInRequest($request, $context);
		}catch(Customweb_Payment_Exception_ExternalCheckoutTokenExpiredException $e) {
			//Ignore expired
		}
		$this->getCheckoutService()->markContextAsFailed($context, Customweb_I18n_Translation::__('Cancelled.'));
		return Customweb_Core_Http_Response::redirect($context->getCartUrl());
	}
	
	
	/**
	 * @Action("update-context")
	 */
	public function updateContextAction(Customweb_Core_Http_IRequest $request){
		$checkoutService = $this->container->getCheckoutService();
		
		$this->getTransactionHandler()->beginTransaction();
		$context = $this->loadContextFromRequest($request);
		try{
			$this->checkContextTokenInRequest($request, $context);
		}catch(Customweb_Payment_Exception_ExternalCheckoutTokenExpiredException $e) {
			$this->getCheckoutService()->markContextAsFailed($context, $e->getMessage());
			$this->getTransactionHandler()->commitTransaction();
			return Customweb_Core_Http_Response::redirect($context->getCartUrl());
		}
		
		$jsonAdapter = new Customweb_Saferpay_JsonAdapter($this->getContainer());
		try {
			
			$builder = new Customweb_Saferpay_ParameterBuilder_ExternalCheckout_QueryPaymentMeans($this->getContainer(), $context);
			$url = $this->container->getConfiguration()->getJsonBaseUrl() . Customweb_Saferpay_IConstants::JSON_QUERYPAYMENTMEANS;
			$result = $jsonAdapter->sendRequest($url, $builder->buildParameters());
			if (!isset($result['Payer']['BillingAddress'])) {
				throw new Exception(Customweb_I18n_Translation::__('No billing address was returned.'));
			}
			if (!isset($result['Payer']['DeliveryAddress'])) {
				throw new Exception(Customweb_I18n_Translation::__('No delivery address was returned.'));
			}
			$billingAddress = $this->getAddressFromParameters($result['Payer']['BillingAddress']);
			$checkoutService->updateBillingAddress($context, $billingAddress);
			
			$shippingAddress = $this->getAddressFromParameters($result['Payer']['DeliveryAddress']);
			$checkoutService->updateShippingAddress($context, $shippingAddress);
			
			$this->getTransactionHandler()->commitTransaction();
			
			return $checkoutService->authenticate($context, $result['Payer']['BillingAddress']['Email'], 
					$this->getConfirmationPageUrl($context, $this->getSecurityTokenFromRequest($request)));
		}
		catch (Exception $e) {
			$this->getCheckoutService()->markContextAsFailed($context, $e->getMessage());
			$this->getTransactionHandler()->commitTransaction();
			return Customweb_Core_Http_Response::redirect($context->getCartUrl());
		}
	}

	/**
	 * @Action("confirmation")
	 */
	public function confirmationAction(Customweb_Core_Http_IRequest $request){
		$context = $this->loadContextFromRequest($request);
		try{
			$this->checkContextTokenInRequest($request, $context);
		}catch(Customweb_Payment_Exception_ExternalCheckoutTokenExpiredException $e) {
			$this->getCheckoutService()->markContextAsFailed($context, $e->getMessage());
			return Customweb_Core_Http_Response::redirect($context->getCartUrl());
		}
		try {
			
			$checkoutService = $this->container->getCheckoutService();
			$parameters = $request->getParameters();
			
			$templateContext = new Customweb_Mvc_Template_RenderContext();
			$confirmationErrorMessage = null;
			$shippingMethodErrorMessage = null;
			$additionalFormErrorMessage = null;
			if (isset($parameters['masterpass_update_shipping'])) {
				try {
					$checkoutService->updateShippingMethod($context, $request);
				}
				catch (Exception $e) {
					$shippingMethodErrorMessage = $e->getMessage();
				}
			}
			else if (isset($parameters['masterpass_confirmation'])) {
				try {
					$checkoutService->processAdditionalFormElements($context, $request);
				}
				catch (Exception $e) {
					$additionalFormErrorMessage = $e->getMessage();
				}
				if ($additionalFormErrorMessage === null) {
					try {
						$checkoutService->validateReviewForm($context, $request);
						
						$transaction = $checkoutService->createOrder($context);
						if (!$transaction->isAuthorized() && !$transaction->isAuthorizationFailed()) {
							$this->authorizeTransaction($context, $transaction);
						}
						if ($transaction->isAuthorizationFailed()) {
							$confirmationErrorMessage = current($transaction->getErrorMessages());
						}
						else {
							return Customweb_Core_Http_Response::redirect($transaction->getSuccessUrl());
						}
					}
					catch (Exception $e) {
						$confirmationErrorMessage = $e->getMessage();
					}
				}
			}
			
			$templateContext->setSecurityPolicy(new Customweb_Mvc_Template_SecurityPolicy());
			$templateContext->setTemplate('checkout/masterpass/confirmation');
			
			$templateContext->addVariable('additionalFormElements', 
					$checkoutService->renderAdditionalFormElements($context, $additionalFormErrorMessage));
			$templateContext->addVariable('shippingPane', $checkoutService->renderShippingMethodSelectionPane($context, $shippingMethodErrorMessage));
			$templateContext->addVariable('reviewPane', $checkoutService->renderReviewPane($context, true, $confirmationErrorMessage));
			$templateContext->addVariable('confirmationPageUrl', 
					$this->getConfirmationPageUrl($context, $this->getSecurityTokenFromRequest($request)));
			$templateContext->addVariable('javascript', 
					$this->getAjaxJavascript('.saferpay-masterpass-shipping-pane', '.saferpay-masterpass-confirmation-pane'));
			
			$content = $this->getTemplateRenderer()->render($templateContext);
			
			$layoutContext = new Customweb_Mvc_Layout_RenderContext();
			$layoutContext->setTitle(Customweb_I18n_Translation::__('MasterPass: Order Confirmation'));
			$layoutContext->setMainContent($content);
			return $this->getLayoutRenderer()->render($layoutContext);
		}
		catch (Exception $e) {
			$this->getCheckoutService()->markContextAsFailed($context, $e->getMessage());
			return Customweb_Core_Http_Response::redirect($context->getCartUrl());
		}
	}

	private function authorizeTransaction(Customweb_Payment_ExternalCheckout_IContext $context, Customweb_Saferpay_Authorization_Transaction $transaction){
		$this->getTransactionHandler()->beginTransaction();
		try {
			$jsonAdapter = new Customweb_Saferpay_JsonAdapter($this->getContainer());
			$adjustBuilder = new Customweb_Saferpay_ParameterBuilder_ExternalCheckout_AdjustAmount($this->getContainer(), $context, 
					$transaction);
			$adjustUrl = $this->container->getConfiguration()->getJsonBaseUrl() . Customweb_Saferpay_IConstants::JSON_ADJUSTAMOUNT;
			$jsonAdapter->sendRequest($adjustUrl, $adjustBuilder->buildParameters());
			
			$authorizeBuilder = new Customweb_Saferpay_ParameterBuilder_ExternalCheckout_Authorize($this->getContainer(), $context);
			$authorizeUrl = $this->container->getConfiguration()->getJsonBaseUrl() . Customweb_Saferpay_IConstants::JSON_AUTHORIZE;
			$result = $jsonAdapter->sendRequest($authorizeUrl, $authorizeBuilder->buildParameters());
			
			$jsonAdapter->checkPaymentResult($transaction, $result);
		}
		catch (Exception $e) {
			$transaction->setAuthorizationFailed($e->getMessage());
		}
		$this->getTransactionHandler()->persistTransactionObject($transaction);
		$this->getTransactionHandler()->commitTransaction();
	}

	private function getConfirmationPageUrl(Customweb_Payment_ExternalCheckout_IContext $context, $token){
		return $this->getUrl('masterpass', 'confirmation', array(
			'context-id' => $context->getContextId(),
			'token' => $token 
		));
	}

	private function getAddressFromParameters(array $parameters){
		$requiredParamters = array(
			'FirstName',
			'LastName',
			'Street',
			'Zip',
			'City',
			'CountryCode',
			'Email' 
		);
		foreach ($requiredParamters as $parameterName) {
			if (!isset($parameters[$parameterName])) {
				throw new Exception("Parameter $parameterName is missing.");
			}
		}
		
		$address = new Customweb_Payment_Authorization_OrderContext_Address_Default();
		// @formatter:off
		$address
			->setStreet($parameters['Street'])
			->setCity($parameters['City'])
			->setCountryIsoCode($parameters['CountryCode'])
			->setPostCode($parameters['Zip'])
			->setEMailAddress($parameters['Email']);
		// @formatter:on
		

		$firstName = $parameters['FirstName'];
		$lastName = $parameters['LastName'];
		if (empty($firstName)) {
			$splitted = explode(' ', $lastName, 2);
			if (count($splitted) > 1) {
				$firstName = $splitted[0];
				$lastName = $splitted[1];
			}
			else {
				throw new Exception("Could not extract firstname is missing.");
			}
		}
		
		$address->setFirstName($firstName)->setLastName($lastName);
		
		if (isset($parameters['Street2'])) {
			$address->setStreet($address->getStreet() . " " . $parameters['Street2']);
		}
		
		if (isset($parameters['Phone'])) {
			$address->setPhoneNumber($parameters['Phone']);
		}
		
		return $address;
	}

	/**
	 *
	 * @return Customweb_Saferpay_Method_Factory
	 */
	protected function getMethodFactory(){
		return $this->getContainer()->getBean('Customweb_Saferpay_Method_Factory');
	}

	protected function getPaymentMethodByTransaction(Customweb_Saferpay_Authorization_Transaction $transaction){
		return $this->getMethodFactory()->getPaymentMethod($transaction->getTransactionContext()->getOrderContext()->getPaymentMethod(), 
				$transaction->getAuthorizationMethod());
	}
}