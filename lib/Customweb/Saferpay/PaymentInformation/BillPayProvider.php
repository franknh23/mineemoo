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

//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Saferpay/PaymentInformation/IProvider.php';

class Customweb_Saferpay_PaymentInformation_BillPayProvider implements Customweb_Saferpay_PaymentInformation_IProvider {

	public function getPaymentInformation(array $information){
		$formatted = '';
		
		$formatted = Customweb_I18n_Translation::__(
				"Thank you for choosing the BillPay Pay by invoice option. Please transfer the amount within the payment deadline to the following account, stating the reference (@ref):",
				array(
					"@ref" => $information['reasonForTransfer']['value'] 
				));
		$formatted .= '<br /><br />';
		$formatted .= Customweb_I18n_Translation::__("Amount: @amount", array(
			"@amount" => $information['amount']['value'] 
		)) . '<br />';
		if (isset($information['accountHolder'])) {
			$formatted .= Customweb_I18n_Translation::__("Account name: @account",
					array(
						"@account" => $information['accountHolder']['value'] 
					)) . '<br />';
		}
		$formatted .= Customweb_I18n_Translation::__("IBAN: @iban", array(
			"@iban" => $information['iban']['value'] 
		)) . '<br />';
		
		if (isset($information['bic'])) {
			$formatted .= Customweb_I18n_Translation::__("BIC: @bic", array(
				"@bic" => $information['bic']['value'] 
			)) . '<br />';
		}
		if (isset($information['bankName'])) {
			$formatted .= Customweb_I18n_Translation::__("Bank: @bank", array(
				"@bank" => $information['bankName']['value'] 
			)) . '<br />';
		}
		$formatted .= Customweb_I18n_Translation::__("Reference: @reference",
				array(
					"@reference" => $information['reasonForTransfer']['value'] 
				)) . '<br /><br />';
		$formatted .= Customweb_I18n_Translation::__(
				"Please note that your invoice is paid when the amount has successfully arrived the account named above.");
		
		return $formatted;
	}
}