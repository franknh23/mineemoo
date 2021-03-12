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

/**
 * This interface provides some constants for the Saferpay service.
 *
 * @author Severin Klingler
 *      	    	 			    	 
 */
interface Customweb_Saferpay_IConstants {
	
	const SEND_ADDRESS_MODE_NONE = 'none';
	const SEND_ADDRESS_MODE_DELIVERY = 'delivery';
	const SEND_ADDRESS_MODE_BILLING = 'billing';
	const SEND_ADDRESS_MODE_BOTH = 'both';

	/**
	 * Technical stuff
	 */
	const MAX_ALLOWED_URL_LENGTH = 2000;
	const PARAMETER_SIGNATURE = 'cwsig';
	
	
	/**
	 * JSON API constants
	 */
	const JSON_API_VERSION = '1.15';
	
	const JSON_PAYMENTPAGE_INIT = '/Payment/v1/PaymentPage/Initialize';
	const JSON_PAYMENTPAGE_ASSERT = '/Payment/v1/PaymentPage/Assert';
	const JSON_INITIALIZATION = '/Payment/v1/Transaction/Initialize';
	const JSON_AUTHORIZE = '/Payment/v1/Transaction/Authorize';
	const JSON_AUTHORIZE_DIRECT = '/Payment/v1/Transaction/AuthorizeDirect';
	const JSON_AUTHORIZE_REFERENECED = '/Payment/v1/Transaction/AuthorizeReferenced';
	const JSON_QUERYPAYMENTMEANS = '/Payment/v1/Transaction/QueryPaymentMeans';
	const JSON_ADJUSTAMOUNT = '/Payment/v1/Transaction/AdjustAmount';
	const JSON_REDIRECT_PAYMENT = '/Payment/v1/Transaction/RedirectPayment';
	const JSON_REDIRECT_PAYMENT_ASSERT = '/Payment/v1/Transaction/AssertRedirectPayment';
	const JSON_CAPTURE = '/Payment/v1/Transaction/Capture';
	const JSON_CANCEL = '/Payment/v1/Transaction/Cancel';
	const JSON_REFUND_REFERENCED = '/Payment/v1/Transaction/Refund';
	const JSON_REFUND_DIRECT = '/Payment/v1/Transaction/RefundDirect';
	const JSON_ALIAS_INSERT_FORM = '/Payment/v1/Alias/Insert';
	
	
	//Error Codes returned by PSP
	const ERROR_NAME_AUTHENTICATION_FAILED = 'AUTHENTICATION_FAILED';
	const ERROR_NAME_ALIAS_INVALID = 'ALIAS_INVALID';
	const ERROR_NAME_BLOCKED_BY_RISK_MANAGEMENT = 'BLOCKED_BY_RISK_MANAGEMENT';
	const ERROR_NAME_CARD_CHECK_FAILED = 'CARD_CHECK_FAILED';
	const ERROR_NAME_CARD_CVC_INVALID = 'CARD_CVC_INVALID';
	const ERROR_NAME_CARD_CVC_REQUIRED = 'CARD_CVC_REQUIRED';
	const ERROR_NAME_CARD_EXPIRED = 'CARD_EXPIRED';
	const ERROR_NAME_PAYMENTMEANS_INVALID = 'PAYMENTMEANS_INVALID';
	const ERROR_NAME_INTERNAL_ERROR = 'INTERNAL_ERROR';
	const ERROR_NAME_3DS_AUTHENTICATION_FAILED = '3DS_AUTHENTICATION_FAILED';
	const ERROR_NAME_NO_CONTRACT = 'NO_CONTRACT';
	const ERROR_NAME_NO_CREDITS_AVAILABLE = 'NO_CREDITS_AVAILABLE';
	const ERROR_NAME_PERMISSION_DENIED = 'PERMISSION_DENIED';
	const ERROR_NAME_TRANSACTION_DECLINED = 'TRANSACTION_DECLINED';
	const ERROR_NAME_VALIDATION_FAILED= 'VALIDATION_FAILED';
	const ERROR_NAME_AMOUNT_INVALID = 'AMOUNT_INVALID';
	const ERROR_NAME_CURRENCY_INVALID = 'CURRENCY_INVALID';
	const ERROR_NAME_COMMUNICATION_FAILED = 'COMMUNICATION_FAILED';
	const ERROR_NAME_COMMUNICATION_TIMEOUT = 'COMMUNICATION_TIMEOUT';
	const ERROR_NAME_TOKEN_EXPIRED = 'TOKEN_EXPIRED';
	const ERROR_NAME_TOKEN_INVALID = 'TOKEN_INVALID';
	const ERROR_NAME_TRANSACTION_IN_WRONG_STATE = 'TRANSACTION_IN_WRONG_STATE';
	const ERROR_NAME_ACTION_NOT_SUPPORTED = 'ACTION_NOT_SUPPORTED';
	const ERROR_NAME_TRANSACTION_NOT_FOUND = 'TRANSACTION_NOT_FOUND';
	const ERROR_NAME_CONDITION_NOT_SATISFIED = 'CONDITION_NOT_SATISFIED';
	const ERROR_NAME_TRANSACTION_NOT_STARTED = 'TRANSACTION_NOT_STARTED';
	const ERROR_NAME_TRANSACTION_ABORTED = 'TRANSACTION_ABORTED';
	const ERROR_NAME_GENERAL_DECLINED = 'GENERAL_DECLINED';
	const ERROR_NAME_TRANSACTION_ALREADY_CAPTURED = 'TRANSACTION_ALREADY_CAPTURED';

	//Additional Error Codes for detailed analysis
	const ERROR_NAME_UNKOWN = 'UNKOWN';
	const ERROR_NAME_NOT_RETURNED = 'NOT_RETURNED';
	
	
	
}