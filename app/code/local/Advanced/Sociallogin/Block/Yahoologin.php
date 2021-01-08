<?php

class Advanced_Sociallogin_Block_Yahoologin extends Mage_Core_Block_Template {

    public function getLoginUrl() {
        return $this->getUrl('sociallogin/yahoologin/login');
    }

    protected function _beforeToHtml() {

        return parent::_beforeToHtml();
    }

}
