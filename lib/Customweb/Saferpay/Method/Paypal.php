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

//require_once 'Customweb/Saferpay/Method/Default.php';
//require_once 'Customweb/Saferpay/IConstants.php';



/**
 *
 * @author Thomas Hunziker
 * @Method(paymentMethods={'paypal'})
 */
class Customweb_Saferpay_Method_Paypal extends Customweb_Saferpay_Method_Default {
	
	//We always send the address for paypal merchant protection
	public function getAddressBehavior(){
		return Customweb_Saferpay_IConstants::SEND_ADDRESS_MODE_BOTH;
	}
	
	public function getServerFlow(){
		return 'redirect';
	}
	
}