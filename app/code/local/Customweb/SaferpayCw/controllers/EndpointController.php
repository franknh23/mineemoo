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
 *
 * @category	Customweb
 * @package		Customweb_SaferpayCw
 *
 */

class Customweb_SaferpayCw_EndpointController extends Customweb_SaferpayCw_Controller_Action
{
	public function indexAction()
	{
		header_remove('Set-Cookie');

		$container = Mage::helper('SaferpayCw')->createContainer();
		$packages = array(
			0 => 'Customweb_Saferpay',
 			1 => 'Customweb_Payment_Authorization',
 		);
		$adapter = Mage::getModel('saferpaycw/endpointAdapter');

		$dispatcher = new Customweb_Payment_Endpoint_Dispatcher($adapter, $container, $packages);
		$response = $dispatcher->dispatch(Customweb_Core_Http_ContextRequest::getInstance());
		$wrapper = new Customweb_Core_Http_Response($response);
		$wrapper->send();
		die();
	}
}