<?php
class BPMedia_GDPR_Model_Observer
{
    /**
     * Add buttons to the customer edit page showing the delete and e-mail options
     *
     * @param $observer
     */
    public function addAnonymiseButton($observer) {
        if ($observer->getBlock()->getType() != 'adminhtml/customer_edit') {
            return;
        }

        if (!Mage::helper('bpmedia_gdpr')->isEnabled()) {
            return;
        }

        $customer = Mage::registry('current_customer');
        if (!$customer->getId()) {
            return;
        }

        // Delete link
        $location = Mage::helper('adminhtml')->getUrl(
            'adminhtml/bpmedia_gdpr/deletecustomer',
            array('id' => $customer->getId())
        );
        $data = array(
            'label' => 'Delete Customer and Anonymise Data',
            'onclick' => 'setLocation(\''.$location.'\')',
        );
        $observer->getBlock()->addButton('anonymise_button', $data);

        // E-mail link
        $location = Mage::helper('adminhtml')->getUrl(
            'adminhtml/bpmedia_gdpr/sendAnonymiseEmail',
            array('id' => $customer->getId())
        );
        $data = array(
            'label' => 'Send Anonymisation Email',
            'onclick' => 'setLocation(\''.$location.'\')',
        );
        $observer->getBlock()->addButton('anonymisation_email_button', $data);
    }
}