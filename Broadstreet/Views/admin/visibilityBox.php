<div class="misc-publishing-actions">
    <div class="misc-pub-section">
        <p>Use this switch to disable all ads on this particular page or post.</p>
        <div style="float:left; padding-top: 5px;">
            <strong>Disable Ads</strong>
        </div>
        <div class="checkbox-switch" style="float:right;">
            <input type="checkbox" <?php if ($meta['bs_ads_disabled']) echo 'checked' ?> value="1" name="bs_ads_disabled" class="input-checkbox" id="bs_ads_disabled">
            <div class="checkbox-animate">
                <span class="checkbox-off">Off</span>
                <span class="checkbox-on">On</span>
            </div>
        </div>
    </div>
    <div style="clear:both;"></div>
</div>

<input type="hidden" name="bs_ads_disabled_submit" value="1" />