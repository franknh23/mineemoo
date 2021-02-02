<?php
/**
 * @package Xonu_Directdebit
 * @copyright 2016 Pawel Kazakow, xonu EEC, http://www.xonu.de
 * @author Pawel Kazakow <support@xonu.de>
 * @license xonu EEC EULA, http://xonu.de/license
 *
 */
class Xonu_Directdebit_Model_Setup extends Mage_Core_Model_Resource_Setup {
    /**
     * Save configuration data
     *
     * @param string $path
     * @param string $value
     * @param int|string $scope
     * @param int $scopeId
     * @param int $inherit
     * @return Mage_Core_Model_Resource_Setup
     */
    public function setIgnoreConfigData($path, $value, $scope = 'default', $scopeId = 0, $inherit=0)
    {
        $table = $this->getTable('core/config_data');
        // this is a fix for mysql 4.1
        $this->getConnection()->showTableStatus($table);

        $data  = array(
            'scope'     => $scope,
            'scope_id'  => $scopeId,
            'path'      => $path,
            'value'     => $value
        );
        // $this->getConnection()->insertOnDuplicate($table, $data, array('value'));

        // insert ignore ...
        try { $this->getConnection()->insert($table, $data, array('value')); } catch (Zend_Db_Exception $e) {}
        return $this;
    }


    /**
     * setup terms and conditions for the mandate agreement
     */
    public function setupAgreement() {
        // enable terms and conditions
        $this->setConfigData('checkout/options/enable_agreements', true);

        // disable the mandate agreement from Xonu_Directdebit 1.x
        if($mandateAgreementId = Mage::getStoreConfig('xonu_directdebit/mandate/mandate_terms')) {
            $agreement = Mage::getModel('checkout/agreement')->load($mandateAgreementId);
            $agreement->setIsActive(false);
            $agreement->save();
        }

        // create new mandate agreement
        $agreement = Mage::getModel('checkout/agreement');
        $agreement->setData(array(
            'content' => '{{var mandate_content}}',
            'checkbox_text' => '{{var mandate_checkbox}}',
            'name' => 'SEPA Direct Debit Mandate Preview',
            'is_active' => true,
            'is_html' => true,
            'is_required' => true,
            'stores' => array(0)
        ));
        $agreement->save();

        $this->setConfigData('xonu_directdebit/mandate/mandate_terms', $agreement->getId());
        $this->setConfigData('xonu_directdebit/mandate/mandate_terms_active', true);
    }

}