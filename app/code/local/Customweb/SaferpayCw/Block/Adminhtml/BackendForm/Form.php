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

class Customweb_SaferpayCw_Block_Adminhtml_BackendForm_Form extends Mage_Adminhtml_Block_Template
{
	private $form = null;
	
	public function setForm(Customweb_Form $form)
	{
		$this->form = $form;
		return $this;
	}
	
    protected function _toHtml()
    {
    	return Mage::getModel('saferpaycw/backendFormRenderer')->renderForm($this->form);
    }
}