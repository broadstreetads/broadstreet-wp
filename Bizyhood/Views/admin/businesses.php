<script src="https://broadstreet-common.s3.amazonaws.com/broadstreet-net/init.js"></script>
<form method="post">
<div id="main">
      <?php Broadstreet_View::load('admin/global/header') ?>
      <div class="left_column">
          <div id="controls">
            <div class="box">
                <div class="title">Business Directory Settings</div>
                <div class="content">
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Featured Business Badge   
                            </div>
                            <div class="desc nomargin">
                                Click <a id="bs-upload-featured" href="#">here</a> to use an alternate.<br />
                            </div>
                        </div>
                        <div class="control-container">
                            <input id="featured_business_image" name="featured_business_image" type="text" value="<?php echo $featured_image ?>" />
                        </div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                &nbsp;
                            </div>
                        </div>
                        <div class="save-container">
                            <input type="submit" value="Save" name="" />
                        </div>
                    </div>
                        <div style="clear:both;"></div>
                    </div>
                </div>
            </div>
        </div>
      </div>
      <div class="right_column">
          <?php Broadstreet_View::load('admin/global/sidebar') ?>
      </div>
    </div>
      <div class="clearfix"></div>
</form>
<script>
    jQuery('#bs-upload-featured').click(function() {        
        window.send_to_editor = upload_featured_handler;
        tb_show('', 'media-upload.php?type=image&amp;amp;amp;TB_iframe=true');
        return false;
    });
    
    function upload_featured_handler(html) {
        // It's probably a pdf or some non-image'
        url = jQuery(html).attr('href');
        
        // Okay, maybe it's an image
        if(!url) url = jQuery('img',html).attr('src');
        if(!url) url = jQuery(html).attr('src');
        
        jQuery('#featured_business_image').val(url);
        tb_remove();
    }
</script>
      <!-- <img src="http://report.Broadstreet2.com/checkin/?s=<?php echo $service_tag.'&'.time(); ?>" alt="" /> -->