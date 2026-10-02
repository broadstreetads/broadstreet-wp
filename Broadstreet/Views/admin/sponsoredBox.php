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
        <div class="misc-pub-section" id="bsa_sponsor_advertiser_selection">
            <div><strong>Advertiser</strong></div>
            <?php if (isset($meta['bs_sponsor_advertiser_id'])): ?>
                <input type="hidden" id="bs_sponsor_old_advertiser_id" name="bs_sponsor_old_advertiser_id" value="<?php echo esc_attr($meta['bs_sponsor_advertiser_id']) ?>">
            <?php endif; ?>
            <?php
                # The advertiser list is loaded by bsaLoadAdvertisers() once tracking is on. Until then,
                # hold on to the post's current advertiser (if it has one) so that saving doesn't change it
                $has_advertiser = $meta['bs_sponsor_advertiser_id'] && $meta['bs_sponsor_advertiser_id'] != 'new_advertiser';
            ?>
            <select id="bs_sponsor_advertiser_id" name="bs_sponsor_advertiser_id" onchange="sponsorSelect()" <?php if (!$has_advertiser) echo 'disabled' ?>>
                <option value="<?php if ($has_advertiser) echo esc_attr($meta['bs_sponsor_advertiser_id']) ?>" selected="selected">Loading advertisers...</option>
            </select>
            <a href="#" id="bsa_sponsor_advertisers_retry" onclick="bsaLoadAdvertisers(); return false;" style="display:none;">Try again</a>
            <input type="text" name="bs_sponsor_advertiser_name" id="bs_sponsor_advertiser_name" placeholder="Untitled Advertiser" minlength="3" value="" style="display:none;" />
            <?php if (isset($meta['bs_sponsor_advertisement_id'])): ?>
                <input type="hidden" id="bs_sponsor_advertisement_id" name="bs_sponsor_advertisement_id" value="<?php echo esc_attr($meta['bs_sponsor_advertisement_id']) ?>">
            <?php endif; ?>
        </div>
        <?php if (@$meta['bs_sponsor_advertiser_id'] && @$meta['bs_sponsor_advertisement_id']): ?>
            <div class="misc-pub-section">
                <p>You can view this post's performance in
                    <a href="<?php echo esc_url(Broadstreet_Utility::getBroadstreetDashboardURL() . 'networks/' . urlencode($network_id) . '/advertisers/' . urlencode($meta['bs_sponsor_advertiser_id']) . '/advertisements/' . urlencode($meta['bs_sponsor_advertisement_id'])) ?>" target="_blank">Broadstreet's dashboard</a>.
                </p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        window.bsaAdvertisersState = null; // null, 'loading' or 'loaded'

        // The advertiser list comes from Broadstreet's API, so it's only fetched
        // once tracking is on for this post, not every time the editor opens
        window.bsaLoadAdvertisers = function () {
            if (window.bsaAdvertisersState) return;
            window.bsaAdvertisersState = 'loading';

            var select = jQuery('#bs_sponsor_advertiser_id');
            var retry = jQuery('#bsa_sponsor_advertisers_retry').hide();
            select.find('option').first().text('Loading advertisers...');

            var failed = function () {
                window.bsaAdvertisersState = null;
                select.find('option').first().text('Could not load advertisers');
                retry.show();
            };

            jQuery.get(window.ajaxurl, {action: 'bs_get_advertisers', _wpnonce: '<?php echo esc_js(wp_create_nonce('broadstreet_sponsor_nonce')); ?>'}, function (data) {
                if (!data || !data.success) return failed();

                var current = select.val();
                select.empty();
                jQuery.each(data.advertisers, function (i, advertiser) {
                    select.append(jQuery('<option></option>').val(advertiser.id).text(advertiser.name + ' (ID: ' + advertiser.id + ')'));
                });
                select.append(jQuery('<option value="new_advertiser"></option>').text('-- Create a New Advertiser --'));

                var has_current = select.find('option').filter(function () { return current && this.value == current; }).length;
                if (has_current) {
                    select.val(current);
                }

                select.prop('disabled', false);
                window.bsaAdvertisersState = 'loaded';
                sponsorSelect();
            }, 'json').fail(failed);
        }

        window.bsaSponsorToggle = function (e) {
            var sel = jQuery('#bsa_sponsor_advertiser_selection');
            if (jQuery('#bsa_is_sponsored').is(':checked')) {
                sel.fadeIn();
                bsaLoadAdvertisers();
            } else {
                sel.fadeOut();
            }
        }

        window.sponsorSelect = function() {
            var val = jQuery('#bs_sponsor_advertiser_id').val();
            if (val == 'new_advertiser') {
                jQuery('#bs_sponsor_advertiser_name').hide();
                jQuery('#bs_sponsor_advertiser_name').show();
            } else {
                jQuery('#bs_sponsor_advertiser_name').hide();
            }
        }

        window.bsaSponsorToggle();
        window.sponsorSelect();

        window.bsaSaveTimeout = null;

        // for gutenberg, after saving we need to update the form values with the
        // latest meta info
        jQuery(function() {
            if (wp && wp.data && wp.data.subscribe) {
                wp.data.subscribe(function (a,b,c) {
                    var editor = wp.data.select('core/editor');

                    if (!editor) {
                        console.info('Broadstreet could not get editor from promise');
                        return;
                    }

                    var isSavingPost = editor.isSavingPost();
                    var isAutosavingPost = editor.isAutosavingPost();
                    
                    if (isSavingPost) {
                        if (window.bsaSaveTimeout) {
                            clearTimeout(window.bsaSaveTimeout);
                        }

                        window.bsaSaveTimeout = setTimeout(function () {
                            var el = document.getElementById('bs_sponsor_old_advertisement_id');
                            var post_id = editor.getCurrentPostId();

                            jQuery.get(window.ajaxurl + '?action=get_sponsored_meta&post_id=' + post_id + '&nonce=' + broadstreetAjax.nonce, function (data) {
                                var meta = data.meta;
                                console.info('Broadstreet Meta Update ...', meta);
                                if (meta.bs_sponsor_is_sponsored == '1') {
                                    jQuery('#bsa_is_sponsored').prop('checked', true);                                    
                                    jQuery('#bs_sponsor_advertisement_id').val(meta.bs_sponsor_advertisement_id);
                                    var sel = jQuery('#bsa_sponsor_advertiser_selection option[value="' + meta.bs_sponsor_advertiser_id+ '"]');
                                    if (sel.length == 0) {
                                        jQuery('#bsa_sponsor_advertiser_selection select').append(
                                            jQuery('<option value="' + meta.bs_sponsor_advertiser_id + '"></option>').text(jQuery('#bs_sponsor_advertiser_name').val())
                                        );
                                    }
                                    sel = jQuery('#bsa_sponsor_advertiser_selection option[value="' + meta.bs_sponsor_advertiser_id + '"]').prop('selected', true);
                                    jQuery('#bs_sponsor_old_advertiser_id', meta.bs_sponsor_advertiser_id);
                                }
                                bsaSponsorToggle();
                                sponsorSelect();
                            }, 'json');
                        }, 500)                
                    }            
                })  
            }
        })      
    </script>
<?php else: ?>
        <p style="color: green; font-weight: bold;">You either have no zones or
            Broadstreet isn't configured correctly. Go to 'Settings', then 'Broadstreet',
        and make sure your access token is correct, and make sure you have zones set up.</p>
<?php endif; ?>
<input type="hidden" name="bs_sponsor_submit" value="1" />
<script>
window.bs_post_id = <?php echo (int)$GLOBALS['post']->ID ?>;
window.broadstreet_sponsor_nonce = '<?php echo wp_create_nonce('broadstreet_sponsor_nonce'); ?>';
</script>