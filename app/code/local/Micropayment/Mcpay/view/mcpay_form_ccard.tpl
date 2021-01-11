<div id="PREFIX_form" action="?go=1" method="POST">
  <input type="hidden" name="PREFIX_token" value="" id="PREFIX_token">
  <div id="PREFIX_modal"></div>
  <div id="PREFIX_load"></div>
  <div id="PREFIX_error" class="hide">Please check your inputs!</div>
  <div id="PREFIX_reuse"></div>
  <div id="PREFIX_newcard"></div>
  <div id="PREFIX_newform" class="show">
    <img src="https://www.micropayment.de/resources/?what=img&group=cc&show=type-n.1" style="{IMG_STYLE}">
    <div class="PREFIX_field">
      <label class="PREFIX_label" for="PREFIX_holder">Name on Card</label>
      <span class="PREFIX_fieldIcon"><span class="glyphicon glyphicon-user"></span></span>
      <input type="text" name="PREFIX_holder" id="PREFIX_holder" value="" placeholder="Vorname Nachname" />
      <span id="PREFIX_holder_ok" class="PREFIX_notcheck">&#10003;</span>
    </div>

    <div  class="PREFIX_field">
      <label class="PREFIX_label" for="PREFIX_pan">Credit Card Number</label>
      <span class="PREFIX_fieldIcon"><span class="glyphicon glyphicon-credit-card"></span></span>
      <div id="PREFIX_pan"></div>
      <input type="hidden" name="PREFIX_pan" value="" id="PREFIX_pan_mask">
      <span id="PREFIX_pan_ok" class="PREFIX_notcheck">&#10003;</span>
    </div>

    <div  class="PREFIX_field">
      <label class="PREFIX_label" for="PREFIX_month">Expiration Date</label>
      <span class="PREFIX_fieldIcon"><span class="glyphicon glyphicon-calendar"></span></span>
      <select name="PREFIX_month" id="PREFIX_month">
        <option>Month</option>
      </select>
      <span class="PREFIX_fieldIcon"><span class="glyphicon glyphicon-calendar"></span></span>
      <select name="PREFIX_year" id="PREFIX_year">
        <option>Year</option>
      </select>
      <span id="PREFIX_expire_ok" class="PREFIX_notcheck">&#10003;</span>
    </div>

    <div  class="PREFIX_field">
      <label class="PREFIX_label" for="PREFIX_cvc">Card CVC</label>
      <span class="PREFIX_fieldIcon"><span class="glyphicon glyphicon-lock"></span></span>
      <div id="PREFIX_cvc"></div>
      <input type="hidden" name="PREFIX_cvc" value="" id="PREFIX_cvc_mask">
      <span id="PREFIX_cvc_ok" class="PREFIX_notcheck">&#10003;</span>
    </div>

      <div id="PREFIX_remember"></div>
  </div>

</div>