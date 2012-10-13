<div id="main">
      <img src="<?php echo Broadstreet_Utility::getImageBaseURL(); ?>broadstreet-header.png" alt="" />
      <div class="left_column">
         <?php if($errors): ?>
             <div class="box">
                    <div class="shadow_column">
                        <div class="title" style="padding-left: 27px; background: #F1F1F1 url('<?php echo Broadstreet_Utility::getImageBaseURL(); ?>info.png') no-repeat scroll 7px center;">
                            Alerts
                        </div>
                        <div class="content">
                            <p>
                                We've noticed some things you'll need to take
                                care of before you get started:
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
                                
                            </div>
                        </div>
                        <div class="save-container">
                            <span class="success" id="save-success">Saved!</span>
                            <input id="save" type="button" value="Save" name="" />
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
        <div class="about">
            <?php echo $about; ?>
        </div>
      </div>
      <div class="right_column">
          <a href="http://broadstreetads.com" target="_blank">
            <img class="oconf_logo" src="<?php echo Broadstreet_Utility::getImageBaseURL(); ?>marty.png" alt="" />
          </a>
          <?php
                if($message = Broadstreet_Utility::getBroadstreetMessage())
                {
                    echo $message;
                }
          ?>
          <h3>Have a bug report?</h3>
          <p>
              We like to crush bugs as soon as we hear about them!
              Be sure to give us as much detail as possible,
              such as the number of posts you have, any error messages that
              were given, and any behavior you've observed.
          </p>
          <p>
              Send any and all reports to <a href="mailto:ohcrap@broadstreetads.com">ohcrap@broadstreetads.com</a>. Thanks
              for using Broadstreet!
          </p>
      </div>
    </div>
      <div class="clearfix"></div>
      <!-- <img src="http://report.Broadstreet2.com/checkin/?s=<?php echo $service_tag.'&'.time(); ?>" alt="" /> -->