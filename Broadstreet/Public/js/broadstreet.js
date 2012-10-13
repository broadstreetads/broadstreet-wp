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
        
        var network_id = $('#network').val();
        
        jQuery.post(ajaxurl, {
             action: 'save_settings', 
             api_key: $('#api_key').val(),
             network_id: network_id
            }, 
            function(response) {
                if(response.success)
                {
                    markSaved('#save-success');
                    $('#network').empty();

                    if(response.key_valid) {
                        $('#key-invalid').hide().removeClass('visible');;
                        $('#key-valid').fadeIn().addClass('visible');
                        var opt;
                        
                        for(var i in response.networks) {
                            opt = $('<option>')
                                    .text(response.networks[i].name)
                                    .attr('value', response.networks[i].id);
                                    
                            if(network_id == response.networks[i].id)
                                opt.attr('selected', 'selected');
                                    
                            $('#network').append(opt);
                        }

                    } else {
                        $('#network').append($('<option value="-1">Enter a valid token above</option>'));
                        $('#key-valid').hide().removeClass('visible');
                        $('#key-invalid').fadeIn().addClass('visible');
                    }
                }
            },
        'json');
    });
    
});