function mcpay_ccard_execute()
{
    //console.log('mcpay ccard executer running...');

    // find anker point for script
    var f = getElem('dt_method_mcpay_ccard');
    // to show this js is running
    //f.setAttribute("style", "border: 10px solid #0f0");
    if (!f) return false;

    var s = document.createElement("script");
    s.setAttribute( "src", "/micropayment/payment/getscript/sn/mcpay_formstyle_ccard_check.js" );
    f.appendChild(s);

    return true;
}

getElem = function (name) {
    var obj = document.getElementById(name);
    //console.log(obj);
    //console.log(typeof obj);
    if (typeof(obj) === 'undefined'){
        alert('Micropayment CCard Element ID is undefined! Please check config!');
        return false;
    }
    if (obj === null){
        alert('Micropayment CCard Element ID not found! Please check config!');
        return false;
    }
    return obj;
}