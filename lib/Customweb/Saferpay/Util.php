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

//require_once 'Customweb/Payment/Util.php';
//require_once 'Customweb/Http/Request.php';
//require_once 'Customweb/Http/Response.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Util/Url.php';
//require_once 'Customweb/Util/Html.php';

final class Customweb_Saferpay_Util {

	private function __construct() {
		// prevent any instantiation of this class
	}

	public static function getCleanLanguageCode($lang) {
		$supportedLanguages = array('de_DE','en_US','fr_FR','da_DK',
				'cs_CZ','es_ES','hr_HR','it_IT','hu_HU','nl_NL',
				'no_NO','pl_PL','pt_PT','ru_RU','ro_RO','sk_SK',
				'sl_SI','fi_FI','sv_SE','tr_TR','el_GR','ja_JP', 'zh_CN'
		);
		return substr(Customweb_Core_Util_Language::getCleanLanguageCode($lang,$supportedLanguages), 0, 2);
	}
	
	
	public static function convertPaymentInformationArray(array $original, $amount, $currencyCode) {
		$information = array();
		$information['amount'] = array(
			'label' => Customweb_I18n_Translation::__('Amount'),
			'value' => Customweb_Util_Currency::formatAmount($amount, $currencyCode).' '.$currencyCode
		);
		if(isset($original['ReasonForTransfer'])){
			$information['reasonForTransfer'] = array(
				'label' => Customweb_I18n_Translation::__('Payment Reason'),
				'value' => $original['ReasonForTransfer']
			);
		}
		if(isset($original['Payee']['IBAN'])){
			$information['iban'] = array(
				'label' => Customweb_I18n_Translation::__('IBAN'),
				'value' => $original['Payee']['IBAN']
			);
		}
		if(isset($original['Payee']['HolderName'])){
			$information['accountHolder'] = array(
				'label' => Customweb_I18n_Translation::__('Account Holder'),
				'value' => $original['Payee']['HolderName']
			);
		}
		if(isset($original['Payee']['BIC'])){
			$information['bic'] = array(
				'label' => Customweb_I18n_Translation::__('BIC'),
				'value' => $original['Payee']['BIC']
			);
		}
		if(isset($original['Payee']['BankName'])){
			$information['bankName'] = array(
				'label' => Customweb_I18n_Translation::__('Bank Name'),
				'value' => $original['Payee']['BankName']
			);
		}
		if(isset($original['DueDate'])){
			$information['dueDate'] = array(
				'label' => Customweb_I18n_Translation::__('Due Date'),
				'value' => $original['DueDate']
			);
		}
		return $information;
	}
	
}