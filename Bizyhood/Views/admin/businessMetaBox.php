<style>
    .bs-meta-table input,textarea {
        width: 85%;
    }
    
    .bs-meta-table input.short {
        width: 50%;
    }
    
    .bs-meta-table input.shorter {
        width: 25%;
    }
    
    .bs-meta-table input.menu-upload-button {
        width:auto;
    }
    
    #bs_image_list li {
        display: inline-block;
        margin-right: 3px;
        padding: 3px;
        text-align: center;
        -moz-box-shadow: 0 0 4px #888;
        -webkit-box-shadow: 0 0 4px#888;
        box-shadow: 0 0 4px #888;
        cursor: move;
    }
    
    #bs_image_list li.featured {
        background-color: lightYellow;
        border-color: #E6DB55;
    }
    
    #bs_image_list li a {
        text-decoration: none;
    }
    
    #bs_image_list li img {
        height: 50px;
        border: 1px solid #ccc;
    }
    
    .bs-meta-table select {
        width: 50%;
    }
    
    .bs-meta-table textarea {
        height: 70px;
    }
    
    .bs-hours input {
        width: 100px;
    }
    
    .bs-hours .day {
        font-weight: bold;
        width: 30%;
    }
    
    .bs-meta-table > tbody > tr > td {
        vertical-align: top;
    }
    
    .bs-meta-table .label {
        font-weight: bold;
    }
    
    .bs-meta-table .label em {
        font-weight: normal;
        font-size: 80%;
    }
    
    .time-picker {
        width: 100px !important;
    }
    
    .broadstreet-special {
        border: 1px solid #ccc;
        padding: 5px;
        border-radius:5px;
        background: rgb(255,255,255); /* Old browsers */
        background: -moz-linear-gradient(top,  rgba(255,255,255,1) 0%, rgba(246,246,246,1) 47%, rgba(237,237,237,1) 100%); /* FF3.6+ */
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(255,255,255,1)), color-stop(47%,rgba(246,246,246,1)), color-stop(100%,rgba(237,237,237,1))); /* Chrome,Safari4+ */
        background: -webkit-linear-gradient(top,  rgba(255,255,255,1) 0%,rgba(246,246,246,1) 47%,rgba(237,237,237,1) 100%); /* Chrome10+,Safari5.1+ */
        background: -o-linear-gradient(top,  rgba(255,255,255,1) 0%,rgba(246,246,246,1) 47%,rgba(237,237,237,1) 100%); /* Opera 11.10+ */
        background: -ms-linear-gradient(top,  rgba(255,255,255,1) 0%,rgba(246,246,246,1) 47%,rgba(237,237,237,1) 100%); /* IE10+ */
        background: linear-gradient(to bottom,  rgba(255,255,255,1) 0%,rgba(246,246,246,1) 47%,rgba(237,237,237,1) 100%); /* W3C */
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#ededed',GradientType=0 ); /* IE6-9 */
        margin-bottom: 10px;
    }
    
    #import-progress {
        display: none;
    }
</style>
<div id="broadstreet-updates" class="broadstreet-special">
    <table width="100%" class="bs-meta-table">
        <tbody>
            <tr>
                <td width="30%" class="label">
                    Magic Import ($<?php echo number_format($network->import_cost/100, 2) ?>) <img id="import-progress" src="<?php echo Broadstreet_Utility::getImageBaseURL() ?>ajax-loader-rotate.gif" alt="Progress!" /><br />
                    <img align="left" width="40" class="oconf_logo" src="<?php echo Broadstreet_Utility::getImageBaseURL(); ?>marty.png" alt="" />
                    <em>If the business has a Facebook page, we can auto-fill some fields and crawl the web for images.</em>
                </td>
                <td>
                    <input class="short"  id="bs-business-id" placeholder="[paste the facebook page URL here]" type="text" />
                    <input class="shorter" type="button" id="bs-import" value="Import" />
                    
                </td>
            </tr>
        </tbody>
    </table>
