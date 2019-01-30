<?php if(true): ?>
    <div class="misc-publishing-actions">
        <div class="misc-pub-section">
            <div style="float:left; padding-top: 5px;">
                <strong>Performance Tracking</strong>
            </div>
            <div class="checkbox-switch" style="float:right;">
                <input type="checkbox" <?php if ($meta['bs_sponsor_is_sponsored']) echo 'checked' ?> onchange="bsaSponsorToggle(event)" value="1" name="bs_sponsor_is_sponsored" class="input-checkbox" id="bsa_is_sponsored">
                <div class="checkbox-animate">
                    <span class="checkbox-off">Off</span>
                    <span class="checkbox-on">On</span>
                </div>
            </div>
        </div>
        <div style="clear:both;"></div>
        <div class="misc-pub-section" id="bsa_sponsor_advertiser_selection" style="display:none;">
            <div><strong>Advertiser</strong></div>
            <select id="bs_sponsor_advertiser_id" name="bs_sponsor_advertiser_id">
                <?php $linked = false; ?>
                <?php $has_match = false ?>
                <?php foreach($advertisers as $advertiser): ?>
                    <?php if($advertiser->name == $GLOBALS['post']->post_title) $has_match = true; ?>
                    <?php if($meta['bs_sponsor_advertiser_id'] == $advertiser->id) $linked = true; ?>
                    <option value="<?php echo $advertiser->id ?>" <?php if($meta['bs_sponsor_advertiser_id'] == $advertiser->id) echo ' selected="selected"' ?>><?php echo esc_html($advertiser->name) ?> (ID: <?php echo esc_html($advertiser->id) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="misc-pub-section">
            <p>You can view this post's performance in
                <a href="https://my.broadstreetads.com/networks/1/advetisers/1/advertisements/1" target="_blank">Broadstreet's dashboard</a>.
            </p>
        </div>
    </div>

    <script>
        window.bsaSponsorToggle = function (e) {
            var sel = jQuery('#bsa_sponsor_advertiser_selection');
            if (jQuery('#bsa_is_sponsored').is(':checked')) {
                sel.fadeIn();
            } else {
                sel.fadeOut();
            }
        }

        window.bsaSponsorToggle();
    </script>
<?php else: ?>
        <p style="color: green; font-weight: bold;">You either have no zones or
            Broadstreet isn't configured correctly. Go to 'Settings', then 'Broadstreet',
        and make sure your access token is correct, and make sure you have zones set up.</p>
<?php endif; ?>
<input type="hidden" name="bs_sponsor_submit" value="1" />
<script>window.bs_post_id = '<?php echo $GLOBALS['post']->ID ?>';</script>