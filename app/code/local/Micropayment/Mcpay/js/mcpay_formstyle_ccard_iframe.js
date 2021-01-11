var debug = false;
if (debug) console.log('mcpay_formstyle_ccard_iframe.js loaded');
var prefixCCard = 'mcpay_card_';

getElem = function (name) {
    return document.getElementById(name);
}

setupStyle = function () {
    if (debug) console.log('setupStyle');
    Micropayment.style('background-color', '#fff');
    Micropayment.style('font-size', '15px');
    Micropayment.style('font-weight', 'normal');
    Micropayment.style('color', '#333333');
    Micropayment.style('width', '99%'); // no calc support
    Micropayment.style('width', 'calc(100% - 1px)'); // mit calc support - override 99%
    Micropayment.style('display', 'inline-block');
    Micropayment.style('padding', '6px 6px 6px 45px');
    //Micropayment.style('margin', '0 .327em');

    Micropayment.setAttribute('placeholder', '**** **** **** ****', 'pan');
    Micropayment.setAttribute('placeholder', '***', 'cvc');
    Micropayment.setAttribute('type', 'tel', 'pan');
    Micropayment.setAttribute('type', 'tel', 'cvc');
}
Micropayment.addEvent('ready', setupStyle);

onError = function (e) {
    if (debug) console.log('onError');
    if (debug) console.log(e);
    var cardToken = getElem(prefixCCard + 'token');
    //cardToken.value = '';
    getElem(prefixCCard + 'load').setAttribute('class', 'hide');
    getElem(prefixCCard + 'modal').setAttribute('class', 'hide');
}

onSuccess = function (e) {
    if (debug) console.log('onSuccess');
    if (debug) console.log(e);
    //console.log(e.token);
    //console.log(e.fields.pan.val);
    //console.log(e.fields.cvc.val);
    var cardToken = getElem(prefixCCard + 'token');
    cardToken.value = e.token;
    var cardPan = getElem(prefixCCard + 'pan_mask');
    cardPan.value = e.fields.pan.val;
    var cardCVC = getElem(prefixCCard + 'cvc_mask');
    cardCVC.value = e.fields.cvc.val;

    getElem(prefixCCard + 'load').setAttribute('class', 'hide');
    getElem(prefixCCard + 'modal').setAttribute('class', 'hide');
}

onComplete = function (field) {
    setClassField(prefixCCard + field + '_ok', prefixCCard + 'check');
    if (field == 'cvc' || field == 'pan') {
        //console.log(field);
        getElem(prefixCCard + 'load').setAttribute('class', 'show');
        getElem(prefixCCard + 'modal').setAttribute('class', 'show');
        Micropayment.makeToken();
    }
}

onUnComplete = function (field) {
    setClassField(prefixCCard + field + '_ok', prefixCCard + 'notcheck');
    if (field == 'cvc' || field == 'pan') {
        //console.log(field);
        var cardToken = getElem(prefixCCard + 'token');
        cardToken.value = '';
    }
}

setClassField = function (field, cls) {
    getElem(field).setAttribute('class', cls);
}

onEvent = function (e) {
    if (!(e.field != 'pan' || e.field != 'cvc')) {
        console.log('unknown event');
        return;
    }
    switch (e.event) {
        case 'focus':
            if (e.field == 'pan' || e.field == 'cvc') onFieldFocus(e.field);
            break;
        case 'input':
            if (e.field == 'pan') onPanInput();
            break;
        case 'submit':
        case 'blur':
            if (e.field == 'pan' || e.field == 'cvc') onFieldBlur(e.field);
            break;
        case 'mouseover':
        case 'mouseout':
            break;
    }
}
onPanInput = function () {
    // console.log('pan input');
}

onFieldFocus = function (field) {
    getElem(prefixCCard + field).setAttribute('class', 'focus');
}

onFieldBlur = function (field) {
    getElem(prefixCCard + field).setAttribute('class', 'blur');
}

Micropayment.addEvent('on', onEvent);
Micropayment.addEvent('complete', onComplete);
Micropayment.addEvent('uncomplete', onUnComplete);
Micropayment.addEvent('error', onError);
Micropayment.addEvent('success', onSuccess);

setupStyle();