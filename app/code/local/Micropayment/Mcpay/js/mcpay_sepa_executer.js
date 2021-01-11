function mcpay_sepa_execute()
{
    //console.log('mcpay sepa executer running...');

    // find anker point for script
    var f = getElem('dt_method_mcpay_sepa');
    // to show this js is running
    //f.setAttribute("style", "border: 10px solid #f00");
    if (!f) return false;

    var s = document.createElement("script");
    s.setAttribute( "src", "/micropayment/payment/getscript/sn/mcpay_formstyle_sepa.js" );
    f.appendChild(s);

    return true;
}

getElem = function (name) {
    var obj = document.getElementById(name);
    //console.log(obj);
    //console.log(typeof obj);
    if (typeof(obj) === 'undefined'){
        alert('Micropayment Sepa Element ID is undefined! Please check config!');
        return false;
    }
    if (obj === null){
        alert('Micropayment Sepa Element ID not found! Please check config!');
        return false;
    }
    return obj;
}