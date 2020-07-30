<?php

class SendCloud_Plugin_Adminhtml_AutoconnectController
    extends Mage_Adminhtml_Controller_Action
{
    const API_ROLE_NAME = 'SendCloud API';
    const API_USERNAME = 'sendcloud';
    private $_helper;

    public function _construct()
    {
        parent::_construct();
        $this->_helper = Mage::helper('SendCloud_Plugin');
    }

    protected function _isAllowed()
    {
        return parent::_isAllowed();
    }

    public function gotoAction()
    {
        Mage::app()->getResponse()->setBody($this->_helper->panelUrl());
    }

    public function connectAction()
    {
        $apiKey = $this->_generateApiKey();
        $apiUser = $this->_getOrCreateApiUser($apiKey);
        $siteUrl = $this->_helper->getBaseUrl();
        $url = sprintf(
            '%s/shops/magento_v1/connect/?shop_url=%s&username=%s&password=%s',
            $this->_helper->panelUrl(),
            urlencode($siteUrl),
            $apiUser->username,
            $apiKey
        );
        Mage::app()->getResponse()->setBody($url);
    }

    private function _generateApiKey()
    {
        return md5(rand() . time());
    }

    private function _getOrCreateApiRole()
    {
        $apiRoleModel = Mage::getModel('api/roles');
        $apiRole = $apiRoleModel->load(self::API_ROLE_NAME, 'role_name');

        if (!$apiRole->getId()) {
            $apiRole = $apiRoleModel
                ->setName('SendCloud API')
                ->setPid(false)
                ->setRoleType('G')
                ->save();
        }

        Mage::getModel('api/rules')
            ->setRoleId($apiRole->getId())
            ->setResources(array('all'))
            ->saveRel();

        return $apiRole;
    }

    private function _getOrCreateApiUser($apiKey)
    {
        $apiRole = $this->_getOrCreateApiRole();
        $apiUserModel = Mage::getModel('api/user');

        $apiUser = $apiUserModel->loadByUsername(self::API_USERNAME);

        if (!$apiUser->getId()) {
            $apiUser = $apiUserModel->setData(array(
                'username' => self::API_USERNAME,
                'firstname' => 'SendCloud',
                'lastname' => 'API',
                'email' => 'contact@sendcloud.sc',
                'api_key' => $apiKey,
                'api_key_confirmation' => $apiKey,
                'is_active' => 1,
                'user_roles' => '',
                'assigned_user_role' => '',
                'role_name' => '',
                'roles' => array($apiRole->getId()),
            ));
        } else {
            $apiUser->setIsActive(1);
            $apiUser->setApiKey($apiKey);
        }

        $apiUser->save();

        $apiUser
            ->setRoleIds(array($apiRole->getId()))
            ->setRoleUserId($apiUser->getUserId())
            ->saveRelations();
        return $apiUser;
    }
}
