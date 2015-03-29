<script src="https://broadstreet-common.s3.amazonaws.com/broadstreet-net/init.js"></script>
<div id="main">
      <?php Broadstreet_View::load('admin/global/header') ?>
      <div class="left_column">
         <?php if($errors): ?>
             <div class="box">
                    <div class="shadow_column">
                        <div class="title" style="padding-left: 27px; background: #F1F1F1 url('<?php echo Broadstreet_Utility::getImageBaseURL(); ?>info.png') no-repeat scroll 7px center;">
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
                            <input id="api_key" type="text" value="<?php echo $api_key ?>" />
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>

                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">API URL</div>
                            <div class="desc nomargin">
                                The URL of the Bizyhood API. Shouldn't need to be changed except for testing purposes.<br />
                            </div>
                        </div>
                        <div class="control-container">
                            <input id="api_url" type="text" value="<?php echo $api_url ?>" />
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>

                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Targeted ZIP codes
                            </div>
                            <div class="desc nomargin">
                                These are the ZIP codes included in your local area<br />
                            </div>
                        </div>
                        <div class="control-container">
                            ZIP Code: <input type="text" maxlength="5" id="zip_code"/>
                            <input type="button" id="zip_code_add" value="Add"/>
                            <br/>
                            <select id="zip_codes" multiple="multiple">
                                <?php foreach($zip_codes as $zip_code): ?>
                                <option value="<?php echo $zip_code ?>" selected="selected"><?php echo $zip_code ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>

                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Category types
                            </div>
                            <div class="desc nomargin">
                                If this is a restaurant blog, enable this option to use cuisine types for the category list page. Otherwise, you can enter applicable categories.<br />
                            </div>
                        </div>
                        <div class="control-container">
                            <input type="checkbox" id="use_cuisine_types" value="TRUE" <?php if ($use_cuisine_types): ?>checked="checked"<?php endif; ?> />
                            <label for="use_cuisine_types">Enable restaurant behavior</label>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>

                    <div class="option" id="custom-categories" <?php if ($use_cuisine_types): ?>style="display:none"<?php endif; ?> >
                        <div class="control-label">
                            <div class="name nomargin">
                                Relevant categories
                            </div>
                            <div class="desc nomargin">
                                These categories will be shown when browsing by category<br />
                            </div>
                        </div>
                        <div class="control-container">
                            Category name: <br/>
                            <input type="text" id="category"/>
                            <input type="button" id="category_add" value="Add"/>
                            <br/>
                            <select id="categories" multiple="multiple">
                                <?php foreach($categories as $category): ?>
                                <option value="<?php echo $category; ?>" selected="selected"><?php echo $category; ?></option>
                                <?php endforeach; ?>
                            </select>
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
                                <option <?php if($network_id == $network->id) echo "selected"; ?> value="<?php echo $network->id ?>"><?php echo htmlentities($network->name) ?></option>
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
                                <a href="?page=Broadstreet-Help">How to Get Started</a>
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
    </div>
      <div class="clearfix"></div>
