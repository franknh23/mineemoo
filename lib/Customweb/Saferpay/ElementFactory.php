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

//require_once 'Customweb/Form/Element.php';
//require_once 'Customweb/Form/Validator/NotEmpty.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Form/Control/Select.php';

/**
 * This class provides method for creating elements that are used by Saferpay.
 *
 * @author Severin Klinglerl
 *      	    	 			    	 
 */
final class Customweb_Saferpay_ElementFactory{
	
	
	public static function getGenderElement($fieldName){
		$gender = array(
				'none' => Customweb_I18n_Translation::__('Gender'),
				'male' => Customweb_I18n_Translation::__('Male'),
				'female' => Customweb_I18n_Translation::__('Female'), 
		);
		
		$control = new Customweb_Form_Control_Select($fieldName, $gender);
		$validator = new Customweb_Form_Validator_NotEmpty($control, Customweb_I18n_Translation::__('Please choose your gender'));
		$control->addValidator($validator);
		$element = new Customweb_Form_Element(
				Customweb_I18n_Translation::__('Gender'),
				$control,
				Customweb_I18n_Translation::__('Select your gender.')
		);
		
		return $element;
	}
	
	public static function getLEgalFormElement($fieldName){
		$forms = array(
			'none' => Customweb_I18n_Translation::__('Legalform'),
			'AG' => Customweb_I18n_Translation::__('Ltd.'),
			'GmbH' => Customweb_I18n_Translation::__('LLC'),
			'Misc' => Customweb_I18n_Translation::__('Other'),
		);
	
		$control = new Customweb_Form_Control_Select($fieldName, $forms);
		$validator = new Customweb_Form_Validator_NotEmpty($control, Customweb_I18n_Translation::__('Please choose the legal form of your company.'));
		$control->addValidator($validator);
		$element = new Customweb_Form_Element(
				Customweb_I18n_Translation::__('Legal form'),
				$control,
				Customweb_I18n_Translation::__('Select your companys legal form.')
				);
	
		return $element;
	}
	
	
}