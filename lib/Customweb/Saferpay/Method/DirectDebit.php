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
//require_once 'Customweb/Form/Element.php';
//require_once 'Customweb/Form/Control/HiddenInput.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Form/HiddenElement.php';
//require_once 'Customweb/Util/Address.php';
//require_once 'Customweb/Saferpay/Method/Default.php';
//require_once 'Customweb/Form/Control/Html.php';
//require_once 'Customweb/Form/ElementFactory.php';
//require_once 'Customweb/Payment/Authorization/Method/Sepa/Mandate.php';

/**
 *
 * @author Nico Eigenmann
 * @Method(paymentMethods={'directdebits'}, authorizationMethods={'ServerAuthorization'}, processors={'elv'})
 */
class Customweb_Saferpay_Method_DirectDebit extends Customweb_Saferpay_Method_Default {
	private static $MANDATE_ID_SCHEMA = '{year}{month}{day}-{random}';

	public function getVisibleFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction, $failedTransaction){
		$elements = array();
		
		if ($aliasTransaction == null || $aliasTransaction == 'new' ||
				 Customweb_Util_Address::compareShippingAddresses($orderContext, $aliasTransaction->getTransactionContext()->getOrderContext())) {
			$elements[] = Customweb_Form_ElementFactory::getIbanNumberElement('iban');
		}
		
		return array_merge($this->getMandateElements($orderContext), $elements);
	}

	private function getMandateElements(Customweb_Payment_Authorization_IOrderContext $orderContext){
		$customer = $orderContext->getBillingAddress()->getFirstName() . ' ' . $orderContext->getBillingAddress()->getLastName();
		$mandateId = Customweb_Payment_Authorization_Method_Sepa_Mandate::generateMandateId(self::$MANDATE_ID_SCHEMA);
		
		$mandateIdControl = new Customweb_Form_Control_HiddenInput('mandateId', $mandateId);
		$mandateIdElement = new Customweb_Form_HiddenElement($mandateIdControl);
		
		$mandateTextControl = new Customweb_Form_Control_Html('mandate_text', $this->getMandateText($mandateId, $customer));
		$mandateTextElement = new Customweb_Form_Element(Customweb_I18n_Translation::__('Mandate text'), $mandateTextControl);
		$mandateTextElement->setRequired(false);
		
		return array(
			$mandateIdElement,
			$mandateTextElement 
		);
	}

	private function getMandateText($mandateReference, $customer){
		$creditorId = $this->getPaymentMethodConfigurationValue('creditor_identifier');
		$merchantName = $this->getPaymentMethodConfigurationValue('mandate_name');
		$date = date('Y-m-d H:i');
		
		//@formatter:off
		return Customweb_I18n_Translation::__("Creditor identification number: !CREDITOR_ID<br/>
Mandate reference: !MANDATE_REFERENCE<br/>
Date: !DATE<br/>
<br/>
I !CUSTOMER grant !MERCHANT_NAME the revocable authorization to debit the above-listed amount and to collect future payments to be paid by me from my account by means of direct debit, in accordance with my details listed above.
At the same time, I instruct my financial institution to redeem the debits from on my account.
<br/>
<br/>
Note: I can request the reimbursement of the debited amount with eight weeks of the date on which it is debited. The conditions agreed to with my financial institution apply for this.
<br/>
I hereby confirm the SEPA Direct Debit mandate", array(
								'!CREDITOR_ID' => $creditorId,
								'!CUSTOMER' => $customer,
								'!MANDATE_REFERENCE' => $mandateReference,
								'!DATE' => $date,
								'!MERCHANT_NAME' => $merchantName,
							))
		;
		//@formatter:on
	}

	public function getServerFlow(){
		return 'direct';
	}

	public function getAuthorizationParameters(Customweb_Saferpay_Authorization_Transaction $transaction, array $formData){
		$parameters = array(
			'Payment' => array(
				'MandateId' => $formData['mandateId'] 
			) 
		);
		
		if ((!$transaction->isUseExistingAlias() ||
				 Customweb_Util_Address::compareShippingAddresses($transaction->getTransactionContext()->getOrderContext(), 
						$transaction->getTransactionContext()->getAlias()->getTransactionContext()->getOrderContext())) && !isset($formData['iban'])) {
			throw new Exception(Customweb_I18n_Translation::__('No IBAN provided.'));
		}
		elseif (!$transaction->isUseExistingAlias()) {
			$parameters['PaymentMeans'] = array(
				'BankAccount' => array(
					'IBAN' => str_replace(' ', '', $formData['iban']) 
				) 
			);
		}
		return $parameters;
	}
}
