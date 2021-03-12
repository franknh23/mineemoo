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

//require_once 'Customweb/Payment/Exception/PaymentErrorException.php';



/**
 * Extends the Customweb_Payment_Exception_PaymentErrorException with the error name retrieved from the psp
 *
 * @author Nico Eigenmann
 */
class Customweb_Saferpay_Exception_RemoteErrorNameException extends Customweb_Payment_Exception_PaymentErrorException {
	
	/**
	 * @var string
	 */
	private $remoteErrorName = '';
	
	public function __construct(Customweb_Payment_Authorization_IErrorMessage $message, $remoteErrorName) {
		parent::__construct($message);
		$this->remoteErrorName = $remoteErrorName;
	}
	
	/**
	 * 
	 * @return string
	 */
	final public function getRemoteErrorName() {
		return $this->remoteErrorName;
	}
	
}