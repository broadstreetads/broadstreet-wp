<style>
    .biz-column-1 {
        float: left;
        width: 25%;
    }
    
    .biz-column-1 img {
        background-color: white;
        border: 1px solid #ccc;
        padding: 3px;
    }
    
    .biz-column-2 {
        float: left;
        width: 70%;
        padding-left: 4px;
    }
    
    .boxed-sizing {
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        -webkit-box-sizing: border-box;
    }
    
    .bs-social {
        text-align: right;
    }
    
    .sponsored-listing {
        background: rgb(254,252,234); /* Old browsers */
        background: -moz-linear-gradient(top,  rgba(254,252,234,1) 0%, rgba(241,218,54,1) 100%); /* FF3.6+ */
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(254,252,234,1)), color-stop(100%,rgba(241,218,54,1))); /* Chrome,Safari4+ */
        background: -webkit-linear-gradient(top,  rgba(254,252,234,1) 0%,rgba(241,218,54,1) 100%); /* Chrome10+,Safari5.1+ */
        background: -o-linear-gradient(top,  rgba(254,252,234,1) 0%,rgba(241,218,54,1) 100%); /* Opera 11.10+ */
        background: -ms-linear-gradient(top,  rgba(254,252,234,1) 0%,rgba(241,218,54,1) 100%); /* IE10+ */
        background: linear-gradient(to bottom,  rgba(254,252,234,1) 0%,rgba(241,218,54,1) 100%); /* W3C */
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#fefcea', endColorstr='#f1da36',GradientType=0 ); /* IE6-9 */
        padding: 5px;
        margin-bottom: 10px;
        border-radius: 3px;
        border: 1px solid #ddd;
        text-align: center;
    }
    
</style>
<?php if($meta['bs_update_source']): ?>
    <div class="sponsored-listing">
        <?php echo $meta['bs_advertisement_html'] ?>
    </div>
<?php endif; ?>
<div class="biz-column-1">
    <?php if(count($meta['bs_images'])): ?>
    <img class="boxed-sizing" src="<?php echo $meta['bs_images'][0] ?>" alt="" width="100%" />
    <div></div>
    <?php endif; ?>
    <div class="basic-info">
        <?php if($meta['bs_address']): ?>
            <?php echo nl2br($meta['bs_address']) ?><br />
        <?php endif; ?>
        <?php if($meta['bs_phone']): ?>    
            <?php echo $meta['bs_phone'] ?>
        <?php endif; ?>
    </div>
</div>
<div class="biz-column-2" class="boxed-sizing">
    <?php echo wp_trim_words(strip_tags($content), 100); ?> <a href="<?php the_permalink() ?>">Read More</a>
    <?php $day = strtolower(date('l')); ?>
    <?php if($meta["bs_{$day}_open"]): ?>
        <div><strong>Open today</strong> <?php echo $meta["bs_{$day}_open"] . ' to ' . $meta["bs_{$day}_close"]; ?>.
            <?php if($meta['bs_menu']): ?>
                <a href="<?php echo $meta['bs_menu'] ?>">View a menu</a>
            <?php endif; ?>        
        </div>
        <?php echo get_the_term_list($GLOBALS['post']->ID, Broadstreet_Core::BIZ_TAXONOMY, 'Posted in: ', ', ', '') ?> 
    <?php endif; ?>
    <?php if($meta['bs_twitter'] || $meta['bs_twitter'] || $meta['bs_yelp']): ?>
        <p class="bs-social">
            <?php if($meta['bs_twitter']): ?>
            <a target="_blank" href="<?php echo $meta['bs_twitter'] ?>"><img src="<?php echo Broadstreet_Utility::getImageBaseURL().'twitter.png' ?>" alt="twitter" width="20" /></a>
            <?php endif; ?>
            <?php if($meta['bs_facebook']): ?>
            <a target="_blank" href="<?php echo $meta['bs_facebook'] ?>"><img src="<?php echo Broadstreet_Utility::getImageBaseURL().'facebook.png' ?>" alt="facebook" width="20" /></a>
            <?php endif; ?>
            <?php if($meta['bs_gplus']): ?>
            <a target="_blank" href="<?php echo $meta['bs_gplus'] ?>"><img src="<?php echo Broadstreet_Utility::getImageBaseURL().'google.png' ?>" alt="Google Plus" width="20" /></a>
            <?php endif; ?>
            <?php if($meta['bs_yelp']): ?>
            <a target="_blank" href="<?php echo $meta['bs_yelp'] ?>"><img src="<?php echo Broadstreet_Utility::getImageBaseURL().'yelp.png' ?>" alt="yelp" width="20" /></a>
            <?php endif; ?>
        </p>
    <?php endif; ?>
</div>