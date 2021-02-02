<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */

class Xonu_Directdebit_Block_Adminhtml_Export_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm(){
		$form = new Varien_Data_Form(array(
			'id'		=> 'edit_form',
		));

		$form->setUseContainer(true);
		$this->setForm($form);
		return parent::_prepareForm();
	}
}