<div id="main">
      <?php Bizyhood_View::load('admin/global/header') ?>
      <div class="left_column">
         <?php if($errors): ?>
             <div class="box">
                    <div class="shadow_column">
                        <div class="title" style="padding-left: 27px; background: #F1F1F1 url('<?php echo Bizyhood_Utility::getImageBaseURL(); ?>info.png') no-repeat scroll 7px center;">
                            Alerts
                        </div>
                        <div class="content">
                            <p>
                                Nice to have you! We've noticed some things you may want to take
                                care of:
                            </p>
                            <ol>
                                <?php foreach($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ol>
                        </div>
                    </div>
                    <div class="shadow_bottom"></div>
             </div>
         <?php endif; ?>
          <div id="controls">
            <div class="box">
                <div class="title">Setup</div>
                <div class="content">

                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">Production Mode</div>
                            <div class="desc nomargin">
                                Uncheck this only for development purposes.<br />
                                <small><?php echo $api_url; ?>
                            </div>
                        </div>
                        <div class="control-container">
                            <input type="checkbox" id="api_production" value="TRUE" <?php if ($api_production) { echo 'checked="checked"'; } ?> />
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>
                    
                    
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">API Client ID</div>
                            <div class="desc nomargin">
                              Provided by Bizyhood
                            </div>
                        </div>
                        <div class="control-container">
                            <input type="text" id="api_id" name="api_id" value="<?php echo $api_id; ?>" />
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>
                    
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">API Client Secret Key</div>
                            <div class="desc nomargin">
                              Provided by Bizyhood
                            </div>
                        </div>
                        <div class="control-container">
                            <input type="text" id="api_secret" name="api_secret" value="<?php echo $api_secret; ?>" />
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>

                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">Business Directory Page</div>
                            <div class="desc nomargin">
                                The page that will be used to show the categories and businesses. Must include the [bh-businesses] shortcode.<br />
                            </div>
                        </div>
                        <div class="control-container">
                            <?php wp_dropdown_pages( array('name' => 'main_page_id', 'selected' => $main_page_id) ) ?>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>
                    
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">Business Signup Page</div>
                            <div class="desc nomargin">
                                The landing/marketing page that will be used to allow businesses to signup for a Bizyhood account.<br />
                            </div>
                        </div>
                        <div class="control-container">
                            <?php wp_dropdown_pages( array('name' => 'signup_page_id', 'selected' => $signup_page_id) ) ?>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    
                    <div class="break"></div>
                    
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">Business Promotions Page</div>
                            <div class="desc nomargin">
                                The page that will be used to display all businesses promotions. Must include the [bh-promotions] shortcode.<br />
                            </div>
                        </div>
                        <div class="control-container">
                            <?php wp_dropdown_pages( array('name' => 'promotions_page_id', 'selected' => $promotions_page_id) ) ?>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    
                    <div class="break"></div>
                    
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">Business Events Page</div>
                            <div class="desc nomargin">
                                The page that will be used to display all businesses events. Must include the [bh-events] shortcode.<br />
                            </div>
                        </div>
                        <div class="control-container">
                            <?php wp_dropdown_pages( array('name' => 'events_page_id', 'selected' => $events_page_id) ) ?>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    
                    <div class="break"></div>

                    <div class="option">
                        <div class="save-container">
                            <span class="success" id="save-success">Saved!</span>
                            <input id="save-bizyhood" type="button" value="Save" name="" />
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
      </div>
    </div>
