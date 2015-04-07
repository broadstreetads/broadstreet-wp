<script src="https://bizyhood-common.s3.amazonaws.com/bizyhood-net/init.js"></script>
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
                            <div class="name nomargin">Business directory page</div>
                            <div class="desc nomargin">
                                The page that will be used to show the categories and businesses. Must include the [bh-categories] and [bh-businesses] shortcodes.<br />
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
                            <input type="button" id="zip_code_remove" value="Remove"/>
                            <br/>
                            <select id="zip_codes" multiple="multiple">
                                <?php foreach($zip_codes as $zip_code): ?>
                                <option value="<?php echo $zip_code ?>"><?php echo $zip_code ?></option>
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
                            <input type="button" id="category_remove" value="Remove"/>

                            <br/>
                            <select id="categories" multiple="multiple">
                                <?php foreach($categories as $category): ?>
                                <option value="<?php echo $category; ?>"><?php echo $category; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="clear:both;"></div>
                    </div>

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
