var debug = false;

if (debug) console.log('mcpay_formstyle_ccard_check.js loaded');
var prefixCCard = 'mcpay_card_';

getElem = function (name) {
    var obj = document.getElementById(name);
    if (typeof(obj) === 'undefined'){
        if (debug) console.log('Micropayment Element ID ('+name+') is undefined!');
        return false;
    }
    if (obj === null){
        if (debug) console.log('Micropayment Element ID ('+name+') is null!');
        return false;
    }
    return obj;
}

if (getElem('mcpay_card_token-form') === false){
    if (!debug) alert('Micropayment PayForm ID (payformid) not found! Please check config!');
    if (debug) console.log('Micropayment PayForm ID (payformid) not found! Please check config!');
}

//if(getElem(prefixCCard + 'token').value == ''){
    //doMIPA();
//}

setClassField = function (field, cls) {
    getElem(field).setAttribute('class', cls);
}

getElem(prefixCCard + 'holder').onchange = function (e) {
    if (debug) console.log(e);
    if (debug) console.log(prefixCCard+'holder onchange');
    if (debug) console.log(this.value);
    // woo commerce auto prefill event needs no modal
    if (!e.isTrusted) {
      if (typeof Micropayment === 'undefined') {
        if (debug) console.log('doMIPA Call !!');
        doMIPA();
      }
      return;
    }
    if (this.value == '') {
        setClassField(prefixCCard + 'holder_ok', prefixCCard + 'notcheck');
    } else {
        setClassField(prefixCCard + 'holder_ok', prefixCCard + 'check');
        getElem(prefixCCard + 'load').setAttribute('class', 'show');
        getElem(prefixCCard + 'modal').setAttribute('class', 'show');
        Micropayment.makeToken();
    }
}
getElem(prefixCCard + 'month').onchange = function (e) {
    if (debug) console.log(e);
    if (debug) console.log(prefixCCard+'month onchange');
    getElem(prefixCCard + 'load').setAttribute('class', 'show');
    getElem(prefixCCard + 'modal').setAttribute('class', 'show');
    checkExpire();
    if (typeof Micropayment !== 'undefined') {
      Micropayment.makeToken();
    } else {
      doMIPA();
    }
}
getElem(prefixCCard + 'year').onchange = function (e) {
    if (debug) console.log(e);
    if (debug) console.log(prefixCCard+'month onchange');
    getElem(prefixCCard + 'load').setAttribute('class', 'show');
    getElem(prefixCCard + 'modal').setAttribute('class', 'show');
    checkExpire();
    if (typeof Micropayment !== 'undefined') {
      Micropayment.makeToken();
    } else {
      doMIPA();
    }
}

checkExpire = function () {
    var month = parseInt(getElem(prefixCCard + 'month').value);
    var year = parseInt(getElem(prefixCCard + 'year').value);
    if (month <= 0) {
        setClassField(prefixCCard + 'expire_ok', prefixCCard + 'notcheck');
        return;
    }
    if (year <= 0) {
        setClassField(prefixCCard + 'expire_ok', prefixCCard + 'notcheck');
        return;
    }
    year+= 2000;
    //console.log(month+' - '+year);
    var dNow = new Date();
    var dCard = new Date(year, month);
    //console.log(dNow.getTime()+' < '+dCard.getTime());
    if (dNow.getTime() < dCard.getTime()) {
        // date in future
        setClassField(prefixCCard + 'expire_ok', prefixCCard + 'check');
    } else {
        // date expired
        setClassField(prefixCCard + 'expire_ok', prefixCCard + 'notcheck');
    }
}

// check click on buy button
if (getElem('place_order') !== false) {
    getElem('place_order').addEventListener("click", function (e) {
        if (debug) console.log('place_order ccard clicked');
        // if not mipa cc act like normal
        if (!getElem('payment_method_mipa_ccard').checked) return true;
        if (getElem(prefixCCard + 'reuse_old') != undefined) {
            if (getElem(prefixCCard + 'reuse_old').checked) return true;
        }
        // check token
        var cardToken = getElem(prefixCCard + 'token');
        //console.log(cardToken);
        // if token empty show error
        if (cardToken.value == '') {
            if (debug) console.log('place_order ccard fields not filled.');
            // display an error message
            getElem(prefixCCard + 'modal').setAttribute('class', 'show');
            //getElem(prefixCCard+'error').innerHTML = "Please check you inputs!<br>Bitte Eingaben &uuml;berpr&uuml;fen.";
            getElem(prefixCCard + 'error').setAttribute('class', 'show');

            e.cancelBubble = true;
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        return true;
    });
}

getElem(prefixCCard + 'error').onclick = function () {
    //getElem(prefixCCard+'error').innerHTML = "";
    getElem(prefixCCard + 'error').setAttribute('class', 'hide');
    getElem(prefixCCard + 'modal').setAttribute('class', 'hide');
}

getElem(prefixCCard + 'modal').onclick = function () {
    //getElem(prefixCCard+'error').innerHTML = "";
    getElem(prefixCCard + 'error').setAttribute('class', 'hide');
    getElem(prefixCCard + 'modal').setAttribute('class', 'hide');
}




var handleCCardRadioClick = function(myRadio) {
    if (myRadio.value == 'new'){
        getElem(prefixCCard+'newform').setAttribute('class', 'show');
        getElem(prefixCCard+'reuse_card').setAttribute('class', 'hide');
    } else {
        getElem(prefixCCard+'newform').setAttribute('class', 'hide');
        getElem(prefixCCard+'reuse_card').setAttribute('class', 'show');
    }
}

// WooCommerce Special Start
if (getElem('createaccount') !== false) {
    var checkCCardACC = function () {
        var chkBox = getElem('createaccount');
        if (chkBox.checked) {
            //console.log(prefixCCard + 'remember show');
            getElem(prefixCCard + 'remember').setAttribute('class', 'show');
        } else {
            //console.log(prefixCCard + 'remember hide');
            getElem(prefixCCard + 'remember').setAttribute('class', 'hide');
        }
    }
    getElem('createaccount').addEventListener("change", function (e) {
        checkCCardACC();
    });

    checkCCardACC();
}
if (getElem('account_password') !== false) {
    getElem(prefixCCard + 'remember').setAttribute('class', 'show');
}
// WooCommerce Special End