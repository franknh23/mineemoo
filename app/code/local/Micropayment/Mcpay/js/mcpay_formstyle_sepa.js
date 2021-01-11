var debug = false;
if (debug) console.log('mcpay_formstyle_sepa.js loaded');
var prefixSepa = 'mcpay_sepa_';

getElem = function (name) {
    return document.getElementById(name);
}

setClassField = function (field, cls) {
    getElem(field).setAttribute('class', cls);
}

getElem(prefixSepa + 'holder').onchange = function (e) {
    if (this.value == '') {
        setClassField(prefixSepa + 'holder_ok', prefixSepa + 'notcheck');
    } else {
        setClassField(prefixSepa + 'holder_ok', prefixSepa + 'check');
        setClassField(prefixSepa + 'holder', 'ok');
    }
}

getElem(prefixSepa + 'iban').onchange = function (e) {
    if (this.value == '') {
        setClassField(prefixSepa + 'iban_ok', prefixSepa + 'notcheck');
    } else {
        setClassField(prefixSepa + 'iban_ok', prefixSepa + 'check');
        setClassField(prefixSepa + 'iban', 'ok');
    }
}

getElem(prefixSepa + 'bic').onchange = function (e) {
    if (this.value == '') {
        setClassField(prefixSepa + 'bic_ok', prefixSepa + 'notcheck');
    } else {
        setClassField(prefixSepa + 'bic_ok', prefixSepa + 'check');
        setClassField(prefixSepa + 'bic', 'ok');
    }
}

// check click on buy button
if (getElem('place_order') != undefined) {
    getElem('place_order').addEventListener("click", function (e) {
        if (debug) console.log('place_order sepa clicked.');
        if (!getElem('payment_method_mipa_sepa').checked) return true;
        if (getElem(prefixSepa + 'reuse_old') != undefined) {
            //console.log(getElem(prefixSepa + 'reuse_old'));
            if (getElem(prefixSepa + 'reuse_old').checked) return true;
        }
        // check fields
        var allFieldsFilled = true;
        if (getElem(prefixSepa + 'holder').value == '') {
            allFieldsFilled = false;
            setClassField(prefixSepa + 'holder', 'error');
        }
        var ibanCountry = '';
        if (getElem(prefixSepa + 'iban').value == '') {
            allFieldsFilled = false;
            setClassField(prefixSepa + 'iban', 'error');
        } else if (!isValidIBANNumber(getElem(prefixSepa + 'iban').value)){
            allFieldsFilled = false;
            setClassField(prefixSepa + 'iban', 'error');
            console.log('invalid IBAN');
        } else {
            ibanCountry = getElem(prefixSepa + 'iban').value.substring(0, 2);
            //console.log(ibanCountry);
        }
        if (ibanCountry != 'DE' && getElem(prefixSepa + 'bic').value == '') {
            allFieldsFilled = false;
            setClassField(prefixSepa + 'bic', 'error');
        }
        // if a field is empty show error
        if (!allFieldsFilled) {
            if (debug) console.log('place_order sepa fields not filled.');
            // display an error message
            getElem(prefixSepa + 'modal').setAttribute('class', 'show');
            getElem(prefixSepa + 'error').setAttribute('class', 'show');

            e.cancelBubble = true;
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        return true;
    });
}

getElem(prefixSepa + 'error').onclick = function () {
    //getElem(prefixSepa+'error').innerHTML = "";
    getElem(prefixSepa + 'error').setAttribute('class', 'hide');
    getElem(prefixSepa + 'modal').setAttribute('class', 'hide');
}

getElem(prefixSepa + 'modal').onclick = function () {
    //getElem(prefixSepa+'error').innerHTML = "";
    getElem(prefixSepa + 'error').setAttribute('class', 'hide');
    getElem(prefixSepa + 'modal').setAttribute('class', 'hide');
}

function isValidIBANNumber(input) {
    var CODE_LENGTHS = {
        AD: 24, AE: 23, AT: 20, AZ: 28, BA: 20, BE: 16, BG: 22, BH: 22, BR: 29,
        CH: 21, CR: 21, CY: 28, CZ: 24, DE: 22, DK: 18, DO: 28, EE: 20, ES: 24,
        FI: 18, FO: 18, FR: 27, GB: 22, GI: 23, GL: 18, GR: 27, GT: 28, HR: 21,
        HU: 28, IE: 22, IL: 23, IS: 26, IT: 27, JO: 30, KW: 30, KZ: 20, LB: 28,
        LI: 21, LT: 20, LU: 20, LV: 21, MC: 27, MD: 24, ME: 22, MK: 19, MR: 27,
        MT: 31, MU: 30, NL: 18, NO: 15, PK: 24, PL: 28, PS: 29, PT: 25, QA: 29,
        RO: 24, RS: 22, SA: 24, SE: 24, SI: 19, SK: 24, SM: 27, TN: 24, TR: 26
    };
    var iban = String(input).toUpperCase().replace(/[^A-Z0-9]/g, ''), // keep only alphanumeric characters
        code = iban.match(/^([A-Z]{2})(\d{2})([A-Z\d]+)$/), // match and capture (1) the country code, (2) the check digits, and (3) the rest
        digits;
    // check syntax and length
    if (!code || iban.length !== CODE_LENGTHS[code[1]]) {
        return false;
    }
    // rearrange country code and check digits, and convert chars to ints
    digits = (code[3] + code[1] + code[2]).replace(/[A-Z]/g, function (letter) {
        return letter.charCodeAt(0) - 55;
    });
    // final check
    var checksum = digits.slice(0, 2), fragment;
    for (var offset = 2; offset < digits.length; offset += 7) {
        fragment = String(checksum) + digits.substring(offset, offset + 7);
        checksum = parseInt(fragment, 10) % 97;
    }
    return checksum === 1;
}

var handleSepaRadioClick = function(myRadio) {
    if (myRadio.value == 'new'){
        getElem(prefixSepa+'newform').setAttribute('class', 'show');
        getElem(prefixSepa+'reuse_card').setAttribute('class', 'hide');
    } else {
        getElem(prefixSepa+'newform').setAttribute('class', 'hide');
        getElem(prefixSepa+'reuse_card').setAttribute('class', 'show');
    }
}

// WooCommerce Special Start
if (getElem('createaccount') != undefined) {
    var checkSepaACC = function () {
        var chkBox = getElem('createaccount');
        if (chkBox.checked) {
            //console.log(prefixSepa + 'remember show');
            getElem(prefixSepa + 'remember').setAttribute('class', 'show');
        } else {
            //console.log(prefixSepa + 'remember hide');
            getElem(prefixSepa + 'remember').setAttribute('class', 'hide');
        }
    }
    getElem('createaccount').addEventListener("change", function (e) {
        checkSepaACC();
    });

    checkSepaACC();
}
// WooCommerce Special End