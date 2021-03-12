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
//require_once 'Customweb/Saferpay/Authorization/Transaction.php';
//require_once 'Customweb/Core/Exception/CastException.php';
//require_once 'Customweb/Payment/Endpoint/Controller/Abstract.php';
//require_once 'Customweb/Payment/Authorization/ErrorMessage.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Saferpay/IConstants.php';
//require_once 'Customweb/Core/Logger/Factory.php';
//require_once 'Customweb/Core/Http/Response.php';


/**
 *
 * @author Nico Eigenmann
 * @Controller("process")
 *
 */
class Customweb_Saferpay_Endpoint_Process extends Customweb_Payment_Endpoint_Controller_Abstract {
	
	/**
	 * @var Customweb_Core_ILogger
	 */
	private $logger;

	/**
	 * @param Customweb_DependencyInjection_IContainer $container
	 */
	public function __construct(Customweb_DependencyInjection_IContainer $container){
		parent::__construct($container);
		$this->logger = Customweb_Core_Logger_Factory::getLogger(get_class($this));
	}

	/**
	 * 
	 * @Action("alias-success")
	 * @param Customweb_Core_Http_IRequest $request
	 */
	public function aliasSuccess(Customweb_Core_Http_IRequest $request){
		$idMap = $this->getTransactionId($request);
		$transaction = $this->getTransactionHandler()->findTransactionByTransactionExternalId($idMap['id']);
		$this->logger->logInfo("The alias success process has been started for the transaction with external transaction id " . $idMap['id'] . ".");
		if (!($transaction instanceof Customweb_Saferpay_Authorization_Transaction)) {
			throw new Customweb_Core_Exception_CastException('Customweb_Saferpay_Authorization_Transaction');
		}
		$this->checkSignature('process/alias-success', $transaction, $request);
		$lastException = new Exception('This should never happen.');
		for ($i = 0; $i < 5; $i++) {
			try {
				$this->getTransactionHandler()->beginTransaction();
				$transaction = $this->getTransactionHandler()->findTransactionByTransactionExternalId($idMap['id'], false);
				if ($transaction->isAuthorized() || $transaction->isAuthorizationFailed()) {
					$this->getTransactionHandler()->commitTransaction();
					$this->logger->logInfo(
							"The alias success process has been finished for the transaction with external transaction id " . $idMap['id'] . ".");
					return $this->getResponse($transaction, $transaction->getSuccessUrl());
				}
				
				$this->getContainer()->getBean('Customweb_Saferpay_Authorization_AliasAdapter')->confirm($transaction,
						$request->getParameters());
				$this->getTransactionHandler()->persistTransactionObject($transaction);
				$this->getTransactionHandler()->commitTransaction();
				$this->logger->logInfo(
						"The alias success process has been finished for the transaction with external transaction id " . $idMap['id'] . ".");
				return $this->getResponse($transaction, $transaction->getSuccessUrl());
			}
			catch (Customweb_Payment_Exception_OptimisticLockingException $lockingException) {
				$lastException = $lockingException;
				$this->getTransactionHandler()->rollbackTransaction();
				sleep(1);
			}
			catch (Exception $e) {
				$this->logger->logException($e);
				$this->getTransactionHandler()->rollbackTransaction();
				throw $e;
			}
		}
		$this->logger->logException($lastException);
		return $this->getResponse($transaction, $transaction->getFailedUrl());
	}

	private function getResponse(Customweb_Saferpay_Authorization_Transaction $transaction, $url){
		if ($transaction->getAuthorizationMethod() == Customweb_Payment_Authorization_Widget_IAdapter::AUTHORIZATION_METHOD_NAME) {
			return $this->getBreakoutResponse($url);
		}
		return Customweb_Core_Http_Response::redirect($url);
	}

