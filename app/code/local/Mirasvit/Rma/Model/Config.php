<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   RMA
 * @version   2.1.0-beta
 * @build     1359
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Rma_Model_Config
{
    const FIELD_TYPE_TEXT = 'text';
    const FIELD_TYPE_TEXTAREA = 'textarea';
    const FIELD_TYPE_DATE = 'date';
    const FIELD_TYPE_CHECKBOX = 'checkbox';
    const FIELD_TYPE_SELECT = 'select';
    const RULE_EVENT_RMA_CREATED = 'rma_created';
    const RULE_EVENT_RMA_UPDATED = 'rma_updated';
    const RULE_EVENT_NEW_CUSTOMER_REPLY = 'new_customer_reply';
    const RULE_EVENT_NEW_STAFF_REPLY = 'new_staff_reply';
    const IS_RESOLVED_0 = 0;
    const IS_RESOLVED_1 = 1;
    const IS_RESOLVED_2 = 2;

    const CUSTOMER = 1;
    const USER = 2;

    const COMMENT_PUBLIC = 'public';
    const COMMENT_INTERNAL = 'internal';

    const RMA_CUSTOMER_REQUIRES_REASON = 'reason';
    const RMA_CUSTOMER_REQUIRES_CONDITION = 'condition';
    const RMA_CUSTOMER_REQUIRES_RESOLUTION = 'resolution';

    const RMA_GRID_COLUMNS_INCREMENT_ID = 'increment_id';
    const RMA_GRID_COLUMNS_ORDER_INCREMENT_ID = 'order_increment_id';
    const RMA_GRID_COLUMNS_CUSTOMER_EMAIL = 'customer_email';
    const RMA_GRID_COLUMNS_CUSTOMER_NAME = 'customer_name';
    const RMA_GRID_COLUMNS_USER_ID = 'user_id';
    const RMA_GRID_COLUMNS_LAST_REPLY_NAME = 'last_reply_name';
    const RMA_GRID_COLUMNS_STATUS_ID = 'status_id';
    const RMA_GRID_COLUMNS_STORE_ID = 'store_id';
    const RMA_GRID_COLUMNS_CREATED_AT = 'created_at';
    const RMA_GRID_COLUMNS_UPDATED_AT = 'updated_at';
    const RMA_GRID_COLUMNS_ACTION = 'action';
    const RMA_GRID_COLUMNS_ITEMS = 'items';

    const FEDEX_METHOD_EUROPE_FIRST_INTERNATIONAL_PRIORITY = 'europe_first_international_priority';
    const FEDEX_METHOD_FEDEX_1_DAY_FREIGHT = 'fedex_1_day_freight';
    const FEDEX_METHOD_FEDEX_2_DAY = 'fedex_2_day';
    const FEDEX_METHOD_FEDEX_2_DAY_FREIGHT = 'fedex_2_day_freight';
    const FEDEX_METHOD_FEDEX_3_DAY_FREIGHT = 'fedex_3_day_freight';
    const FEDEX_METHOD_FEDEX_EXPRESS_SAVER = 'fedex_express_saver';
    const FEDEX_METHOD_FEDEX_GROUND = 'fedex_ground';
    const FEDEX_METHOD_FIRST_OVERNIGHT = 'first_overnight';
    const FEDEX_METHOD_GROUND_HOME_DELIVERY = 'ground_home_delivery';
    const FEDEX_METHOD_INTERNATIONAL_ECONOMY = 'international_economy';
    const FEDEX_METHOD_INTERNATIONAL_ECONOMY_FREIGHT = 'international_economy_freight';
    const FEDEX_METHOD_INTERNATIONAL_FIRST = 'international_first';
    const FEDEX_METHOD_INTERNATIONAL_GROUND = 'international_ground';
    const FEDEX_METHOD_INTERNATIONAL_PRIORITY = 'international_priority';
    const FEDEX_METHOD_INTERNATIONAL_PRIORITY_FREIGHT = 'international_priority_freight';
    const FEDEX_METHOD_PRIORITY_OVERNIGHT = 'priority_overnight';
    const FEDEX_METHOD_SMART_POST = 'smart_post';
    const FEDEX_METHOD_STANDARD_OVERNIGHT = 'standard_overnight';

    const FEDEX_SHIPPING_CHARGE_PAYS_SENDER = 'SENDER';
    const FEDEX_SHIPPING_CHARGE_PAYS_RECIPIENT = 'RECIPIENT';

    const FEDEX_SMARTPOST_INDICIA_MEDIAMAIL = 'MEDIA_MAIL';
    const FEDEX_SMARTPOST_INDICIA_PARCEL = 'PARCEL_SELECT';
    const FEDEX_SMARTPOST_INDICIA_PRESORTED = 'PRESORTED_STANDARD';
    const FEDEX_SMARTPOST_INDICIA_PRESORTED_BOUND = 'PRESORTED_BOUND_PRINTED_MATTER';

    /**
     * @param Mage_Core_Model_Store|int $store
     *
     * @return string
     */
    public function getGeneralReturnAddress($store = null)
    {
        return Mage::getStoreConfig('rma/general/return_address', $store);
    }

    /**
     * @param Mage_Core_Model_Store|int $store
     *
     * @return int
     */
    public function getGeneralDefaultStatus($store = null)
    {
        return Mage::getStoreConfig('rma/general/default_status', $store);
    }

    /**
     * @param Mage_Core_Model_Store|int $store
     *
     * @return int
     */
    public function getGeneralDefaultUser($store = null)
    {
        return Mage::getStoreConfig('rma/general/default_user', $store);
    }

    /**
     * @param Mage_Core_Model_Store|int $store
     *
     * @return int
     */
    public function getGeneralIsRequireShippingConfirmation($store = null)
    {
        return Mage::getStoreConfig('rma/general/is_require_shipping_confirmation', $store);
    }

    /**
     * @param Mage_Core_Model_Store|int $store
     *
     * @return string
     */
    public function getGeneralShippingConfirmationText($store = null)
    {
        return Mage::getStoreConfig('rma/general/shipping_confirmation_text', $store);
    }

    /**
     * @param Mage_Core_Model_Store|int $store
     *
     * @return bool
     */
    public function getGeneralIsGiftActive($store = null)
    {
        return Mage::getStoreConfig('rma/general/is_gift_active', $store);
    }

    /**
     * @param Mage_Core_Model_Store|int $store
     *
     * @return bool
     */
    public function getGeneralIsHelpdeskActive($store = null)
    {
        return Mage::getStoreConfig('rma/general/is_helpdesk_active', $store);
    }

    /**
     * @param Mage_Core_Model_Store|int $store
     *
     * @return string
     */
    public function getGeneralBrandAttribute($store = null)
    {
        return Mage::getStoreConfig('rma/general/brand_attribute', $store);
    }

    /**
     * @param Mage_Core_Model_Store|int $store
     *
     * @return array
     */
    public function getGeneralFileAllowedExtensions($store = null)
    {
        if (!$extensions = Mage::getStoreConfig('rma/general/file_allowed_extensions', $store)) {
            return array();
        }
        $extensions = explode(',', $extensions);
        $extensions = array_map('trim', $extensions);

        return $extensions;
    }

    public function getGeneralFileSizeLimit($store = null)
    {
        return Mage::getStoreConfig('rma/general/file_size_limit', $store);
    }

    public function getGeneralRmaGridColumns($store = null)
    {
        $value = Mage::getStoreConfig('rma/general/rma_grid_columns', $store);

        return explode(',', $value);
    }

    public function getGeneralIsOfflineOrdersAllowed($store = null)
    {
        return Mage::getStoreConfig('rma/general/is_allow_offline_orders', $store);
    }

    public function getGeneralIsAdditionalStepAllowed($store = null)
    {
        return (int)$this->getGeneralShippingStepBlock($store) > 0;
    }

    public function getGeneralShippingStepBlock($store = null)
    {
        return Mage::getStoreConfig('rma/general/rma_shipping_step_cms_block', $store);
    }

    public function getGeneralSuccessStepBlock($store = null)
    {
        return Mage::getStoreConfig('rma/general/rma_success_cms_block', $store);
    }

    public function getGeneralCustomerRequirement($store = null)
    {
        return Mage::getStoreConfig('rma/general/rma_customer_requirement', $store);
    }

    public function isCustomerReasonRequired($store = null)
    {
        $config = $this->getGeneralCustomerRequirement($store);
        $data = explode(',', $config);

        return in_array('reason', $data);
    }

    public function isCustomerConditionRequired($store = null)
    {
        $config = $this->getGeneralCustomerRequirement($store);
        $data = explode(',', $config);

        return in_array('condition', $data);
    }

    public function isCustomerResolutionRequired($store = null)
    {
        $config = $this->getGeneralCustomerRequirement($store);
        $data = explode(',', $config);

        return in_array('resolution', $data);
    }

    public function getFedexFedexEnable($store = null)
    {
        return Mage::getStoreConfig('rma/fedex/fedex_enable', $store);
    }

    public function getFedexFedexMethod($store = null)
    {
        return Mage::getStoreConfig('rma/fedex/fedex_method', $store);
    }

    public function getFedexFedexReference($store = null)
    {
        return Mage::getStoreConfig('rma/fedex/fedex_reference', $store);
    }

    public function getFedexStorePerson($store = null)
    {
        return Mage::getStoreConfig('rma/fedex/store_person', $store);
    }

    public function getFedexStoreAddressLine1($store = null)
    {
        return Mage::getStoreConfig('rma/fedex/store_address_line1', $store);
    }

    public function getFedexStoreAddressLine2($store = null)
    {
        return Mage::getStoreConfig('rma/fedex/store_address_line2', $store);
    }

    public function getFedexStoreStateCode($store = null)
    {
        return Mage::getStoreConfig('rma/fedex/store_state_code', $store);
    }

    public function getFedexStoreCountry($store = null)
    {
        return Mage::getStoreConfig('rma/fedex/store_country', $store);
    }

    public function getFedexDefaultWeight($store = null)
    {
        return Mage::getStoreConfig('rma/fedex/fedex_default_weight', $store);
    }

    public function getFedexChargesPayor($store = null)
    {
        return Mage::getStoreConfig('rma/fedex/fedex_charges_payor', $store);
    }

    public function getFedexSmartPostIndicia($store = null)
    {
        return Mage::getStoreConfig('rma/fedex/fedex_smartpost_indicia', $store);
    }

    public function getFedexSmartPostHubId($store = null)
    {
        return Mage::getStoreConfig('rma/fedex/fedex_smartpost_hubid', $store);
    }

    public function getFrontendIsActive($store = null)
    {
        return Mage::getStoreConfig('rma/frontend/is_active', $store);
    }

    /**
     * @param Mage_Core_Model_Store|int $store
     *
     * @return int
     */
    public function getPolicyReturnPeriod($store = null)
    {
        return Mage::getStoreConfig('rma/policy/return_period', $store);
    }

    /**
     * @param Mage_Core_Model_Store|int $store
     *
     * @return array
     */
    public function getPolicyAllowInStatuses($store = null)
    {
        $value = Mage::getStoreConfig('rma/policy/allow_in_statuses', $store);

        return explode(',', $value);
    }

    /**
     * @param Mage_Core_Model_Store|int $store
     *
     * @return string
     */
    public function getPolicyBundleOneByOne($store = null)
    {
        return Mage::getStoreConfig('rma/policy/bundle_one_by_one', $store);
    }

    /**
     * @param Mage_Core_Model_Store|int $store
     *
     * @return bool
     */
    public function getPolicyIsActive($store = null)
    {
        return Mage::getStoreConfig('rma/policy/is_active', $store);
    }

    /**
     * @param Mage_Core_Model_Store|int $store
     *
     * @return string
     */
    public function getPolicyPolicyBlock($store = null)
    {
        return Mage::getStoreConfig('rma/policy/policy_block', $store);
    }

    public function getNumberFormat($store = null)
    {
        return Mage::getStoreConfig('rma/number/format', $store);
    }

    public function getNumberCounterStart($store = null)
    {
        return Mage::getStoreConfig('rma/number/counter_start', $store);
    }

    public function getNumberCounterStep($store = null)
    {
        return Mage::getStoreConfig('rma/number/counter_step', $store);
    }

    public function getNumberCounterLength($store = null)
    {
        return Mage::getStoreConfig('rma/number/counter_length', $store);
    }

    public function getNotificationSenderEmail($store = null)
    {
        return Mage::getStoreConfig('rma/notification/sender_email', $store);
    }

    public function getNotificationCustomerEmailTemplate($store = null)
    {
        return Mage::getStoreConfig('rma/notification/customer_email_template', $store);
    }

    public function getNotificationAdminEmailTemplate($store = null)
    {
        return Mage::getStoreConfig('rma/notification/admin_email_template', $store);
    }

    public function getNotificationRuleTemplate($store = null)
    {
        return Mage::getStoreConfig('rma/notification/rule_template', $store);
    }

    /**
     * Returns setting "Send blind carbon copy (BCC) of all emails to" value.
     *
     * @param Mage_Core_Model_Store $store
     *
     * @return string
     */
    public function getNotificationSendEmailBcc($store = null)
    {
        return Mage::getStoreConfig('rma/notification/send_email_bcc', $store);
    }

    /************************/

    public function isActiveHelpdesk()
    {
        if ($this->getGeneralIsHelpdeskActive() && Mage::helper('mstcore')->isModuleInstalled('Mirasvit_Helpdesk')) {
            return true;
        }
    }
}
