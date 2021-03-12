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

//require_once 'Customweb/Payment/Authorization/IPaymentMethodWrapper.php';


/**
 *
 * @author Thomas Hunziker
 */
interface Customweb_Saferpay_Method_IMethod extends Customweb_Payment_Authorization_IPaymentMethodWrapper {
	
	/**
	 * Returns true if the payment method supports the transaction initialization then authorization process for alias.
	 * 
	 * @return bool
	 */
	public function isSupportingAliasInitialization();
	
	/**
	 * Returns the visible form fields that are needed the authorization.
	 *
	 * @return Customweb_Form_IElement[] List of visible form fields for this payment method.
	 */
	public function getVisibleFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction, $failedTransaction);
	
	
	/**
	 * Returns additional parameters that are required specifically for this payment method
	 * for the capture request.
	 *
	 * @return array Additional parameters
	*/
	public function getAdditionalCaptureParameters();
	
	/**
	 * Returns the flow used to authorize the transaction
	 * 	- redirect => customer should be redirected
	 *  - direct => authorize direct
	 *  - none => not supported
	 *
	 * @return string  redirect | direct | none
	*/
	public function getServerFlow();
	
	/**
	 * Returns the need authorization parameters for this payment method.
	 *
	 * @return array The authorization parameters.
	*/
	public function getAuthorizationParameters(Customweb_Saferpay_Authorization_Transaction $transaction, array $formData);
	
	/**
	 * Returns a key/value pair to identify the payment method at Saferpay
	 * @return array
	 */
	public function getPaymentMeanParameter();
	
	
	/**
	 * Returns true if a confirmation email should be sent by the PSP
	 */
	public function isCustomerConfirmationEmailSentByPSP();
	
	
	public function getPaymentInformationProvider();
	
	
	public function getMaxOrderIdLength();
}