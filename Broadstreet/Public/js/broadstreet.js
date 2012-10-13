jQuery(function($){
    
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
    
    $('#save').click(function() {
        
        jQuery.post(ajaxurl, {action: 'save_settings', api_key: $('#api_key').val()}, function(response) {
            if(isSuccessful(response))
                markSaved('#save-success');
        });
        
    });
    
});