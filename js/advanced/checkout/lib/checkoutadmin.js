function showform(isNew, div) {
    if (isNew) {
        $(div).show();
    } else {
        $(div).hide();
    }
}

Event.observe(window,'load',function(){   
    
    $('onestepcheckout_style_title').style.border = '1px solid #'+$('onestepcheckout_style_title').value;    
    $('onestepcheckout_style_button').style.border = '1px solid #'+$('onestepcheckout_style_button').value;
    $('onestepcheckout_style_color_font').style.border = '1px solid #'+$('onestepcheckout_style_color_font').value;
    $('onestepcheckout_style_text_button').style.border = '1px solid #'+$('onestepcheckout_style_text_button').value;
    $('onestepcheckout_style_title').style.borderRight = '20px solid #'+$('onestepcheckout_style_title').value;
    $('onestepcheckout_style_button').style.borderRight = '20px solid #'+$('onestepcheckout_style_button').value;
    $('onestepcheckout_style_color_font').style.borderRight = '20px solid #'+$('onestepcheckout_style_color_font').value;
    $('onestepcheckout_style_text_button').style.borderRight = '20px solid #'+$('onestepcheckout_style_text_button').value;
    $_('#onestepcheckout_style_title').colpick({
            layout:'hex',
            submit:0,
            colorScheme:'dark',
            color:$('onestepcheckout_style_title').value,
            onChange:function(hsb,hex,rgb,el,bySetColor) {
                    jQuery(el).css('border-color','#'+hex);
                    // Fill the text box just if the color was set using the picker, and not the colpickSetColor function.
                    if(!bySetColor) jQuery(el).val(hex);
            }
    }).keyup(function(){
            jQuery(this).colpickSetColor(this.value);
    });
    
    $_('#onestepcheckout_style_button').colpick({
            layout:'hex',
            submit:0,
            colorScheme:'dark',
            color:$('onestepcheckout_style_button').value,
            onChange:function(hsb,hex,rgb,el,bySetColor) {
                    jQuery(el).css('border-color','#'+hex);
                    // Fill the text box just if the color was set using the picker, and not the colpickSetColor function.
                    if(!bySetColor) jQuery(el).val(hex);
            }
    }).keyup(function(){
            jQuery(this).colpickSetColor(this.value);
    });
    
    $_('#onestepcheckout_style_color_font').colpick({
            layout:'hex',
            submit:0,
            colorScheme:'dark',
            color:$('onestepcheckout_style_color_font').value,
            onChange:function(hsb,hex,rgb,el,bySetColor) {
                    jQuery(el).css('border-color','#'+hex);
                    // Fill the text box just if the color was set using the picker, and not the colpickSetColor function.
                    if(!bySetColor) jQuery(el).val(hex);
            }
    }).keyup(function(){
            jQuery(this).colpickSetColor(this.value);
    });
    
    $_('#onestepcheckout_style_text_button').colpick({
            layout:'hex',
            submit:0,
            colorScheme:'dark',
            color:$('onestepcheckout_style_text_button').value,
            onChange:function(hsb,hex,rgb,el,bySetColor) {
                    jQuery(el).css('border-color','#'+hex);
                    // Fill the text box just if the color was set using the picker, and not the colpickSetColor function.
                    if(!bySetColor) jQuery(el).val(hex);
            }
    }).keyup(function(){
            jQuery(this).colpickSetColor(this.value);
    });
});
