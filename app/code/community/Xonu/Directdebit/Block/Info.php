<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Block_Info extends Mage_Payment_Block_Info {

    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $info = $this->getInfo();
        $transport = new Varien_Object();
        $transport = parent::_prepareSpecificInformation($transport);


        $mandate = Mage::getModel('xonu_directdebit/mandate')->load($info->getSepaMandateId(), 'mandate_identifier');
        if($mandate->getMandateIdentifier() != '') {
            // saved mandate

            if($mandate->getAccountHolder() != '') $transport->addData(array(
                Mage::helper('xonu_directdebit')->__('Account Holder') => $mandate->getAccountHolder()
            ));

            if($mandate->getIban() != '') $transport->addData(array(
                Mage::helper('xonu_directdebit')->__('IBAN') => $mandate->getIban()
            ));

            if($mandate->getBic() != '') $transport->addData(array(
                Mage::helper('xonu_directdebit')->__('BIC') => $mandate->getBic()
            ));

            $transport->addData(array(
                Mage::helper('xonu_directdebit')->__('Mandate Identifier') => $mandate->getMandateIdentifier()
            ));

            $transport->addData(array(
                Mage::helper('xonu_directdebit')->__('Date of Granting') => $mandate->getLocalCreatedAt()
            ));
        } else {
            // new mandate, not yet saved

            $displayMode = (int)Mage::getStoreConfig('xonu_directdebit/mandate/account_display_mode');

            if($displayMode == Xonu_Directdebit_Model_Mandate::ACCOUNT_DISPLAY_MODE_PARTIAL
            || $displayMode == Xonu_Directdebit_Model_Mandate::ACCOUNT_DISPLAY_MODE_FULL) {


                if($info->getSepaHolder() != ''
                && $displayMode == Xonu_Directdebit_Model_Mandate::ACCOUNT_DISPLAY_MODE_FULL) $transport->addData(array(
                    Mage::helper('xonu_directdebit')->__('Account Holder') => $info->getSepaHolder()
                ));

                if($info->getSepaIban() != '') $transport->addData(array(
                    Mage::helper('xonu_directdebit')->__('IBAN') =>
                        ($displayMode == Xonu_Directdebit_Model_Mandate::ACCOUNT_DISPLAY_MODE_PARTIAL ?
                              str_repeat('* ', 10).''.substr($info->getSepaIban(), -3)
                            : $mandate->beautifyIban($info->getSepaIban())
                        )
                ));

                if($info->getSepaBic() != '') $transport->addData(array(
                    Mage::helper('xonu_directdebit')->__('SWIFT-BIC') => $info->getSepaBic()
                ));

            }

            if($info->getSepaMandateId() != '') $transport->addData(array(
                Mage::helper('xonu_directdebit')->__('Mandate Identifier') => $info->getSepaMandateId()
            ));

            $currentDate = Mage::helper('core')->formatDate($this->getData('created_at'), 'medium', false);
            $transport->addData(array(
                Mage::helper('xonu_directdebit')->__('Date of Granting') => $currentDate
            ));
        }

        return $transport;
    }
}