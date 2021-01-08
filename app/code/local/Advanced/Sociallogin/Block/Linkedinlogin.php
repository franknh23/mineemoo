<?php

class Advanced_Sociallogin_Block_Linkedinlogin extends Mage_Core_Block_Template {

    public function getLoginUrl() {
        return $this->getUrl('sociallogin/linkedinlogin/login');
    }

    protected function _beforeToHtml() {

        return parent::_beforeToHtml();
    }

}
