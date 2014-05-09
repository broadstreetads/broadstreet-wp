<?php 
$has_thumbnail = has_post_thumbnail($GLOBALS['post']->ID); 
$thumb_url     = wp_get_attachment_image_src(get_post_thumbnail_id($GLOBALS['post']->ID));
?>
<?php if($meta['bs_update_source']): ?>
    <div class="sponsored-listing">
        <?php echo $meta['bs_advertisement_html'] ?>
    </div>
<?php endif; ?>
<div id="biz-column-1">
    <?php if(count($meta['bs_images'])): ?>
        <a class="nodec" target="_blank" href="<?php echo $meta['bs_images'][0] ?>">
            <img class="profile-pic boxed-sizing" src="<?php echo $meta['bs_images'][0] ?>" alt="" width="100%" />
        </a>
    <div></div>
    <?php endif; ?>
    <?php if(count($meta['bs_images']) > 1): ?>
        <?php for($i = 1; $i < count($meta['bs_images']); $i++): ?>
            <a class="nodec" target="_blank" href="<?php echo $meta['bs_images'][$i] ?>">
                <img class="thumb boxed-sizing" src="<?php echo $meta['bs_images'][$i] ?>" alt=""  />
            </a>
        <?php endfor; ?>
    <?php endif; ?>
    <div class="basic-info">
        <?php if($meta['bs_address_1']): ?>
        <?php echo Broadstreet_Utility::buildAddressFromMeta($meta); ?>
        <?php endif; ?>
        <br />
        <?php if($meta['bs_address_1']): ?>
        <a target="_blank" href="https://maps.google.com/?q=<?php echo urlencode(Broadstreet_Utility::buildAddressFromMeta($meta, true)) ?>">View map</a><br />
        <?php endif; ?>
        <?php if($meta['bs_phone']): ?>
        <?php echo $meta['bs_phone'] ?><br />
        <?php endif; ?>
        <?php if($meta['bs_website']): ?>
        <a target="_blank"  href="<?php echo $meta['bs_website'] ?>">View website</a>
        <?php endif; ?>
    </div>
    <div class="basic-info">
    <?php if($meta['bs_menu']): ?>
        <div class="section-label"><strong>Menu</strong></div>
        <div><a target="_blank" href="<?php echo $meta['bs_menu'] ?>">View or download a menu </a></div>
    <?php endif; ?>
    </div>
    <?php if($meta['bs_publisher_review']): ?>
    <div class="section-label"><strong>Our Review</strong></div>
    <div><a href="<?php echo $meta['bs_publisher_review'] ?>">Read a <?php bloginfo('name') ?> Review</a></div>
    <?php endif; ?>
    <?php if(Broadstreet_Utility::shouldShowTimes($meta)): ?>
    <div class="section-label"><strong>Hours</strong></div>
    <div class="biz-data">
        <ul id="bs-hours-table">
            <li>
                <div class="day">Monday</div>
                <?php if($meta['bs_monday_open'] && $meta['bs_monday_close']): ?>
                <div class="hours"><?php echo $meta['bs_monday_open'] ?></td><td> - </td><td><?php echo $meta['bs_monday_close'] ?></div>
                <?php else: ?>
                    <div class="hours">Closed</div>
                <?php endif; ?>
            </li>
            <li>
                <div class="day">Tuesday</div>
                <?php if($meta['bs_tuesday_open'] && $meta['bs_tuesday_close']): ?>
                    <div class="hours"><?php echo $meta['bs_tuesday_open'] ?></td><td> - </td><td><?php echo $meta['bs_tuesday_close'] ?></div>
                <?php else: ?>
                    <div class="hours">Closed</div>
                <?php endif; ?>
            </li>
            <li>
                <div class="day">Wednesday</div>
                <?php if($meta['bs_wednesday_open'] && $meta['bs_wednesday_close']): ?>
                    <div class="hours"><?php echo $meta['bs_wednesday_open'] ?></td><td> - </td><td><?php echo $meta['bs_wednesday_close'] ?></div>
                <?php else: ?>
                    <div class="hours">Closed</div>
                <?php endif; ?>
            </li>
            <li>
                <div class="day">Thursday</div>
                <?php if($meta['bs_thursday_open'] && $meta['bs_thursday_close']): ?>
                    <div class="hours"><?php echo $meta['bs_thursday_open'] ?></td><td> - </td><td><?php echo $meta['bs_thursday_close'] ?></div>
                <?php else: ?>
                    <div class="hours">Closed</div>
                <?php endif; ?>
            </li>
            <li>
                <div class="day">Friday</div>
                <?php if($meta['bs_friday_open'] && $meta['bs_friday_close']): ?>
                    <div class="hours"><?php echo $meta['bs_friday_open'] ?></td><td> - </td><td><?php echo $meta['bs_friday_close'] ?></div>
                <?php else: ?>
                    <div class="hours">Closed</div>
                <?php endif; ?>
            </li>
            <li>
                <div class="day">Saturday</div>
                <?php if($meta['bs_saturday_open'] && $meta['bs_saturday_close']): ?>
                    <div class="hours"><?php echo $meta['bs_saturday_open'] ?></td><td> - </td><td><?php echo $meta['bs_saturday_close'] ?></div>
                <?php else: ?>
                    <div class="hours">Closed</div>
                <?php endif; ?>
            </li>
            <li>
                <div class="day">Sunday</div>
                <?php if($meta['bs_sunday_open'] && $meta['bs_sunday_close']): ?>
                    <div class="hours"><?php echo $meta['bs_sunday_open'] ?></td><td> - </td><td><?php echo $meta['bs_sunday_close'] ?></div>
                <?php else: ?>
                    <div class="hours">Closed</div>
                <?php endif; ?>
            </li>
            
        </ul>
    </div>
    <?php endif; ?>
        <?php if($meta['bs_twitter'] || $meta['bs_twitter'] || $meta['bs_yelp']): ?>
        <div class="section-label"><strong>Web</strong></div>
            <?php if($meta['bs_twitter']): ?>
            <a class="nodec" target="_blank" href="<?php echo $meta['bs_twitter'] ?>"><img src="<?php echo Broadstreet_Utility::getImageBaseURL().'twitter.png' ?>" alt="Twitter" width="20" /></a>
            <?php endif; ?>
            <?php if($meta['bs_facebook']): ?>
            <a class="nodec" target="_blank" href="<?php echo $meta['bs_facebook'] ?>"><img src="<?php echo Broadstreet_Utility::getImageBaseURL().'facebook.png' ?>" alt="Facebook" width="20" /></a>
            <?php endif; ?>
            <?php if($meta['bs_gplus']): ?>
            <a class="nodec" target="_blank" href="<?php echo $meta['bs_gplus'] ?>"><img src="<?php echo Broadstreet_Utility::getImageBaseURL().'google.png' ?>" alt="Google Plus" width="20" /></a>
            <?php endif; ?>
            <?php if($meta['bs_yelp']): ?>
            <a class="nodec" target="_blank" href="<?php echo $meta['bs_yelp'] ?>"><img src="<?php echo Broadstreet_Utility::getImageBaseURL().'yelp.png' ?>" alt="Yelp" width="20" /></a>
            <?php endif; ?>
        <?php endif; ?>

</div>
<div id="biz-column-2" class="boxed-sizing">
    <?php if($meta['bs_featured_business']): ?>
    <img style="float: right; padding: 2px 0 4px 4px;" src="<?php echo Broadstreet_Utility::featuredBusinessImage() ?>" />
    <?php endif; ?>
    <?php echo $content; ?>
    
<?php if($meta['bs_video']): ?>
<div class="bs-video">Video</div>
<div class="bs-videoWrapper">
    <?php echo Broadstreet_Utility::setVideoWidth($meta['bs_video'], 350); ?>
</div>
<?php endif; ?>
</div>

<div class="clearfix"></div>