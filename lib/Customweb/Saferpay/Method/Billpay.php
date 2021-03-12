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
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Saferpay/Method/Default.php';
//require_once 'Customweb/Form/ElementFactory.php';
//require_once 'Customweb/Saferpay/ElementFactory.php';
//require_once 'Customweb/Saferpay/IConstants.php';
//require_once 'Customweb/Saferpay/PaymentInformation/BillPayProvider.php';

/**
 *
 * @author Nico Eigenmann
 * 
 */
abstract class Customweb_Saferpay_Method_Billpay extends Customweb_Saferpay_Method_Default {

	public function getAddressBehavior(){
		return Customweb_Saferpay_IConstants::SEND_ADDRESS_MODE_BILLING;
	}

	public function getVisibleFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction, $failedTransaction){
		$elements = parent::getVisibleFormFields($orderContext, $aliasTransaction, $failedTransaction);
		
		$companyName = $orderContext->getBillingAddress()->getCompanyName();
		if(empty($companyName)){	
			$dob = $orderContext->getBillingAddress()->getDateOfBirth();
			if ($dob == null) {
				$elements[] = Customweb_Form_ElementFactory::getDateOfBirthElement('dob-year', 'dob-month', 'dob-day');
			}
			 
			
			$gender = $orderContext->getBillingAddress()->getGender();
			if (empty($gender)) {
				$elements[] = Customweb_Saferpay_ElementFactory::getGenderElement('gender');
			}
		}
		else{
			$elements[] = Customweb_Saferpay_ElementFactory::getLegalFormElement('legalform');
		}
		return $elements;
	}

	public function getAuthorizationParameters(Customweb_Saferpay_Authorization_Transaction $transaction, array $formData){
		$parameters = array(
			'Payer' => array(
				'BillingAddress' => array() 
			) 
		);
		$companyName = $transaction->getTransactionContext()->getOrderContext()->getBillingAddress()->getCompanyName();
		if(empty($companyName)){
		
			$dob = $transaction->getTransactionContext()->getOrderContext()->getBillingAddress()->getDateOfBirth();
			if ($dob == null) {
				if (!isset($formData['dob-year']) || !isset($formData['dob-month']) || !isset($formData['dob-day'])) {
					throw new Exception(Customweb_I18n_Translation::__('No date of birth provided.'));
				}
				$parameters['Payer']['BillingAddress']['DateOfBirth'] = $formData['dob-year'] . '-' . $formData['dob-month'] . '-' . $formData['dob-day'];
			}
			$gender = $transaction->getTransactionContext()->getOrderContext()->getBillingAddress()->getGender();
			if ($gender == null) {
				if (!isset($formData['gender'])){
					throw new Exception(Customweb_I18n_Translation::__('No gender provided.'));
				}
				switch($formData['gender']) {
					case 'male':
						$parameters['Payer']['BillingAddress']['Gender'] = 'MALE';
						break;
					case 'female':
						$parameters['Payer']['BillingAddress']['Gender'] = 'FEMALE';
						break;
					default:
						throw new Exception(Customweb_I18n_Translation::__('No gender provided.'));
						break;
				}
			}
		}
		else{
			$parameters['Payer']['BillingAddress']['Gender'] = 'COMPANY';
			if(!isset($formData['legalform'])){
				throw new Exception(Customweb_I18n_Translation::__('No legal form provided.'));
			}
			switch($formData['legalform']) {
				case 'AG':
					$parameters['Payer']['BillingAddress']['LegalForm'] = 'AG';
					break;
				case 'GmbH':
					$parameters['Payer']['BillingAddress']['LegalForm'] = 'GmbH';
					break;
				case 'Misc':
					$parameters['Payer']['BillingAddress']['LegalForm'] = 'Misc';
					break;
				default:
					throw new Exception(Customweb_I18n_Translation::__('No legal form provided.'));
					break;
			}
		}
		return $parameters;
	}
	
	public function getPaymentInformationProvider(){
		return new Customweb_Saferpay_PaymentInformation_BillPayProvider();
	}
}