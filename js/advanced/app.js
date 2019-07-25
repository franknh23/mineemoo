/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var AdvancedSocialLogin = Class.create({
    initialize: function (options) {
        this.options = options;

        this.alert_error = $('advanced-sociallogin-popup-alert-error');

        this.login_form = $('form-login-validate');
        this.login_button = $('advanced-social-login-button');
        this.button_forgot_your_password = $('advanced-button-forgot-your-password');
        this.button_back_login = $('advanced-button-back-login');

        this.login_forgot_password = $('form-forgot-password-validate');
        this.forgot_password_button = $('advanced-social-forgot-password-button');

        this.login_form_register = $('form-forgot-register-validate');
        this.create_account_button = $('advanced-social-create-account-button');
        this.button_create_account = $('advanced-button-create-account');
        this.button_create_back_login = $('advanced-social-create-button-back-login');


        this.bindEventHandlers();
    },
    login_handler: function (e) {
        Event.stop(e);
        $('advanced-social-login-button').disable();
        var login_validator = new Validation('form-login-validate');
        if (login_validator.validate()) {
            var parameters = this.login_form.serialize(true);
            var url = this.options.login_handler_url;
            $('close-login').hide();
            $('load-login').show();
            //this.showLoginLoading();
            new Ajax.Request(url, {
                method: 'post',
                parameters: parameters,
                onSuccess: function (transport) {
                    var result = transport.responseText.evalJSON();
                    //this.hideLoginLoading();
                    if (result.success) {
                        $_adj.advancedfancybox.close();
                        var parentElement = $_adj('#oscpage').parent();
                        parentElement[0].innerHTML = result.html;
                        try {
                            var scripts = result.html.extractScripts();
                            for (var i = 0; i < scripts.length; i++) {
                                var script = scripts[i];
                                var headDoc = $$('head').first();
                                var jsElement = new Element('script');
                                jsElement.type = 'text/javascript';
                                jsElement.text = script;
                                headDoc.appendChild(jsElement);
                            }
                        } catch (e) {
                            console.log(e);
                        }
                        
                    } else {
                        this.alert_error.update('<ul><li><span>' + result.error + '</span></li></ul>');
                        $('close-login').show();
                        $('load-login').hide();
                    }
                    if($('advanced-social-login-button'))
                        $('advanced-social-login-button').enable();
                }.bind(this)
            });
        }else{
            if($('advanced-social-login-button'))
                $('advanced-social-login-button').enable();
        }
    },
    forgot_password_handler: function (e) {
        Event.stop(e);
        var form = new Validation('form-forgot-password-validate');
        if (form.validate()) {
            var parameters = this.login_forgot_password.serialize(true);
            var url = this.options.forgot_password_handler_url;
            
            $('close-forgot-password').hide();
            $('load-forgot-password').show();
            new Ajax.Request(url, {
                method: 'post',
                parameters: parameters,
                onSuccess: function (transport) {
                    var result = transport.responseText.evalJSON();
                    if (result.success) {
                        location.reload();
                    } else {
                        $('advanced-sociallogin-forgot-password-alert-error').update('<ul><li><span>' + result.error + '</span></li></ul>');
                        $('close-forgot-password').show();
                        $('load-forgot-password').hide();
                    }
                }.bind(this)
            });
        }
    },
    create_account_handler: function (e) {
        Event.stop(e);
        var form = new Validation('form-forgot-register-validate');
        if (form.validate()) {
            var parameters = this.login_form_register.serialize(true);
            var url = this.options.create_account_handler_url;

            //this.showLoginLoading();
            new Ajax.Request(url, {
                method: 'post',
                parameters: parameters,
                onSuccess: function (transport) {
                    var result = transport.responseText.evalJSON();

                    //this.hideLoginLoading();
                    if (result.success) {
                        location.reload();
                    } else {
                        this.alert_error.update('<ul><li><span>' + result.error + '</span></li></ul>');
                    }
                }.bind(this)
            });
        }
    },
    button_forgot_your_password_handler: function () {
        this.login_form.hide();
        this.login_forgot_password.show();
        this.login_form_register.hide();
    },
    button_back_login_handler: function () {
        this.login_form.show();
        this.login_forgot_password.hide();
        this.login_form_register.hide();
    },
    button_create_account_handler: function() {
        this.login_form.hide();
        this.login_forgot_password.hide();
        this.login_form_register.show();
    },
    bindEventHandlers: function () {
        if (this.login_button) {
            this.login_button.observe('click', this.login_handler.bind(this));
        }
        if (this.forgot_password_button) {
            this.forgot_password_button.observe('click', this.forgot_password_handler.bind(this));
        }
        if (this.create_account_button) {
            this.create_account_button.observe('click', this.create_account_handler.bind(this));
        }
        if (this.button_forgot_your_password) {
            this.button_forgot_your_password.observe('click', this.button_forgot_your_password_handler.bind(this));
        }
        if (this.button_back_login) {
            this.button_back_login.observe('click', this.button_back_login_handler.bind(this));
        }

        if (this.button_create_account) {
            this.button_create_account.observe('click', this.button_create_account_handler.bind(this));
        }
        
        if (this.button_create_back_login) {
            this.button_create_back_login.observe('click', this.button_back_login_handler.bind(this));
        }
    }
});

