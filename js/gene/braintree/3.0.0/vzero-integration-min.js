var vZeroIntegration=Class.create();vZeroIntegration.prototype={dataCollectorInstance:null,initialize:function(vzero,vzeroPaypal,paypalWrapperMarkUp,paypalButtonClass,isOnepage,config,submitAfterPayment){return vZeroIntegration.prototype.loaded?(console.error("Your checkout is including the Braintree resources multiple times, please resolve this."),!1):(vZeroIntegration.prototype.loaded=!0,this.vzero=vzero||!1,this.vzeroPaypal=vzeroPaypal||!1,!1===this.vzero&&!1===this.vzeroPaypal?(console.warn("The vzero and vzeroPaypal objects are not initiated."),!1):(this.paypalWrapperMarkUp=paypalWrapperMarkUp||!1,this.paypalButtonClass=paypalButtonClass||!1,this.submitButtonClass=this.paypalButtonClass,this.isOnepage=isOnepage||!1,this.config=config||{},this.submitAfterPayment=submitAfterPayment||!1,this._methodSwitchTimeout=!1,this._originalSubmitFn=!1,this.kountEnvironment=!1,this.kountId=!1,document.observe("dom:loaded",function(){this.captureOriginalSubmitFn()&&this.observeSubmissionOverride(),this.prepareSubmitObserver(),this.preparePaymentMethodSwitchObserver()}.bind(this)),this.hostedFieldsGenerated=!1,this.isOnepage&&(this.observeAjaxRequests(),document.observe("dom:loaded",function(){this.initSavedPayPal(),this.initDefaultMethod(),null!==$("braintree-hosted-submit")&&this.initHostedFields()}.bind(this))),document.observe("dom:loaded",function(){this.initSavedMethods(),null!==$("braintree-hosted-submit")&&this.initHostedFields()}.bind(this)),this._deviceDataInit=!1,this.vzero.observeEvent(["onHandleAjaxRequest","integration.onInitSavedMethods"],this.initDeviceData,this),this.vzero.observeEvent("integration.onBeforeSubmit",function(){null!=$("braintree-device-data")&&$("braintree-device-data").writeAttribute("disabled",!1)},this),void this.vzero.fireEvent(this,"integration.onInit",{integration:this})))},initDeviceData:function(params,self){if(null!=$("credit-card-form")){var form=$("credit-card-form").up("form");if(null!=form&&0==form.select("#braintree-device-data").length){if(!0===self._deviceDataInit)return!1;self._deviceDataInit=!0;var input=new Element("input",{type:"hidden",name:"payment[device_data]",id:"braintree-device-data"});form.insert(input),self.populateDeviceData(input)}}},populateDeviceData:function(input){null===this.dataCollectorInstance?this.vzero.getClient(function(clientInstance){var params={client:clientInstance,kount:!0};!1!==this.vzeroPaypal&&(params.paypal=!0),braintree.dataCollector.create(params,function(err,dataCollectorInstance){err?"DATA_COLLECTOR_KOUNT_NOT_ENABLED"!=err.code&&"DATA_COLLECTOR_PAYPAL_NOT_ENABLED"!=err.code?console.error(err):(console.warn("A warning occurred whilst initialisation the Braintree data collector. This warning can be safely ignored."),console.warn(err)):(this.dataCollectorInstance=dataCollectorInstance,input.value=dataCollectorInstance.deviceData,input.writeAttribute("disabled",!1),this._deviceDataInit=!1)}.bind(this))}.bind(this)):this.dataCollectorInstance.teardown(function(){return this.dataCollectorInstance=null,this.populateDeviceData(input)}.bind(this))},initSavedMethods:function(){$$('#creditcard-saved-accounts input[type="radio"], #paypal-saved-accounts input[type="radio"]').each(function(element){var parentElement="",targetElement="";void 0!==element.up("#creditcard-saved-accounts")?(parentElement="#creditcard-saved-accounts",targetElement="#credit-card-form"):void 0!==element.up("#paypal-saved-accounts")&&(parentElement="#paypal-saved-accounts",targetElement=".paypal-info"),$(element).stopObserving("change").observe("change",function(event){return this.showHideOtherMethod(parentElement,targetElement)}.bind(this))}.bind(this)),this.vzero.fireEvent(this,"integration.onInitSavedMethods")},showHideOtherMethod:function(parentElement,targetElement){void 0!==$$(parentElement+" input:checked[type=radio]").first()&&"other"==$$(parentElement+" input:checked[type=radio]").first().value?void 0!==$$(targetElement).first()&&($$(targetElement).first().show(),$$(targetElement+" input, "+targetElement+" select").each(function(formElement){formElement.removeAttribute("disabled")})):void 0!==$$(parentElement+" input:checked[type=radio]").first()&&void 0!==$$(targetElement).first()&&($$(targetElement).first().hide(),$$(targetElement+" input, "+targetElement+" select").each(function(formElement){formElement.setAttribute("disabled","disabled")})),this.vzero.fireEvent(this,"integration.onShowHideOtherMethod",{parentElement:parentElement,targetElement:targetElement})},checkSavedOther:function(){var parentElement="",targetElement="";"gene_braintree_creditcard"==this.getPaymentMethod()?(parentElement="#creditcard-saved-accounts",targetElement="#credit-card-form"):"gene_braintree_paypal"==this.getPaymentMethod()&&(parentElement="#paypal-saved-accounts",targetElement=".paypal-info"),void 0!==$$(parentElement).first()&&this.showHideOtherMethod(parentElement,targetElement),this.vzero.fireEvent(this,"integration.onCheckSavedOther")},afterPaymentMethodSwitch:function(){return!0},initHostedFields:function(){this.vzero.hostedFields&&null!==$("braintree-hosted-submit")&&(void 0!==$("braintree-hosted-submit").up("form")?(this.form=$("braintree-hosted-submit").up("form"),this.vzero.initHostedFields(this)):console.error("Hosted Fields cannot be initialized as we're unable to locate the parent form."))},validateHostedFields:function(){if(!this.vzero.usingSavedCard()&&this.vzero._hostedIntegration){var state=this.vzero._hostedIntegration.getState(),errorMsgs=[],translate={number:Translator.translate("Card Number"),expirationMonth:Translator.translate("Expiry Month"),expirationYear:Translator.translate("Expiry Year"),cvv:Translator.translate("CVV"),postalCode:Translator.translate("Postal Code")};if($H(state.fields).each(function(field){0==field[1].isValid&&errorMsgs.push(translate[field[0]]+" "+Translator.translate("is invalid."))}.bind(this)),0<errorMsgs.length)return alert(Translator.translate("There are a number of errors present with the credit card form:")+"\n"+errorMsgs.join("\n")),!1;if(this.vzero.cardType&&this.vzero.supportedCards&&-1==this.vzero.supportedCards.indexOf(this.vzero.cardType))return alert(Translator.translate("We're currently unable to process this card type, please try another card or payment method.")),!1}return!0},initDefaultMethod:function(){this.shouldAddPayPalButton(!1)&&(this.setLoading(),this.vzero.updateData(function(){this.resetLoading(),this.updatePayPalButton("add")}.bind(this))),this.afterPaymentMethodSwitch(),this.vzero.fireEvent(this,"integration.onInitDefaultMethod")},observeAjaxRequests:function(){this.vzero.observeAjaxRequests(function(){this.vzero.updateData(function(){this.isOnepage&&(this.initSavedPayPal(),this.rebuildPayPalButton(),this.checkSavedOther(),this.vzero.hostedFields&&this.initHostedFields()),this.initSavedMethods(),this.afterPaymentMethodSwitch(),this.vzero.fireEvent(this,"integration.onObserveAjaxRequests")}.bind(this))}.bind(this),void 0!==this.config.ignoreAjax&&this.config.ignoreAjax)},rebuildPayPalButton:function(){null==$("paypal-container")&&this.updatePayPalButton()},initSavedPayPal:function(){void 0!==$$("#paypal-saved-accounts input[type=radio]").first()&&$("paypal-saved-accounts").on("change","input[type=radio]",function(event){this.updatePayPalButton(!1,"gene_braintree_paypal")}.bind(this))},captureOriginalSubmitFn:function(){return!1},observeSubmissionOverride:function(){setInterval(function(){this._originalSubmitFn&&this.prepareSubmitObserver()}.bind(this),500)},prepareSubmitObserver:function(){return!1},beforeSubmit:function(callback){return this._beforeSubmit(callback)},_beforeSubmit:function(callback){this.vzero.fireEvent(this,"integration.onBeforeSubmit"),this.submitAfterPayment&&$("braintree-submit-after-payment")&&$("braintree-submit-after-payment").remove(),callback()},afterSubmit:function(){return this.vzero.fireEvent(this,"integration.onAfterSubmit"),!1},submit:function(type,successCallback,failedCallback,validateFailedCallback){this.vzero._hostedFieldsTokenGenerated=!1,this.hostedFieldsGenerated=!1,this.shouldInterceptSubmit(type)&&("creditcard"!=type||"creditcard"==type&&this.validateHostedFields()?this.validateAll()?(this.setLoading(),this.beforeSubmit(function(){null!=$$('[data-genebraintree-name="number"]').first()&&this.vzero.updateCardType($$('[data-genebraintree-name="number"]').first().value),this.vzero.updateData(function(){this.updateBilling(),this.vzero.process({onSuccess:function(){if(this.enableDeviceData(),this.resetLoading(),this.afterSubmit(),this.enableDisableNonce(),this.vzero._hostedFieldsTokenGenerated=!0,this.hostedFieldsGenerated=!0,"function"==typeof successCallback)var response=successCallback();return this.setLoading(),response}.bind(this),onFailure:function(){if(this.vzero._hostedFieldsTokenGenerated=!1,this.hostedFieldsGenerated=!1,alert(Translator.translate("We're unable to process your payment, please try another card or payment method.")),this.resetLoading(),this.afterSubmit(),"function"==typeof failedCallback)return failedCallback()}.bind(this)})}.bind(this),this.getUpdateDataParams())}.bind(this))):(this.vzero._hostedFieldsTokenGenerated=!1,this.hostedFieldsGenerated=!1,this.resetLoading(),"function"==typeof validateFailedCallback&&validateFailedCallback()):this.resetLoading())},submitCheckout:function(){window.review&&review.save()},submitPayment:function(){payment.save&&payment.save()},enableDisableNonce:function(){"gene_braintree_creditcard"==this.getPaymentMethod()?(null!==$("creditcard-payment-nonce")&&$("creditcard-payment-nonce").removeAttribute("disabled"),null!==$("paypal-payment-nonce")&&$("paypal-payment-nonce").setAttribute("disabled","disabled")):"gene_braintree_paypal"==this.getPaymentMethod()&&(null!==$("creditcard-payment-nonce")&&$("creditcard-payment-nonce").setAttribute("disabled","disabled"),null!==$("paypal-payment-nonce")&&$("paypal-payment-nonce").removeAttribute("disabled"))},preparePaymentMethodSwitchObserver:function(){return this.defaultPaymentMethodSwitch()},defaultPaymentMethodSwitch:function(){var vzeroIntegration=this,paymentSwitchOriginal=Payment.prototype.switchMethod;Payment.prototype.switchMethod=function(method){return vzeroIntegration.paymentMethodSwitch(method),paymentSwitchOriginal.apply(this,arguments)}},paymentMethodSwitch:function(method){clearTimeout(this._methodSwitchTimeout),this._methodSwitchTimeout=setTimeout(function(){this.shouldAddPayPalButton(method)?this.updatePayPalButton("add",method):this.updatePayPalButton("remove",method),"gene_braintree_creditcard"==(method||this.getPaymentMethod())&&this.initHostedFields(),this.checkSavedOther(),this.afterPaymentMethodSwitch(),this.vzero.fireEvent(this,"integration.onPaymentMethodSwitch",{method:method})}.bind(this),50)},completePayPal:function(obj){return this.enableDisableNonce(),this.enableDeviceData(),obj.nonce&&null!==$("paypal-payment-nonce")?($("paypal-payment-nonce").value=obj.nonce,$("paypal-payment-nonce").setAttribute("value",obj.nonce)):console.warn("Unable to update PayPal nonce, please verify that the nonce input field has the ID: paypal-payment-nonce"),this.afterPayPalComplete(),!1},afterPayPalComplete:function(){return this.resetLoading(),this.submitCheckout()},getPayPalMarkUp:function(){return $("braintree-paypal-button").innerHTML},updatePayPalButton:function(action,method){if(!1===this.paypalWrapperMarkUp)return!1;if("refresh"==action)return!0;if(this.shouldAddPayPalButton(method)&&"remove"!=action||"add"==action)if(void 0!==$$(this.paypalButtonClass).first()){if($$(this.paypalButtonClass).first().hide(),void 0!==$$("#paypal-complete").first())return $$("#paypal-complete").first().show(),!0;$$(this.paypalButtonClass).first().insert({after:this.paypalWrapperMarkUp});var options=this.vzeroPaypal._buildOptions();options.events={validate:this.validateAll,onAuthorize:this.completePayPal.bind(this),onCancel:function(){},onError:function(err){alert("object"==typeof Translator?Translator.translate("We were unable to complete the request. Please try again."):"We were unable to complete the request. Please try again."),console.error("Error while processing payment",err)}},this.vzeroPaypal.addPayPalButton(options,"#paypal-container")}else console.warn("We're unable to find the element "+this.paypalButtonClass+". Please check your integration.");else void 0!==$$(this.paypalButtonClass).first()&&$$(this.paypalButtonClass).first().show(),void 0!==$$("#paypal-complete").first()&&$("paypal-complete").hide()},onReviewInit:function(){this.isOnepage||this.updatePayPalButton(),this.vzero.fireEvent(this,"integration.onReviewInit")},paypalOnReady:function(integration){return!0},setLoading:function(){checkout.setLoadWaiting("payment")},resetLoading:function(){checkout.setLoadWaiting(!1)},enableDeviceData:function(){null!==$("device_data")&&$("device_data").removeAttribute("disabled")},updateBilling:function(){(null!==$("billing-address-select")&&""==$("billing-address-select").value||null===$("billing-address-select"))&&(null!==$("billing:firstname")&&null!==$("billing:lastname")&&this.vzero.setBillingName($("billing:firstname").value+" "+$("billing:lastname").value),null!==$("billing:postcode")&&this.vzero.setBillingPostcode($("billing:postcode").value))},getUpdateDataParams:function(){var parameters={};return null!==$("billing-address-select")&&""!=$("billing-address-select").value&&(parameters.addressId=$("billing-address-select").value),parameters},getPaymentMethod:function(){return payment.currentMethod},shouldInterceptSubmit:function(type){switch(type){case"creditcard":return"gene_braintree_creditcard"==this.getPaymentMethod()&&this.vzero.shouldInterceptCreditCard();case"paypal":return"gene_braintree_paypal"==this.getPaymentMethod()&&this.vzero.shouldInterceptCreditCard()}return!1},shouldAddPayPalButton:function(method){return"gene_braintree_paypal"==(method||this.getPaymentMethod())&&null===$("paypal-saved-accounts")||"gene_braintree_paypal"==(method||this.getPaymentMethod())&&void 0!==$$("#paypal-saved-accounts input:checked[type=radio]").first()&&"other"==$$("#paypal-saved-accounts input:checked[type=radio]").first().value},threeDTokenizationComplete:function(){this.resetLoading()},validateAll:function(){return!0},disableCreditCardForm:function(){},enableCreditCardForm:function(){}};