<?php

class Advanced_Sociallogin_Block_Twitterlogin extends Mage_Core_Block_Template {

    public function getLoginUrl() {
        return $this->getUrl('sociallogin/twitterlogin/login');
    }

    protected function _beforeToHtml() {

        return parent::_beforeToHtml();
    }

}
