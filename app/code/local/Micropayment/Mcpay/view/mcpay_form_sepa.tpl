<div id="PREFIX_form" action="?go=1" method="POST">
  <div id="PREFIX_modal"></div>
  <div id="PREFIX_load"></div>
  <div id="PREFIX_error" class="hide">Please check your inputs!</div>
  <div id="PREFIX_reuse"></div>
  <div id="PREFIX_newcard"></div>
  <div id="PREFIX_newform" class="show">
    <img src="https://www.micropayment.de/resources/?what=img&group=dbt&show=type-n.1" style="{IMG_STYLE}">
    <div class="PREFIX_field">
      <label class="PREFIX_label" for="PREFIX_holder">Name on Card</label>
      <span class="PREFIX_fieldIcon"><span class="glyphicon glyphicon-user"></span></span>
      <input type="text" name="PREFIX_holder" id="PREFIX_holder" value="PREFIX_holder_val" class="PREFIX_holder_class" placeholder="Vorname Nachname" />
      <span id="PREFIX_holder_ok" class="PREFIX_notcheck">&#10003;</span>
    </div>

    <div  class="PREFIX_field">
      <label class="PREFIX_label" for="PREFIX_iban">IBAN</label>
      <span class="PREFIX_fieldIcon"><span class="glyphicon glyphicon-credit-card"></span></span>
      <input type="text" name="PREFIX_iban" id="PREFIX_iban" value="PREFIX_iban_val" class="PREFIX_iban_class" placeholder="IBAN" />
      <span id="PREFIX_iban_ok" class="PREFIX_notcheck">&#10003;</span>
    </div>

    <div  class="PREFIX_field">
      <label class="PREFIX_label" for="PREFIX_bic">BIC</label>
      <span class="PREFIX_fieldIcon"><span class="glyphicon glyphicon-credit-card"></span></span>
      <input type="text" name="PREFIX_bic" id="PREFIX_bic" value="PREFIX_bic_val" class="PREFIX_bic_class" placeholder="BIC" />
      <span id="PREFIX_bic_ok" class="PREFIX_notcheck">&#10003;</span>
    </div>

      <div id="PREFIX_remember"></div>
  </div>

</div>