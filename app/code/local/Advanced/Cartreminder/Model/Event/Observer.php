<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Reports
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Reports Event observer model
 *
 * @category   Mage
 * @package    Mage_Reports
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Advanced_Cartreminder_Model_Event_Observer {

    /**
     * delete cart reminder
     *
     * @param Varien_Event_Observer $observer
     * @return sales_quote_delete_after
     */
    public function quoteDeleteAfter(Varien_Event_Observer $observer) {
        $storeId = Mage::app()->getStore()->getStoreId();
        
        $enable = Mage::getStoreConfig('cartreminder/reminder/enable', $storeId);
        if(!$enable)
            return;
        
        $quote = $observer->getEvent()->getQuote();
        $cartReminder =  Mage::getModel('cartreminder/reminder')->load($quote->getId(),'quote_id');
        if($cartReminder->getId()){
            try{
                $cartReminder->delete();
            }catch(Exception $e){
                
            }
        }
    }
    
    /**
     * Add product to shopping cart action
     *
     * @param Varien_Event_Observer $observer
     * @return sales_quote_save_after
     */
    public function quoteSaveAfter(Varien_Event_Observer $observer) {
        $storeId = Mage::app()->getStore()->getStoreId();
        
        $enable = Mage::getStoreConfig('cartreminder/reminder/enable', $storeId);
        if(!$enable)
            return;
        
        $quote = $observer->getEvent()->getQuote();
        if (!$quote->getIsActive())
            return;
        if (!$quote->getCustomerEmail())
            return;
        $cartreminder = Mage::getModel('cartreminder/reminder')->load($quote->getId(), 'quote_id');
        if ($cartreminder->getId()) {
            return;
        } else {
            try {
                $key = Mage::helper('cartreminder')->generateLicense();

                Mage::getModel('cartreminder/reminder')
                        ->setData('quote_id', $quote->getId())
                        ->setData('number_of_reminder', 0)
                        ->setData('reminder_time', now())
                        ->setData('key', $key)
                        ->save();
            } catch (Exception $e) {
                
            }
        }
    }

    /**
     * send email for cart reminder
     *
     * @param Varien_Event_Observer $observer
     * @return Advanced_Cartreminder_Model_Event_Observer
     */
    public function reminder() {
        $storeId = Mage::app()->getStore()->getStoreId();
        
        $enable = Mage::getStoreConfig('cartreminder/reminder/enable', $storeId);
        if(!$enable)
            return;
        
        
        $timeConfig = explode(',', Mage::getStoreConfig('cartreminder/reminder/email_sent_after', $storeId));
        $maxReminder = Mage::getStoreConfig('cartreminder/reminder/number_of_reminder', $storeId);
        $customerGroup = explode(',', Mage::getStoreConfig('cartreminder/reminder/customer_group', $storeId));

        $day = 86400 * (int) $timeConfig[0];
        $hour = 3600 * (int) $timeConfig[1];
        $minute = 60 * (int) $timeConfig[2];
        $delayTime = $day + $hour + $minute;

        $quotes = Mage::getModel('sales/quote')->getCollection()
                ->addFieldToFilter('is_active', 1);

        $resource = Mage::getSingleton('core/resource');

        $quotes->getSelect()->join(array('reminder' => $resource->getTableName('cartreminder/reminder')), 'reminder.quote_id = main_table.entity_id', array('reminder.number_of_reminder', 'reminder.reminder_time', 'reminder.advanced_reminder_id'));


        foreach ($quotes as $quote) {
            $customerId = 0;
            if ($quote->getCustomerGroupId()) {
                $customerId = $quote->getCustomerGroupId();
            }

            if ($quote->getNumberOfReminder() >= $maxReminder) {
                continue;
            }

            foreach($customerGroup as $id => $value)
			{			
				if($customerId === $value){
					 continue;
				}
			}

            if ($quote->getNumberOfReminder() == 0) {
                $checkTime = $quote->getUpdatedAt();
            } else {
                $checkTime = $quote->getReminderTime();
            }

            if ((strtotime(now()) - strtotime($checkTime)) >= $delayTime) {
                //send email                
                $customerName = $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname();
                
                $reminder = Mage::getModel('cartreminder/reminder')->load($quote->getAdvancedReminderId());

                $link = Mage::getUrl('cartreminder/index/recover/',array('key'=> $reminder->getKey()));
                $items = $this->getItems($quote);
                $this->sendReminderEmail($quote->getCustomerEmail(), $customerName, $quote, $link, $items);


                $reminder->setNumberOfReminder($reminder->getNumberOfReminder() + 1)
                        ->setReminderTime(now())
                        ->save();                
            }
        }
    }

    public function getItems($quote) {        
        $items = $quote->getAllItems();
        $str = '';
        $str .= '<table cellspacing="0" cellpadding="0" border="0" width="100%">';
        $str .= '<tr>';
        $str .= '<th width="100" align="left">' . Mage::helper('cartreminder')->__('Image') . '</th>';
        $str .= '<th align="left">' . Mage::helper('cartreminder')->__('Product Name') . '</th>';
        $str .= '<th align="left">' . Mage::helper('cartreminder')->__('Qty') . '</th>';
        $str .= '</tr>';
        foreach ($items as $item):
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            if($product->getVisibility()==1)
		continue;
            $_img = '<img id="image" src="' . Mage::helper('catalog/image')->init($product, 'image')->resize(100) . '"  />';
            $str .= '<tr>';
            $str .= '<td width="100" align="left">' . $_img . '</td>';
            $str .= '<td align="left">' . $product->getName() . '</td>';
            $str .= '<td align="left">' . $item->getQty() . '</td>';
            $str .= '</tr>';
        endforeach;
        $str .= '</table>';
        return $str;
    }

    protected function sendReminderEmail($customerEmail, $customerName, $quote, $link, $items) {
        try {

            $storeId = Mage::app()->getStore()->getStoreId();
            $templateId = Mage::getStoreConfig('cartreminder/reminder/reminderemail', $storeId);

            $mailTemplate = Mage::getModel('core/email_template');
            $translate = Mage::getSingleton('core/translate');

            $emailType = Mage::getStoreConfig('cartreminder/reminder/sender_email', $storeId);
            $from_email = Mage::getStoreConfig('trans_email/ident_' . $emailType . '/email', $storeId);
            $from_name = Mage::getStoreConfig('trans_email/ident_' . $emailType . '/name', $storeId);

            $sender = array('email' => $from_email, 'name' => $from_name);
            $receipientEmail = $customerEmail;
            $receipientName = $customerName;

            $mailTemplate
                    ->setTemplateSubject(Mage::getStoreConfig('cartreminder/reminder/email_subject', $storeId))
                    ->sendTransactional(
                            $templateId, $sender, $receipientEmail, $receipientName, array(
                        'customer_name' => $receipientName,
                        'quote' => $quote,
                        'link' => $link,
                        'item' => $items
                            )
            );
            $translate->setTranslateInline(true);
        } catch (Exception $e) {
            
        }
    }

}
