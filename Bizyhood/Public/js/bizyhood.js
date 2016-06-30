jQuery(function($){
    
    var needRefresh = false;
    
    /**
    * Check a response fromt he server to see if the call was successful (uses
    *  success flag, not HTTP error codes)
    */
    function isSuccessful(raw_json)
    {
        o = eval('(' + raw_json + ')');
        return o.success == true;
    }

    /**
    * Show and fade away a 'saved' message next to a checkbox with the given id
    */
    function markSaved(span_id)
    {
        jQuery(span_id).show().delay(500).fadeOut();
    }
    
    if (jQuery('.left_column .box .color-picker').length > 0) {
      jQuery('.left_column .box .color-picker').wpColorPicker(); 
    }
    
   
    $('#save-bizyhood').click(function() {
        
        var network_id = $('#network').val();
        console.log($('#bh_btn_bg_color').iris('color'));
        // Submit AJAX request
        jQuery.post(ajaxurl, {
             action: 'bizyhood_save_settings', 
             api_production: $('#api_production').is(':checked'),
             api_id: $('#api_id').val(),
             api_secret: $('#api_secret').val(),
             main_page_id: $('#main_page_id').val(),
             signup_page_id: $('#signup_page_id').val(),
             promotions_page_id: $('#promotions_page_id').val(),
             events_page_id: $('#events_page_id').val(),
             btn_bg_color: $('#bh_btn_bg_color').val(),
             btn_font_color: $('#bh_btn_font_color').val()
            }, 
            function(response) {
                if(response.success) {
                    markSaved('#save-success');
                }
            },
        'json');
    });
   
});