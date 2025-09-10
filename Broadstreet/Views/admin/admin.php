<div id="main">
      <?php Broadstreet_View::load('admin/global/header') ?>
      <div class="left_column">
         <?php if($errors): ?>
             <div class="box">
                    <div class="shadow_column">
                        <div class="title" style="">
                            <span class="dashicons dashicons-warning"></span> Alerts
                        </div>
                        <div class="content">
                            <p>
                                Nice to have you! We've noticed some things you may want to take
                                care of:
                            </p>
                            <ol>
                                <?php foreach($errors as $error): ?>
                                    <li><?php echo esc_html($error); ?></li>
                                <?php endforeach; ?>
                            </ol>
                        </div>
                    </div>
                    <div class="shadow_bottom"></div>
             </div>
         <?php endif; ?>
          <div id="controls">
            <div class="box">
                <div class="title"><span class="dashicons dashicons-admin-generic"></span> Setup</div>
                <div class="content">
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Access Token

                                <span class="error <?php if(!$key_valid) echo "visible"; ?>" id="key-invalid">Invalid</span>
                                <span class="success <?php if($key_valid) echo "visible"; ?>" id="key-valid">Valid</span>

                            </div>
                            <div class="desc nomargin">
                                This can be found <a target="_blank" href="http://my.broadstreetads.com/access-token">here</a> when you're logged in to Broadstreet.<br />
                            </div>
                        </div>
                        <div class="control-container">
                            <input id="api_key" type="text" value="<?php echo esc_attr($api_key) ?>" />
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Publisher Selection
                            </div>
                            <div class="desc nomargin">
                                Which publisher or network does this site fall under?
                            </div>
                        </div>
                        <div class="control-container">
                            <select id="network" type="text">
                                <?php foreach($networks as $network): ?>
                                <option <?php if($network_id == $network->id) echo "selected"; ?> value="<?php echo esc_attr($network->id) ?>"><?php echo esc_html($network->name) . ' (' . esc_html($network->id) . ')' ?></option>
                                <?php endforeach; ?>
                                <?php if(count($networks) == 0): ?>
                                <option value="-1">Enter a valid token above</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Enable Business Directory
                            </div>
                            <div class="desc nomargin">
                                Would you like to enable the Broadstreet business directory? Requires an API Key.
                            </div>
                        </div>
                        <div class="control-container">
                            <input id="business_enabled" type="checkbox" value="TRUE" <?php if($key_valid && $business_enabled) echo 'checked="checked"'; ?> <?php if(!$key_valid) echo 'disabled="disabled"'; ?> />
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                <?php if($business_enabled) echo '<a href="?page=Broadstreet-Help">How to Get Started with the Business Directory</a>' ?>
                            </div>
                        </div>
                        <div class="save-container">
                            <span class="success" id="save-success">Saved!</span>
                            <input id="save-broadstreet" type="button" value="Save" name="" />
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
      </div>
      <div class="right_column">
          <?php Broadstreet_View::load('admin/global/sidebar') ?>
      </div>
    </div>
      <div class="clearfix"></div>
      <!-- <img src="http://report.Broadstreet2.com/checkin/?s=<?php echo $service_tag.'&'.time(); ?>" alt="" /> -->