</div>
<table width="100%" id="bs-meta-table" class="bs-meta-table">
    <tbody>
        <tr>
            <td width="30%" class="label">Broadstreet Advertiser
            </td>
            <td>
                <select id="bs_advertiser_id" name="bs_advertiser_id">
                    <?php $linked = false; ?>
                    <?php $has_match = false ?>
                    <?php foreach($advertisers as $advertiser): ?>
                        <?php if($advertiser->name == $GLOBALS['post']->post_title) $has_match = true; ?>
                        <?php if($meta['bs_advertiser_id'] == $advertiser->id) $linked = true; ?>
                        <option value="<?php echo $advertiser->id ?>" <?php if($meta['bs_advertiser_id'] == $advertiser->id) echo ' selected="selected"' ?>><?php echo $advertiser->name ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if(!$has_match): ?>
                - or -
                    <?php if($GLOBALS['post']->post_title): ?>
                    <a href="#" id="save_bs_advertiser" data-name="<?php echo $GLOBALS['post']->post_title ?>">Create <?php if($linked) echo 'new' ?> <?php echo $GLOBALS['post']->post_title ?></a>
                    <?php else: ?>
                    Enter a post title above and save
                    <?php endif; ?>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td width="30%" class="label">
                Image Gallery<br />
                <em>Add as many images as needed. You can rearrange
                the images by dragging them. The first image in the list will be 
                used as the featured image.</em>
            </td>
            <td>
                <ul id="bs_image_list">
                    <?php for($i = 0; $i < count($meta['bs_images']); $i++): ?>
                        <li <?php if($i == 0) echo 'class="featured"' ?>>
                            <a href="<?php echo $meta['bs_images'][$i] ?>" target="_blank">
                                <img src="<?php echo $meta['bs_images'][$i] ?>" alt="<?php echo $GLOBALS['post']->post_title . ' photo #' . $i ?>" />
                                <br />
                                <a class="bs-remove" href="#">Remove</a>
                            </a>
                            <input class="bs-image-input" type="hidden" name="bs_images[<?php echo $i ?>]" value="<?php echo $meta['bs_images'][$i] ?>" />
                        </li>
                    <?php endfor; ?>
                </ul>
                <a href="#" class="upload-button" type="button" value="">Add an Image</a></td>
        </tr>
        <tr>
            <td class="label">Address</td>
            <td>
                <input type="text" name="bs_address_1" value="<?php echo $meta['bs_address_1'] ?>" placeholder="Address 1" /><br />
                <input type="text" name="bs_address_2" value="<?php echo $meta['bs_address_2'] ?>" placeholder="Address 2 (if needed)" />
            </td>
        </tr>
        <tr>
            <td class="label">City, State &amp; Postal Code</td>
            <td>
                <input class="shorter" type="text" name="bs_city" value="<?php echo $meta['bs_city'] ?>" placeholder="City" />
                <input class="shorter" type="text" name="bs_state" value="<?php echo $meta['bs_state'] ?>" placeholder="State" />
                <input class="shorter" type="text" name="bs_postal" value="<?php echo $meta['bs_postal'] ?>" placeholder="Postal Code" />
            </td>
        </tr>
        <tr>
            <td class="label">Phone Number</td>
            <td><input placeholder="Phone Number" type="text" name="bs_phone" value="<?php echo $meta['bs_phone'] ?>" /></td>
        </tr>
        <tr>
            <td class="label">
                Hours of Operation</br>
                <em>Leave the field blank if this business is closed on a particular day.</em>
            </td>
            <td>
                <table class="bs-hours">
                    <tr>
                        <td class="day">Monday</td>
                        <td>Open <input class="timepicker" placeholder="Closed" type="text" name="bs_monday_open" value="<?php echo $meta['bs_monday_open'] ?>" /></td>
                        <td>Close <input class="timepicker" placeholder="Closed" type="text" name="bs_monday_close" value="<?php echo $meta['bs_monday_close'] ?>" /></td>
                    </tr>
                    <tr>
                        <td class="day">Tuesday</td>
                        <td>Open <input class="timepicker" placeholder="Closed" type="text" name="bs_tuesday_open" value="<?php echo $meta['bs_tuesday_open'] ?>" /></td>
                        <td>Close <input class="timepicker" placeholder="Closed" type="text" name="bs_tuesday_close" value="<?php echo $meta['bs_tuesday_close'] ?>" /></td>
                    </tr>
                    <tr>
                        <td class="day">Wednesday</td>
                        <td>Open <input class="timepicker" placeholder="Closed" type="text" name="bs_wednesday_open" value="<?php echo $meta['bs_wednesday_open'] ?>" /></td>
                        <td>Close <input class="timepicker" placeholder="Closed" type="text" name="bs_wednesday_close" value="<?php echo $meta['bs_wednesday_close'] ?>" /></td>
                    </tr>
                    <tr>
                        <td class="day">Thursday</td>
                        <td>Open <input class="timepicker" placeholder="Closed" type="text" name="bs_thursday_open" value="<?php echo $meta['bs_thursday_open'] ?>" /></td>
                        <td>Close <input class="timepicker" placeholder="Closed" type="text" name="bs_thursday_close" value="<?php echo $meta['bs_thursday_close'] ?>" /></td>
                    </tr>
                    <tr>
                        <td class="day">Friday</td>
                        <td>Open <input class="timepicker" placeholder="Closed" type="text" name="bs_friday_open" value="<?php echo $meta['bs_friday_open'] ?>" /></td>
                        <td>Close <input class="timepicker" placeholder="Closed" type="text" name="bs_friday_close" value="<?php echo $meta['bs_friday_close'] ?>" /></td>
                    </tr>
                    <tr>
                        <td class="day">Saturday</td>
                        <td>Open <input class="timepicker" placeholder="Closed" type="text" name="bs_saturday_open" value="<?php echo $meta['bs_saturday_open'] ?>" /></td>
                        <td>Close <input class="timepicker" placeholder="Closed" type="text" name="bs_saturday_close" value="<?php echo $meta['bs_saturday_close'] ?>" /></td>
                    </tr>
                    <tr>
                        <td class="day">Sunday</td>
                        <td>Open <input class="timepicker" placeholder="Closed" type="text" name="bs_sunday_open" value="<?php echo $meta['bs_sunday_open'] ?>" /></td>
                        <td>Close <input class="timepicker" placeholder="Closed" type="text" name="bs_sunday_close" value="<?php echo $meta['bs_sunday_close'] ?>" /></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="label">Website URL</td>
            <td><input placeholder="http://example.com" type="text" name="bs_website" value="<?php echo $meta['bs_website'] ?>" /></td>
        </tr>
        <tr>
            <td class="label">Menu (URL or upload)</td>
            <td>
                <input placeholder="http://example.com" id="bs_menu" class="short" type="text" name="bs_menu" value="<?php echo $meta['bs_menu'] ?>" />
                <a href="#" class="menu-upload-button">Upload a file</a>
            </td>
        </tr>
        <tr>
            <td class="label">Publisher Review URL</td>
            <td><input placeholder="<?php echo site_url() ?>/your/review" type="text" name="bs_publisher_review" value="<?php echo $meta['bs_publisher_review'] ?>" /></td>            
        </tr>
        <tr>
            <td class="label">Twitter URL</td>
            <td><input placeholder="http://twitter.com/username" type="text" name="bs_twitter" value="<?php echo $meta['bs_twitter'] ?>" /></td>
        </tr>
        <tr>
            <td class="label">Facebook Page URL</td>
            <td><input placeholder="http://facebook.com/business-name" type="text" name="bs_facebook" value="<?php echo $meta['bs_facebook'] ?>" /></td>
        </tr>
        <tr>
            <td class="label">Yelp URL</td>
            <td><input placeholder="http://yelp.com/biz/business-name" type="text" name="bs_yelp" value="<?php echo $meta['bs_yelp'] ?>" /></td>
        </tr>
        <tr>
            <td class="label">Google Plus URL</td>
            <td><input placeholder="https://plus.google.com/112513709762074435437" type="text" name="bs_gplus" value="<?php echo $meta['bs_gplus'] ?>" /></td>
        </tr>
        <?php if($show_offers): ?>
        <tr>
            <td class="label">Special Offer Image &amp; Link</td>
            <td>
                <input placeholder="http://example.com/offer.jpg" id="bs_offer" class="short" type="text" name="bs_offer" value="<?php echo $meta['bs_offer'] ?>" />
                <a href="#" class="offer-upload-button">Upload a file</a>
                <br />
                <input placeholder="http://example.com/offer-landing-page" id="bs_offer_link" class="short" type="text" name="bs_offer_link" value="<?php echo $meta['bs_offer_link'] ?>" />
                <strong>Click Url (optional)</strong>
            </td>
        </tr>
        <?php endif; ?>
        <tr>
            <td class="label">Video Embed Code Snippet</td>
            <td><textarea name="bs_video"><?php echo htmlentities($meta['bs_video']) ?></textarea></td>
        </tr>
        <tr>
            <td class="label">Is This a Featured Business?</td>
            <td>
                <select name="bs_featured_business">
                    <option <?php if($meta['bs_featured_business'] == "0") echo "selected" ?> value="0">No</option>
                    <option <?php if($meta['bs_featured_business'] == "1") echo "selected" ?> value="1">Yes</option>
                </select>
            </td>
        </tr>
    </tbody>
