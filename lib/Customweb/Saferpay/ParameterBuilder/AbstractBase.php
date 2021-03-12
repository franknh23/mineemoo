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
//require_once 'Customweb/Filter/Input/String.php';
//require_once 'Customweb/Core/Util/Rand.php';
//require_once 'Customweb/Saferpay/IConstants.php';


abstract class Customweb_Saferpay_ParameterBuilder_AbstractBase{

	private $container;


	public function __construct(Customweb_DependencyInjection_IContainer $container){
		if(!($container instanceof Customweb_Saferpay_Container)){
			$container = new Customweb_Saferpay_Container($container);
		}
		$this->container = new Customweb_Saferpay_Container($container);
	}

	/**
	 *
	 * @return Customweb_Saferpay_Container
	 */
	protected function getContainer(){
		return $this->container;
	}

	protected function getRequestHeaderParameters(){
		return array(
			'SpecVersion' => Customweb_Saferpay_IConstants::JSON_API_VERSION,
			'CustomerId' => $this->getContainer()->getConfiguration()->getCustomerId(),
			'RequestId' => Customweb_Core_Util_Rand::getUuid(),
			'RetryIndicator' => 0,

		);

	}

	protected function getTerminalId(){
		return $this->getContainer()->getConfiguration()->getTerminalId();
	}


	protected function getAddressParameters(Customweb_Payment_Authorization_OrderContext_IAddress $address){
		$parameters =  array();

		$firstName = trim($address->getFirstName());
		if(!empty($firstName)){
			$parameters['FirstName'] = Customweb_Filter_Input_String::_($firstName, 254)->filter();
		}

		$lastName = trim($address->getLastName());
		if(!empty($lastName)){
			$parameters['LastName'] = Customweb_Filter_Input_String::_($lastName, 254)->filter();
		}

		$street = $address->getStreet();
		if(!empty($street)){
			$parameters['Street'] = Customweb_Filter_Input_String::_($street, 254)->filter();
		}

		$zip = $address->getPostCode();
		if(!empty($zip)){
			$parameters['Zip'] = Customweb_Filter_Input_String::_($zip, 254)->filter();
		}

		$city = trim($address->getCity());
		if(!empty($city)){
			$parameters['City'] = Customweb_Filter_Input_String::_($city, 254)->filter();
		}

		$country = trim($address->getCountryIsoCode());
		if(!empty($country)){
			$parameters['CountryCode'] = $country;

			$state = trim($address->getState());
			if (!empty($state) && strtoupper($country) === 'US') {
				$parameters['CountrySubdivisionCode'] = $state;
			}
		}

		$companyName = trim($address->getCompanyName());
		if(!empty($companyName)) {

			$parameters['Company'] = Customweb_Filter_Input_String::_($companyName, 254)->filter();
		}
		if($address->getGender() !== null) {
			switch($address->getGender()) {
				case 'male':
					$parameters['Gender'] = 'MALE';
					break;
				case 'female':
					$parameters['Gender'] = 'FEMALE';
					break;
			}
		}

		$phoneNumber = trim($address->getPhoneNumber());
		if(!empty($phoneNumber)) {
			$parameters['Phone'] = Customweb_Filter_Input_String::_($phoneNumber, 100)->filter();
		}

		$email = trim($address->getEMailAddress());
		if(!empty($email)) {
			$parameters['Email'] = Customweb_Filter_Input_String::_($email, 254)->filter();
		}
		$dob = $address->getDateOfBirth();
		if($dob instanceof DateTime) {
			$parameters['DateOfBirth'] = $dob->format('Y-m-d');
		}
		return $parameters;
	}


	protected function getStylingParameters(){
		$cssUrl = $this->getContainer()->getConfiguration()->getCssUrl();
		$theme = $this->getContainer()->getConfiguration()->getPaymentPageTheme();
		$parameters = array();
		if (!empty($cssUrl)) {
			$parameters['CssUrl'] = $cssUrl;
		}
		if (!empty($theme)) {
			$parameters['Theme'] = $theme;
		}
		return $parameters;
	}
}