	/**
	 * @Action("json")
	 */
	public function jsonAction(Customweb_Core_Http_IRequest $request){
		$idMap = $this->getTransactionId($request);
		$transaction = $this->getTransactionHandler()->findTransactionByTransactionExternalId($idMap['id']);
		$this->logger->logInfo("The notification process has been started for the transaction with external transaction id " . $idMap['id'] . ".");
		if (!($transaction instanceof Customweb_Saferpay_Authorization_Transaction)) {
			throw new Customweb_Core_Exception_CastException('Customweb_Saferpay_Authorization_Transaction');
		}
		$this->checkSignature('process/json', $transaction, $request);
		$lastException = new Exception('This should never happen.');
		for ($i = 0; $i < 5; $i++) {
			try {
				$this->getTransactionHandler()->beginTransaction();
				$transaction = $this->getTransactionHandler()->findTransactionByTransactionExternalId($idMap['id'], false);
				if ($transaction->isAuthorized() || $transaction->isAuthorizationFailed()) {
					$this->getTransactionHandler()->commitTransaction();
					$this->logger->logInfo(
							"The notification process has been finished for the transaction with external transaction id " . $idMap['id'] . ".");
					return Customweb_Core_Http_Response::_('')->setStatusCode(200);
				}
				$adapter = $this->getAdapterFactory()->getAuthorizationAdapterByName($transaction->getAuthorizationMethod());
				$adapter->getResultAndUpdate($transaction);
				$this->getTransactionHandler()->persistTransactionObject($transaction);
				$this->getTransactionHandler()->commitTransaction();
				$this->logger->logInfo(
						"The notification process has been finished for the transaction with external transaction id " . $idMap['id'] . ".");
				return Customweb_Core_Http_Response::_('')->setStatusCode(200);
			}
			catch (Customweb_Payment_Exception_OptimisticLockingException $lockingException) {
				$lastException = $lockingException;
				$this->getTransactionHandler()->rollbackTransaction();
				sleep(1);
			}
			catch (Exception $e) {
				$this->logger->logException($e);
				$this->getTransactionHandler()->rollbackTransaction();
				throw $e;
			}
		}
		$this->logger->logException($lastException);
		throw $lastException;
	}

	/**
	 * @Action("alias")
	 */
	public function aliasAction(Customweb_Payment_Authorization_ITransaction $transaction, Customweb_Core_Http_IRequest $request){
		if (!($transaction instanceof Customweb_Saferpay_Authorization_Transaction)) {
			throw new Customweb_Core_Exception_CastException('Customweb_Saferpay_Authorization_Transaction');
		}
		$this->checkSignature('process/alias', $transaction, $request);
		$adapter = $this->getAdapterFactory()->getAuthorizationAdapterByName($transaction->getAuthorizationMethod());
		$adapter->handleAliasAuthorization($transaction, $request->getParameters());
		$url = $transaction->getSuccessUrl();
		if ($transaction->isAuthorizationFailed()) {
			$url = $transaction->getFailedUrl();
		}
		return $this->getResponse($transaction, $url);
	}

	/**
	 * Called when pp fails, as PSP does not send a notification
	 * @Action("fail")
	 */
	public function failAction(Customweb_Core_Http_IRequest $request){
		$idMap = $this->getTransactionId($request);
		$transaction = $this->getTransactionHandler()->findTransactionByTransactionExternalId($idMap['id']);
		$this->logger->logInfo("The failure process has been started for the transaction with external transaction id " . $idMap['id'] . ".");
		if (!($transaction instanceof Customweb_Saferpay_Authorization_Transaction)) {
			throw new Customweb_Core_Exception_CastException('Customweb_Saferpay_Authorization_Transaction');
		}
		$this->checkSignature('process/fail', $transaction, $request);
		$lastException = new Exception('This should never happen.');
		for ($i = 0; $i < 5; $i++) {
			try {
				$this->getTransactionHandler()->beginTransaction();
				$transaction = $this->getTransactionHandler()->findTransactionByTransactionExternalId($idMap['id'], false);
				$this->handleFailure($transaction, $request);
				$this->getTransactionHandler()->persistTransactionObject($transaction);
				$this->getTransactionHandler()->commitTransaction();
				$this->logger->logInfo("The failure process has been finished for the transaction with external transaction id " . $idMap['id'] . ".");
				return $this->getResponse($transaction, $transaction->getFailedUrl());
			}
			catch (Customweb_Payment_Exception_OptimisticLockingException $lockingException) {
				$lastException = $lockingException;
				$this->getTransactionHandler()->rollbackTransaction();
				sleep(1);
			}
			catch (Exception $e) {
				$this->logger->logException($e);
				$this->getTransactionHandler()->rollbackTransaction();
				return $this->getResponse($transaction, $transaction->getFailedUrl());
			}
		}
		$this->logger->logException($lastException);
		return $this->getResponse($transaction, $transaction->getFailedUrl());
	}

