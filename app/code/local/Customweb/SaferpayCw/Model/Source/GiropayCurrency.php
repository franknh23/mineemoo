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
 */

class Customweb_SaferpayCw_Model_Source_GiropayCurrency
{
	public function toOptionArray()
	{
		$options = array(
			array('value' => 'AED', 'label' => Mage::helper('adminhtml')->__("United Arab Emirates dirham (AED)")),
			array('value' => 'AFN', 'label' => Mage::helper('adminhtml')->__("Afghan afghani (AFN)")),
			array('value' => 'ALL', 'label' => Mage::helper('adminhtml')->__("Albanian lek (ALL)")),
			array('value' => 'AMD', 'label' => Mage::helper('adminhtml')->__("Armenian dram (AMD)")),
			array('value' => 'ANG', 'label' => Mage::helper('adminhtml')->__("Netherlands Antillean guilder (ANG)")),
			array('value' => 'AOA', 'label' => Mage::helper('adminhtml')->__("Angolan kwanza (AOA)")),
			array('value' => 'ARS', 'label' => Mage::helper('adminhtml')->__("Argentine peso (ARS)")),
			array('value' => 'AUD', 'label' => Mage::helper('adminhtml')->__("Australian dollar (AUD)")),
			array('value' => 'AWG', 'label' => Mage::helper('adminhtml')->__("Aruban florin (AWG)")),
			array('value' => 'AZN', 'label' => Mage::helper('adminhtml')->__("Azerbaijani manat (AZN)")),
			array('value' => 'BAM', 'label' => Mage::helper('adminhtml')->__("Bosnia and Herzegovina convertible mark (BAM)")),
			array('value' => 'BBD', 'label' => Mage::helper('adminhtml')->__("Barbados dollar (BBD)")),
			array('value' => 'BDT', 'label' => Mage::helper('adminhtml')->__("Bangladeshi taka (BDT)")),
			array('value' => 'BGN', 'label' => Mage::helper('adminhtml')->__("Bulgarian lev (BGN)")),
			array('value' => 'BHD', 'label' => Mage::helper('adminhtml')->__("Bahraini dinar (BHD)")),
			array('value' => 'BIF', 'label' => Mage::helper('adminhtml')->__("Burundian franc (BIF)")),
			array('value' => 'BMD', 'label' => Mage::helper('adminhtml')->__("Bermudian dollar (BMD)")),
			array('value' => 'BND', 'label' => Mage::helper('adminhtml')->__("Brunei dollar (BND)")),
			array('value' => 'BOB', 'label' => Mage::helper('adminhtml')->__("Boliviano (BOB)")),
			array('value' => 'BOV', 'label' => Mage::helper('adminhtml')->__("Bolivian Mvdol (BOV)")),
			array('value' => 'BRL', 'label' => Mage::helper('adminhtml')->__("Brazilian real (BRL)")),
			array('value' => 'BSD', 'label' => Mage::helper('adminhtml')->__("Bahamian dollar (BSD)")),
			array('value' => 'BTN', 'label' => Mage::helper('adminhtml')->__("Bhutanese ngultrum (BTN)")),
			array('value' => 'BWP', 'label' => Mage::helper('adminhtml')->__("Botswana pula (BWP)")),
			array('value' => 'BYN', 'label' => Mage::helper('adminhtml')->__("Belarusian ruble (BYN)")),
			array('value' => 'BZD', 'label' => Mage::helper('adminhtml')->__("Belize dollar (BZD)")),
			array('value' => 'CAD', 'label' => Mage::helper('adminhtml')->__("Canadian dollar (CAD)")),
			array('value' => 'CDF', 'label' => Mage::helper('adminhtml')->__("Congolese franc (CDF)")),
			array('value' => 'CHE', 'label' => Mage::helper('adminhtml')->__("WIR Euro (CHE)")),
			array('value' => 'CHF', 'label' => Mage::helper('adminhtml')->__("Swiss franc (CHF)")),
			array('value' => 'CHW', 'label' => Mage::helper('adminhtml')->__("WIR Franc (CHW)")),
			array('value' => 'CLF', 'label' => Mage::helper('adminhtml')->__("Unidad de Fomento (CLF)")),
			array('value' => 'CLP', 'label' => Mage::helper('adminhtml')->__("Chilean peso (CLP)")),
			array('value' => 'CNY', 'label' => Mage::helper('adminhtml')->__("Chinese yuan (CNY)")),
			array('value' => 'COP', 'label' => Mage::helper('adminhtml')->__("Colombian peso (COP)")),
			array('value' => 'COU', 'label' => Mage::helper('adminhtml')->__("Unidad de Valor Real (COU)")),
			array('value' => 'CRC', 'label' => Mage::helper('adminhtml')->__("Costa Rican colon (CRC)")),
			array('value' => 'CUC', 'label' => Mage::helper('adminhtml')->__("Cuban convertible peso (CUC)")),
			array('value' => 'CUP', 'label' => Mage::helper('adminhtml')->__("Cuban peso (CUP)")),
			array('value' => 'CVE', 'label' => Mage::helper('adminhtml')->__("Cape Verde escudo (CVE)")),
			array('value' => 'CZK', 'label' => Mage::helper('adminhtml')->__("Czech koruna (CZK)")),
			array('value' => 'DJF', 'label' => Mage::helper('adminhtml')->__("Djiboutian franc (DJF)")),
			array('value' => 'DKK', 'label' => Mage::helper('adminhtml')->__("Danish krone (DKK)")),
			array('value' => 'DOP', 'label' => Mage::helper('adminhtml')->__("Dominican peso (DOP)")),
			array('value' => 'DZD', 'label' => Mage::helper('adminhtml')->__("Algerian dinar (DZD)")),
			array('value' => 'EGP', 'label' => Mage::helper('adminhtml')->__("Egyptian pound (EGP)")),
			array('value' => 'ERN', 'label' => Mage::helper('adminhtml')->__("Eritrean nakfa (ERN)")),
			array('value' => 'ETB', 'label' => Mage::helper('adminhtml')->__("Ethiopian birr (ETB)")),
			array('value' => 'EUR', 'label' => Mage::helper('adminhtml')->__("Euro (EUR)")),
			array('value' => 'FJD', 'label' => Mage::helper('adminhtml')->__("Fiji dollar (FJD)")),
			array('value' => 'FKP', 'label' => Mage::helper('adminhtml')->__("Falkland Islands pound (FKP)")),
			array('value' => 'GBP', 'label' => Mage::helper('adminhtml')->__("Pound sterling (GBP)")),
			array('value' => 'GEL', 'label' => Mage::helper('adminhtml')->__("Georgian lari (GEL)")),
			array('value' => 'GHS', 'label' => Mage::helper('adminhtml')->__("Ghanaian cedi (GHS)")),
			array('value' => 'GIP', 'label' => Mage::helper('adminhtml')->__("Gibraltar pound (GIP)")),
			array('value' => 'GMD', 'label' => Mage::helper('adminhtml')->__("Gambian dalasi (GMD)")),
			array('value' => 'GNF', 'label' => Mage::helper('adminhtml')->__("Guinean franc (GNF)")),
			array('value' => 'GTQ', 'label' => Mage::helper('adminhtml')->__("Guatemalan quetzal (GTQ)")),
			array('value' => 'GYD', 'label' => Mage::helper('adminhtml')->__("Guyanese dollar (GYD)")),
			array('value' => 'HKD', 'label' => Mage::helper('adminhtml')->__("Hong Kong dollar (HKD)")),
			array('value' => 'HNL', 'label' => Mage::helper('adminhtml')->__("Honduran lempira (HNL)")),
			array('value' => 'HRK', 'label' => Mage::helper('adminhtml')->__("Croatian kuna (HRK)")),
			array('value' => 'HTG', 'label' => Mage::helper('adminhtml')->__("Haitian gourde (HTG)")),
			array('value' => 'HUF', 'label' => Mage::helper('adminhtml')->__("Hungarian forint (HUF)")),
			array('value' => 'IDR', 'label' => Mage::helper('adminhtml')->__("Indonesian rupiah (IDR)")),
			array('value' => 'ILS', 'label' => Mage::helper('adminhtml')->__("Israeli new shekel (ILS)")),
			array('value' => 'INR', 'label' => Mage::helper('adminhtml')->__("Indian rupee (INR)")),
			array('value' => 'IQD', 'label' => Mage::helper('adminhtml')->__("Iraqi dinar (IQD)")),
			array('value' => 'IRR', 'label' => Mage::helper('adminhtml')->__("Iranian rial (IRR)")),
			array('value' => 'ISK', 'label' => Mage::helper('adminhtml')->__("Icelandic króna (ISK)")),
			array('value' => 'JMD', 'label' => Mage::helper('adminhtml')->__("Jamaican dollar (JMD)")),
			array('value' => 'JOD', 'label' => Mage::helper('adminhtml')->__("Jordanian dinar (JOD)")),
			array('value' => 'JPY', 'label' => Mage::helper('adminhtml')->__("Japanese yen (JPY)")),
			array('value' => 'KES', 'label' => Mage::helper('adminhtml')->__("Kenyan shilling (KES)")),
			array('value' => 'KGS', 'label' => Mage::helper('adminhtml')->__("Kyrgyzstani som (KGS)")),
			array('value' => 'KHR', 'label' => Mage::helper('adminhtml')->__("Cambodian riel (KHR)")),
			array('value' => 'KMF', 'label' => Mage::helper('adminhtml')->__("Comoro franc (KMF)")),
			array('value' => 'KPW', 'label' => Mage::helper('adminhtml')->__("North Korean won (KPW)")),
			array('value' => 'KRW', 'label' => Mage::helper('adminhtml')->__("South Korean won (KRW)")),
			array('value' => 'KWD', 'label' => Mage::helper('adminhtml')->__("Kuwaiti dinar (KWD)")),
			array('value' => 'KYD', 'label' => Mage::helper('adminhtml')->__("Cayman Islands dollar (KYD)")),
			array('value' => 'KZT', 'label' => Mage::helper('adminhtml')->__("Kazakhstani tenge (KZT)")),
			array('value' => 'LAK', 'label' => Mage::helper('adminhtml')->__("Lao kip (LAK)")),
			array('value' => 'LBP', 'label' => Mage::helper('adminhtml')->__("Lebanese pound (LBP)")),
			array('value' => 'LKR', 'label' => Mage::helper('adminhtml')->__("Sri Lankan rupee (LKR)")),
			array('value' => 'LRD', 'label' => Mage::helper('adminhtml')->__("Liberian dollar (LRD)")),
			array('value' => 'LSL', 'label' => Mage::helper('adminhtml')->__("Lesotho loti (LSL)")),
			array('value' => 'LTL', 'label' => Mage::helper('adminhtml')->__("Lithuanian litas (LTL)")),
			array('value' => 'LVL', 'label' => Mage::helper('adminhtml')->__("Latvian lats (LVL)")),
			array('value' => 'LYD', 'label' => Mage::helper('adminhtml')->__("Libyan dinar (LYD)")),
			array('value' => 'MAD', 'label' => Mage::helper('adminhtml')->__("Moroccan dirham (MAD)")),
			array('value' => 'MDL', 'label' => Mage::helper('adminhtml')->__("Moldovan leu (MDL)")),
			array('value' => 'MGA', 'label' => Mage::helper('adminhtml')->__("Malagasy ariary (MGA)")),
			array('value' => 'MKD', 'label' => Mage::helper('adminhtml')->__("Macedonian denar (MKD)")),
			array('value' => 'MMK', 'label' => Mage::helper('adminhtml')->__("Myanma kyat (MMK)")),
			array('value' => 'MNT', 'label' => Mage::helper('adminhtml')->__("Mongolian tugrik (MNT)")),
			array('value' => 'MOP', 'label' => Mage::helper('adminhtml')->__("Macanese pataca (MOP)")),
			array('value' => 'MRO', 'label' => Mage::helper('adminhtml')->__("Mauritanian ouguiya (MRO)")),
			array('value' => 'MUR', 'label' => Mage::helper('adminhtml')->__("Mauritian rupee (MUR)")),
			array('value' => 'MVR', 'label' => Mage::helper('adminhtml')->__("Maldivian rufiyaa (MVR)")),
			array('value' => 'MWK', 'label' => Mage::helper('adminhtml')->__("Malawian kwacha (MWK)")),
			array('value' => 'MXN', 'label' => Mage::helper('adminhtml')->__("Mexican peso (MXN)")),
			array('value' => 'MXV', 'label' => Mage::helper('adminhtml')->__("Mexican Unidad de Inversion (MXV)")),
			array('value' => 'MYR', 'label' => Mage::helper('adminhtml')->__("Malaysian ringgit (MYR)")),
			array('value' => 'MZN', 'label' => Mage::helper('adminhtml')->__("Mozambican metical (MZN)")),
			array('value' => 'NAD', 'label' => Mage::helper('adminhtml')->__("Namibian dollar (NAD)")),
			array('value' => 'NGN', 'label' => Mage::helper('adminhtml')->__("Nigerian naira (NGN)")),
			array('value' => 'NIO', 'label' => Mage::helper('adminhtml')->__("Nicaraguan córdoba (NIO)")),
			array('value' => 'NOK', 'label' => Mage::helper('adminhtml')->__("Norwegian krone (NOK)")),
			array('value' => 'NPR', 'label' => Mage::helper('adminhtml')->__("Nepalese rupee (NPR)")),
			array('value' => 'NZD', 'label' => Mage::helper('adminhtml')->__("New Zealand dollar (NZD)")),
			array('value' => 'OMR', 'label' => Mage::helper('adminhtml')->__("Omani rial (OMR)")),
			array('value' => 'PAB', 'label' => Mage::helper('adminhtml')->__("Panamanian balboa (PAB)")),
			array('value' => 'PEN', 'label' => Mage::helper('adminhtml')->__("Peruvian nuevo sol (PEN)")),
			array('value' => 'PGK', 'label' => Mage::helper('adminhtml')->__("Papua New Guinean kina (PGK)")),
			array('value' => 'PHP', 'label' => Mage::helper('adminhtml')->__("Philippine peso (PHP)")),
			array('value' => 'PKR', 'label' => Mage::helper('adminhtml')->__("Pakistani rupee (PKR)")),
			array('value' => 'PLN', 'label' => Mage::helper('adminhtml')->__("Polish złoty (PLN)")),
			array('value' => 'PYG', 'label' => Mage::helper('adminhtml')->__("Paraguayan guaraní (PYG)")),
			array('value' => 'QAR', 'label' => Mage::helper('adminhtml')->__("Qatari riyal (QAR)")),
			array('value' => 'RON', 'label' => Mage::helper('adminhtml')->__("Romanian new leu (RON)")),
			array('value' => 'RSD', 'label' => Mage::helper('adminhtml')->__("Serbian dinar (RSD)")),
			array('value' => 'RUB', 'label' => Mage::helper('adminhtml')->__("Russian rouble (RUB)")),
			array('value' => 'RWF', 'label' => Mage::helper('adminhtml')->__("Rwandan franc (RWF)")),
			array('value' => 'SAR', 'label' => Mage::helper('adminhtml')->__("Saudi riyal (SAR)")),
			array('value' => 'SBD', 'label' => Mage::helper('adminhtml')->__("Solomon Islands dollar (SBD)")),
			array('value' => 'SCR', 'label' => Mage::helper('adminhtml')->__("Seychelles rupee (SCR)")),
			array('value' => 'SDG', 'label' => Mage::helper('adminhtml')->__("Sudanese pound (SDG)")),
			array('value' => 'SEK', 'label' => Mage::helper('adminhtml')->__("Swedish krona (SEK)")),
			array('value' => 'SGD', 'label' => Mage::helper('adminhtml')->__("Singapore dollar (SGD)")),
			array('value' => 'SHP', 'label' => Mage::helper('adminhtml')->__("Saint Helena pound (SHP)")),
			array('value' => 'SLL', 'label' => Mage::helper('adminhtml')->__("Sierra Leonean leone (SLL)")),
			array('value' => 'SOS', 'label' => Mage::helper('adminhtml')->__("Somali shilling (SOS)")),
			array('value' => 'SRD', 'label' => Mage::helper('adminhtml')->__("Surinamese dollar (SRD)")),
			array('value' => 'SSP', 'label' => Mage::helper('adminhtml')->__("South Sudanese pound (SSP)")),
			array('value' => 'STD', 'label' => Mage::helper('adminhtml')->__("São Tomé and Príncipe dobra (STD)")),
			array('value' => 'SYP', 'label' => Mage::helper('adminhtml')->__("Syrian pound (SYP)")),
			array('value' => 'SZL', 'label' => Mage::helper('adminhtml')->__("Swazi lilangeni (SZL)")),
			array('value' => 'THB', 'label' => Mage::helper('adminhtml')->__("Thai baht (THB)")),
			array('value' => 'TJS', 'label' => Mage::helper('adminhtml')->__("Tajikistani somoni (TJS)")),
			array('value' => 'TMT', 'label' => Mage::helper('adminhtml')->__("Turkmenistani manat (TMT)")),
			array('value' => 'TND', 'label' => Mage::helper('adminhtml')->__("Tunisian dinar (TND)")),
			array('value' => 'TOP', 'label' => Mage::helper('adminhtml')->__("Tongan paʻanga (TOP)")),
			array('value' => 'TRY', 'label' => Mage::helper('adminhtml')->__("Turkish lira (TRY)")),
			array('value' => 'TTD', 'label' => Mage::helper('adminhtml')->__("Trinidad and Tobago dollar (TTD)")),
			array('value' => 'TWD', 'label' => Mage::helper('adminhtml')->__("New Taiwan dollar (TWD)")),
			array('value' => 'TZS', 'label' => Mage::helper('adminhtml')->__("Tanzanian shilling (TZS)")),
			array('value' => 'UAH', 'label' => Mage::helper('adminhtml')->__("Ukrainian hryvnia (UAH)")),
			array('value' => 'UGX', 'label' => Mage::helper('adminhtml')->__("Ugandan shilling (UGX)")),
			array('value' => 'USD', 'label' => Mage::helper('adminhtml')->__("United States dollar (USD)")),
			array('value' => 'USN', 'label' => Mage::helper('adminhtml')->__("United States dollar (USN)")),
			array('value' => 'USS', 'label' => Mage::helper('adminhtml')->__("United States dollar  (USS)")),
			array('value' => 'UYI', 'label' => Mage::helper('adminhtml')->__("Uruguay Peso en Unidades Indexadas (UYI)")),
			array('value' => 'UYU', 'label' => Mage::helper('adminhtml')->__("Uruguayan peso (UYU)")),
			array('value' => 'UZS', 'label' => Mage::helper('adminhtml')->__("Uzbekistan som (UZS)")),
			array('value' => 'VEF', 'label' => Mage::helper('adminhtml')->__("Venezuelan bolívar fuerte (VEF)")),
			array('value' => 'VND', 'label' => Mage::helper('adminhtml')->__("Vietnamese dong (VND)")),
			array('value' => 'VUV', 'label' => Mage::helper('adminhtml')->__("Vanuatu vatu (VUV)")),
			array('value' => 'WST', 'label' => Mage::helper('adminhtml')->__("Samoan tala (WST)")),
			array('value' => 'XAF', 'label' => Mage::helper('adminhtml')->__("CFA franc BEAC (XAF)")),
			array('value' => 'XBA', 'label' => Mage::helper('adminhtml')->__("European Composite Unit (XBA)")),
			array('value' => 'XBB', 'label' => Mage::helper('adminhtml')->__("European Monetary Unit (XBB)")),
			array('value' => 'XBC', 'label' => Mage::helper('adminhtml')->__("European Unit of Account 9 (XBC)")),
			array('value' => 'XBD', 'label' => Mage::helper('adminhtml')->__("European Unit of Account 17 (XBD)")),
			array('value' => 'XCD', 'label' => Mage::helper('adminhtml')->__("East Caribbean dollar (XCD)")),
			array('value' => 'XDR', 'label' => Mage::helper('adminhtml')->__("Special drawing rights (XDR)")),
			array('value' => 'XFU', 'label' => Mage::helper('adminhtml')->__("UIC franc (XFU)")),
			array('value' => 'XOF', 'label' => Mage::helper('adminhtml')->__("CFA franc BCEAO (XOF)")),
			array('value' => 'XPF', 'label' => Mage::helper('adminhtml')->__("CFP franc (XPF)")),
			array('value' => 'YER', 'label' => Mage::helper('adminhtml')->__("Yemeni rial (YER)")),
			array('value' => 'ZAR', 'label' => Mage::helper('adminhtml')->__("South African rand (ZAR)")),
			array('value' => 'ZMW', 'label' => Mage::helper('adminhtml')->__("Zambian kwacha (ZMW)"))
		);
		return $options;
	}
}
