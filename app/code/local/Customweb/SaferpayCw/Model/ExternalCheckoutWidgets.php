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
 *
 * @category Customweb
 * @package Customweb_SaferpayCw
 *
 */

/**
 *
 * @author Simon Schurter
 */
class Customweb_SaferpayCw_Model_ExternalCheckoutWidgets {
	public function getWidgets()
    {
    	$lastException = null;
    	for ($i = 0; $i < 10; $i++) {
    		try {
		    	$widgets = array();

		    	
		    	$quote = Mage::getSingleton('checkout/session')->getQuote();

		    	/* @var $context Customweb_SaferpayCw_Model_ExternalCheckoutContext */
		    	$context = Mage::getModel('saferpaycw/externalCheckoutContext')->loadByQuote($quote);
		    	if ($context === null || !$context->getId() || $context->getState() != Customweb_Payment_ExternalCheckout_IContext::STATE_PENDING) {
		    		$context = Mage::getModel('saferpaycw/externalCheckoutContext');
		    		$context->setQuoteId($quote->getId());
		    		$context->save();
		    	}

		    	$providerService = Mage::helper('SaferpayCw')->createContainer()->getBean('Customweb_Payment_ExternalCheckout_IProviderService');
		    	if (!($providerService instanceof Customweb_Payment_ExternalCheckout_IProviderService)) {
		    		throw new Customweb_Core_Exception_CastException('Customweb_Payment_ExternalCheckout_IProviderService');
		    	}

		    	$context->updateFromQuote($quote);
		    	$context->setState(Customweb_Payment_ExternalCheckout_IContext::STATE_PENDING);

				$checkouts = $providerService->getCheckouts($context);

		    	foreach ($checkouts as $checkout) {
					$widgets[] = array(
						'html' => $providerService->getWidgetHtml($checkout, $context),
						'checkoutName' => $checkout->getMachineName(),
						'sortOrder' => $checkout->getSortOrder()
					);
				}

				$context->save();
		    	

				return $widgets;
			} catch (Customweb_SaferpayCw_Model_OptimisticLockingException $e) {
				// Try again.
				$lastException = $e;
			}
		}
		throw $lastException;
    }

    public function getAllWidgets()
    {
    	if (Mage::registry('customweb_externalcheckout_widgets_collected') == null) {
    		Mage::register('customweb_externalcheckout_widgets_collected', true);

    		$widgets = array();
    		Mage::dispatchEvent('customweb_externalcheckout_widgets_collect', array(
                'widgets' => &$widgets,
            ));

    		usort($widgets, array($this, 'sort'));
    		return $widgets;
    	} else {
    		return array();
    	}
    }

    public function sort($a, $b){
    	if ($a['sortOrder'] == $b['sortOrder']) {
   			return 0;
  		} else {
    		return $a['sortOrder'] < $b['sortOrder'] ? -1 : 1;
    	}
   	}
}
