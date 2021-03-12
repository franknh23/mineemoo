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
//require_once 'Customweb/Saferpay/ParameterBuilder/AbstractBase.php';



/**
 *
 * @author Nico Eigenmann
 */
class Customweb_Saferpay_ParameterBuilder_StatusCheck extends Customweb_Saferpay_ParameterBuilder_AbstractBase {
	private $transaction;

	public function __construct(Customweb_DependencyInjection_IContainer $container, Customweb_Saferpay_Authorization_Transaction $transaction){
		parent::__construct($container);
		$this->transaction = $transaction;
	}

	protected function getTransaction(){
		return $this->transaction;
	}

	public function buildParameters(){
		$parameters = array(
			'RequestHeader' => $this->getRequestHeaderParameters(),
			'Token' => $this->getTransaction()->getInitToken() 
		);
		return $parameters;
	}
}