	private function handleFailure(Customweb_Saferpay_Authorization_Transaction $transaction, Customweb_Core_Http_IRequest $request){
		if ($transaction->isAuthorizationFailed()) {
			return;
		}
		$adapter = $this->getAdapterFactory()->getAuthorizationAdapterByName($transaction->getAuthorizationMethod());
		$adapter->getResultAndUpdate($transaction);
		$parameters = $request->getParameters();		
		if(!$transaction->isAuthorizationFailed() && !$transaction->isAuthorized()){
			if (isset($parameters['cancelled']) && $parameters['cancelled'] == 'true') {
				$transaction->setAuthorizationFailed(
						new Customweb_Payment_Authorization_ErrorMessage(Customweb_I18n_Translation::__('Transaction cancelled'),
								Customweb_I18n_Translation::__('Transaction cancelled by the customer.')));
			}
			else {
				$transaction->setAuthorizationFailed(
						new Customweb_Payment_Authorization_ErrorMessage(Customweb_I18n_Translation::__("The payment could not be processed."),
								Customweb_I18n_Translation::__("The payment failed.")));
			}
		}
	}

	/**
	 * @Action("success")
	 */
	public function successAction(Customweb_Core_Http_IRequest $request){
		$transactionHandler = $this->getTransactionHandler();
		$idMap = $this->getTransactionId($request);
		$transaction = $transactionHandler->findTransactionByTransactionExternalId($idMap['id']);
		$this->checkSignature('process/success', $transaction, $request);
		$this->logger->logInfo("The success process has been started for the transaction with external transaction id " . $idMap['id'] . ".");
		if (!($transaction instanceof Customweb_Saferpay_Authorization_Transaction)) {
			throw new Customweb_Core_Exception_CastException('Customweb_Saferpay_Authorization_Transaction');
		}
		$lastException = new Exception('This should never happen.');
		for ($i = 0; $i < 5; $i++) {
			try {
				$this->getTransactionHandler()->beginTransaction();
				$transaction = $this->getTransactionHandler()->findTransactionByTransactionExternalId($idMap['id'], false);
				if ($transaction->isAuthorized()) {
					$this->getTransactionHandler()->commitTransaction();
					$this->logger->logInfo(
							"The success process has been finished for the transaction with external transaction id " . $idMap['id'] . ".");
					return $this->getResponse($transaction, $transaction->getSuccessUrl());
				}
				if ($transaction->isAuthorizationFailed()) {
					$this->getTransactionHandler()->commitTransaction();
					$this->logger->logInfo(
							"The success process has been finished for the transaction with external transaction id " . $idMap['id'] . ".");
					return $this->getResponse($transaction, $transaction->getSuccessUrl());
				}
				$adapter = $this->getAdapterFactory()->getAuthorizationAdapterByName($transaction->getAuthorizationMethod());
				$adapter->getResultAndUpdate($transaction);
				$this->getTransactionHandler()->persistTransactionObject($transaction);
				$this->getTransactionHandler()->commitTransaction();
				$this->logger->logInfo("The success process has been finished for the transaction with external transaction id " . $idMap['id'] . ".");
				if ($transaction->isAuthorizationFailed()) {
					return $this->getResponse($transaction, $transaction->getFailedUrl());
				}
				return $this->getResponse($transaction, $transaction->getSuccessUrl());
			}
			catch (Customweb_Payment_Exception_OptimisticLockingException $lockingException) {
				$lastException = $lockingException;
				$this->getTransactionHandler()->rollbackTransaction();
				sleep(1);
			}
			catch (Exception $e) {
				$this->logger->logException($e);
				$this->getTransactionHandler()->rollbackTransaction();
				return $this->getResponse($transaction, $transaction->getSuccessUrl());
			}
		}
		$this->logger->logException($lastException);
		return $this->getResponse($transaction, $transaction->getSuccessUrl());
	}


	private function checkSignature($entity, Customweb_Saferpay_Authorization_Transaction $transaction, Customweb_Core_Http_IRequest $request){
		$parameters = $request->getParameters();
		if (!isset($parameters[Customweb_Saferpay_IConstants::PARAMETER_SIGNATURE])) {
			throw new Exception('Signature missing;');
		}
		$transaction->checkSecuritySignature($entity, $parameters[Customweb_Saferpay_IConstants::PARAMETER_SIGNATURE]);
	}

	private function getBreakoutResponse($url){
		return Customweb_Core_Http_Response::_(
				'<script type="text/javascript">
				top.location.href = "' . $url . '";
			</script>
		
			<noscript>
				<a class="button btn saferpay-continue-button submit" href="' . $url . '" target="_top">' . Customweb_I18n_Translation::__('Continue') . '</a>
			</noscript>');
	}
}