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

    $('#business_enabled').click(function() {
        needRefresh = true;
    });

    $('#one-click-signup').click(function(e) {
        e.preventDefault();
        var email = prompt('Please confirm your email address:', window.admin_email);

        if(!email) return false;

        $.post(ajaxurl, {action: 'register', email: email}, function(response) {
            if(response.success)
            {
                location.reload();
            }
            else
            {
                alert('There was an error creating a an account! Do you already have an account? If not, try again.');
            }
        }, 'json');
    });

    $('#save-broadstreet').click(function() {

        var network_id = $('#network').val();

        jQuery.post(ajaxurl, {
             action: 'bs_save_settings',
             api_key: $('#api_key').val(),
             business_enabled: $('#business_enabled').is(':checked'),
             network_id: network_id
            },
            function(response) {
                if (console) console.log(response);
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

                        $('#business_enabled')
                            .attr('disabled', false);

                    } else {
                        $('#network').append($('<option value="-1">Enter a valid token above</option>'));
                        $('#key-valid').hide().removeClass('visible');
                        $('#key-invalid').fadeIn().addClass('visible');
                        $('#business_enabled').attr('checked', false).attr('disabled', 'disabled');
                        alert(response.message);
                    }

                    if(needRefresh) {
                        location.reload();
                    }
                }
            },
        'json');
    });


    $('#save_bs_advertiser').click(function(e) {
        e.preventDefault();

        var el = $(e.target);
        var name = el.attr('data-name');

        $.post(ajaxurl, {
            action: 'create_advertiser',
            name: name
        }, function(response) {
            console.log(response);
            if(response.success) {

                $('#bs_advertiser_id').append(
                    $('<option value="' + response.advertiser.id + '" selected="selected">' + response.advertiser.name + '</option>')
                );
            }
        },
        'json');
    });

    $('#bs_update_source').change(showUpdateDetails);

    function showUpdateDetails() {
        var type = $('#bs_update_source').val();

        $('#bs_source_details').children().hide();
        $('#bs_source_' + type + '_detail').show();
    }

    $('#bs_source_details').children().hide();
    showUpdateDetails();

   var uploadField = '';
    var newItem = null;

    $('.upload-button').click(function() {
        window.send_to_editor = image_upload_handler;
        tb_show('', 'media-upload.php?type=image&amp;amp;amp;TB_iframe=true');
        return false;
    });

    $('.menu-upload-button').click(function() {
        window.send_to_editor = menu_upload_handler;
        tb_show('', 'media-upload.php?type=image&amp;amp;amp;TB_iframe=true');
        return false;
    });

    $('.offer-upload-button').click(function() {
        window.send_to_editor = offer_upload_handler;
        tb_show('', 'media-upload.php?type=image&amp;amp;amp;TB_iframe=true');
        return false;
    });

    window.remove_image = function(e) {
        e.preventDefault();
        el = $(e.target);

        if(confirm('Are you sure?'))
        {
            el = $(el);
            el.parents('li').remove();
        }

        window.rewrite_image_names();
    };

    function menu_upload_handler(html) {
        // It's probably a pdf or some non-image'
        url = $(html).attr('href');

        // Okay, maybe it's an image
        if(!url) url = $('img',html).attr('src');
        if(!url) url = $(html).attr('src');

        $('#bs_menu').val(url);
        tb_remove();
    }

    function offer_upload_handler(html) {
        // It's probably a pdf or some non-image'
        url = $(html).attr('href');

        // Okay, maybe it's an image
        if(!url) url = $('img',html).attr('src');
        if(!url) url = $(html).attr('src');

        $('#bs_offer').val(url);
        tb_remove();
    }

    function image_upload_handler(html) {
        imgurl = $('img',html).attr('src');
        if(!imgurl) imgurl = $(html).attr('src');
        add_images(imgurl);
        tb_remove();
    };

    function add_images(imgurl) {
        if(!$.isArray(imgurl)) imgurl = [imgurl];

        for(var i in imgurl) {
            var a, img, rm;
            a = $('<a target="_blank">').attr('href', imgurl[i]);
            rm = $('<a class="bs-remove" href="#">Remove</a>');
            rm.click(window.remove_image);
            img = $('<img src="' + imgurl[i] + '" alt="Photo" />');
            a.append(img).append('<br />').append(rm);

            uploadField = $('<input class="upload" type="hidden" value="" />');
            uploadField.attr('name', 'bs_images[' + $('#bs_image_list').children().length + ']');
            uploadField.val(imgurl[i]);

            $('#bs_image_list').append(
                $('<li>').append(a).append(uploadField)
            );
        }

        window.rewrite_image_names();
    }

    window.rewrite_image_names = function() {
        var len = $('#bs_image_list').children.length;

        $('#bs_image_list').children().each(function(i, li) {
            li = $(li);
            li.children('input').attr('name', 'bs_images[' + i + ']');
            if(i == 0) {
                li.addClass('featured');
            } else {
                li.removeClass('featured');
            }
        });
    };

    $('#bs-import').click(function(e) {
        e.preventDefault();
        var id = $('#bs-business-id').val();

        if(!id) {
            alert("Enter the business' Facebook page URL");
            return false;
        }

        $('#import-progress').show();

        $.post(ajaxurl, {id:id, post_id: window.bs_post_id, action: 'import_facebook'}, function(response) {
            console.log(response);
            if(response.success) {
                var count = 0;
                for(var key in response.profile) {
                    if(response.profile[key]) {
                        count++;
                        $('#bs-meta-table').find('[name="' + key + '"]').val(response.profile[key]);
                    }
                }
                add_images(response.profile.images);

                if(confirm('We were able to pull back a title and/or description for this business. Should we place it in the editor above?')) {
                    $('#title').val(response.profile.name);
                    tinymce.get('content').focus();
                    tinyMCE.activeEditor.setContent(response.profile.description);
                }

                alert(count + ' fields were magically imported!');

                if(!response.profile.charged) {
                    alert("Since we couldn't retrieve all of the standard information, this import is on the house.")
                }

            } else {
                alert("There was an error importing. Message: " + response.message);
            }
            $('#import-progress').hide();
        }, 'json')
    });

    $('.bs-remove').click(window.remove_image);
    try {
        $('#bs_image_list').sortable({update: window.rewrite_image_names });
    } catch(e) {}
});