</table>

<div id="broadstreet-updates" class="broadstreet-special">
    <table width="100%" class="bs-meta-table">
        <tbody>
            <tr>
                <td width="30%" class="label">
                    <div>Updatable Messages ($25 / month)</div>
                    <img align="left" width="40" class="oconf_logo" src="<?php echo Broadstreet_Utility::getImageBaseURL(); ?>marty.png" alt="" />
                    <em>Let businesses updates messages on their profile. This is a premium feature offered by Broadstreet.</em>
                    <br/>
                    <br/>
                    <strong>How It Works</strong><br/>
                    <em>Provide the business' social account information on the right.
                        When the business posts social updates, they will appear
                        in their listings on your site, giving them a platform
                        for specials and announcements.
                    </em>
                </td>
                <td>
                    <div>
                        <select id="bs_update_source" name="bs_update_source">
                            <option value="" <?php if($meta['bs_update_source'] == '') echo 'selected="selected"' ?>>No Status Updates</option>
                            <option value="facebook" <?php if($meta['bs_update_source'] == 'facebook') echo 'selected="selected"' ?>>Facebook Updates</option>
                            <option value="twitter" <?php if($meta['bs_update_source'] == 'twitter') echo 'selected="selected"' ?>>Twitter Updates</option>
                            <option value="text_message" <?php if($meta['bs_update_source'] == 'text_message') echo 'selected="selected"' ?>>Text Message Updates</option>
                        </select>
                    </div>
                    <div id="bs_source_details">
                        <div id="bs_source_facebook_detail">
                            <strong>Facebook User or Page Id</strong><br/>
                            <input type="text" name="bs_facebook_id" value="<?php echo $meta['bs_facebook_id'] ?>" /><br/>
                            <strong>Hashtag (optional, control which updates get pulled)</strong><br/>
                            <input type="text" name="bs_facebook_hashtag" value="<?php echo @$meta['preferred_hash_tag'] ?>" />
                        </div>
                        <div id="bs_source_twitter_detail">
                            <strong>Twitter Username</strong><br/>
                            <input type="text" name="bs_twitter_id" value="<?php echo $meta['bs_twitter_id'] ?>" /><br />
                            <strong>Hashtag (optional, control which updates get pulled)</strong><br/>
                            <input type="text" name="bs_twitter_hashtag" value="<?php echo @$meta['preferred_hash_tag'] ?>" />
                        </div>
                        <div id="bs_source_text_message_detail">
                            <strong>Phone Number (have advertiser send updates from this # to 609-207-7067)</strong><br/>
                            <input type="text" name="bs_phone_number" value="<?php echo $meta['bs_phone_number'] ?>" />
                        </div>
                    </div>
                    
                    <?php if($meta['bs_update_source']): ?>
                    <div>
                        <strong>Text Preview: </strong>
                        <?php echo $meta['bs_advertisement_html'] ?>
                    </div>
                    <?php endif; ?>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<input type="hidden" name="bs_submit" value="1" />

<script src="<?php echo Broadstreet_Utility::getVendorBaseURL() ?>jquery/jquery-ui-1.9.1.sortable-custom.min.js"></script>
<script>window.bs_post_id = '<?php echo $GLOBALS['post']->ID ?>';</script>
