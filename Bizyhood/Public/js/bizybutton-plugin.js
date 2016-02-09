(function() {
      
    // Register plugin
    tinymce.create( 'tinymce.plugins.bizylink', {
      
      init: function( editor, url )  {

        editor.addButton( 'bizylink', {
            title: 'Bizybox / Bizylink',
            image : url+ '/../img/bizylink.png',
            cmd: 'bizylink_command'
        });
        
        
        // Called when we click the Insert Gistpen button
        editor.addCommand( 'bizylink_command', function() {
          // Calls the pop-up modal
          editor.windowManager.open({
            
            title: 'Insert BizyBox',
            width: 500,
            height: 400,
            inline: 1,
            id: 'bizylink-insert-dialog',
            
            buttons: [{
              text: 'Insert',
              id: 'plugin-bizzy-button-insert',
              classes: 'widget btn primary first abs-layout-item',
              onclick: function( e ) {
                insertBizyBox(editor);
              },
            },
            {
              text: 'Cancel',
              id: 'plugin-bizy-button-cancel',
              onclick: 'close'
            }]
          });
          
          appendBizyInsertDialog(url);
          
        });
        
        
        
      
      }
  });
  
  
  tinymce.PluginManager.add( 'bizylink', tinymce.plugins.bizylink );
  
  
  function insertBizyBox(editor) {
    
    var title = jQuery('#bizylink_title').val();
    var link = jQuery('#bizylink_link').val();
    var type = jQuery('input[name="bizylink_type"]:checked').val();
    
    if (document.getElementById('bizylink_target').checked) {
      var target = '_blank';
    } else {
      var target = '_self';
    }
    
    
    if(link === '' || title === '') {
      var window_id = this._id;

      // editor.windowManager.alert('Please fill in all fields.');

      if(title === '') {
          jQuery('#bizylink_title').css('border-color', 'red');
      }

      if(link === '') {
          jQuery('#bizylink_link').css('border-color', 'red');
      }
      return false;
    }
    

    
    editor.insertContent( '<a href="' + link + '" class="' + type + '" target="' + target + '">' + title + '</a>');
    editor.windowManager.close();
  }
  
  
  
  
  function appendBizyInsertDialog(url) {
    
    
        
    var dialogBody = jQuery( '#bizylink-insert-dialog-body' ).append('<span class="loading" ><img style="margin:20px;" src="' + url + '/../img/loading.gif" /></span>');
    
		// Get the form template from WordPress
		jQuery.post( ajaxurl, {
			action: 'bizylink_insert_dialog'
		}, function( response ) {
			template = response;

			dialogBody.children( '.loading' ).remove();
			dialogBody.append( template );
      
      
      if (tinyMCE.activeEditor.selection.getContent() != '') {
        jQuery('#bizylink_title').val(tinyMCE.activeEditor.selection.getContent());
        jQuery('#bizylink_search').val(tinyMCE.activeEditor.selection.getContent());
        doneTyping ();
      }
      
      jQuery('#bizylink_link').on('click', function() {
        jQuery(this).css('border-color', '');
      });
      jQuery('#bizylink_title').on('click', function() {
        jQuery(this).css('border-color', '');
      });
      
      // get the results
      var typingTimer;                //timer identifier
      var doneTypingInterval = 500;  //time in ms, 5 second for example
      var $input = jQuery('#bizylink_search');

      //on keyup, start the countdown
      $input.on('keyup', function () {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(doneTyping, doneTypingInterval);
      });

      //on keydown, clear the countdown 
      $input.on('keydown', function () {
        clearTimeout(typingTimer);
      });
      
      
		});
    
       

    //user is "finished typing," do something
    function doneTyping () {
      if (jQuery('#bizylink_search').val().length > 2) {
        
        jQuery('#bizylink_results').html('<span class="loading"><img src="' + url + '/../img/loading.gif" /></span>');
        
        jQuery.post( ajaxurl, {
          action: 'bizylink_business_results',
          keywords: jQuery('#bizylink_search').val()
        }, function( response ) {
          jQuery('#bizylink_results .loading').hide();
          jQuery('#bizylink_results').append(response);
          jQuery('#bizylink_results a').on('click', function(e) {
            e.preventDefault();
            
              jQuery('#bizylink_results ul li').removeClass('clicked');
              jQuery(this).closest('li').addClass('clicked');
            
              jQuery('#mceu_55').slideDown();
              jQuery('#bizylink_link').val(jQuery(this).attr('href')).css('border-color', '');
            return false;
          });
        });
        
        
        
      }
    }
    
    
	}
  
  
})();