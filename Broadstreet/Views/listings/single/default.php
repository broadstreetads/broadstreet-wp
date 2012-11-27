<style>
    #biz-column-1 {
        float: left;
        width: 40%;
    }
    
    #biz-column-1 img {
        background-color: white;
        border: 1px solid #ccc;
        padding: 3px;
    }
    
    #biz-column-2 {
        float: left;
        width: 60%;
        padding-left: 4px;
    }
    
    .boxed-sizing {
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        -webkit-box-sizing: border-box;
    }
    
    .thumb {
        height: 40px;
    }
    
    .biz-data {
        padding-left: 5px;
    }
    
    .biz-data table {
        border: none !important;
    }
    
    .biz-data table tr td {
        padding: 2px !important;
        border: none !important;
    }
    
    #biz-column-1 .basic-info {
        padding: 5px 0px 10px 0;
    }
    
    .closed {
        text-align: center !important;
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
    
    a.nodec {
        text-decoration: none !important;
    }
    
</style>
<?php if($meta['bs_update_source']): ?>
    <div class="sponsored-listing">
        <?php echo $meta['bs_advertisement_html'] ?>
    </div>
<?php endif; ?>
<div id="biz-column-1">
    <?php if(count($meta['bs_images'])): ?>
        <a class="nodec" target="_blank" href="<?php echo $meta['bs_images'][0] ?>">
            <img class="boxed-sizing" src="<?php echo $meta['bs_images'][0] ?>" alt="" width="100%" />
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
        <div><strong>Menu</strong></div>
        <div><a target="_blank" href="<?php echo $meta['bs_menu'] ?>">View or download a menu </a></div>
    <?php endif; ?>
    </div>
    <?php if($meta['bs_publisher_review']): ?>
    <div><a href="<?php echo $meta['bs_publisher_review'] ?>">Read a <?php bloginfo('name') ?> Review</a></div>
    <?php endif; ?>
    <div><strong>Hours</strong></div>
    <div class="biz-data">
        <table>
            <tr>
                <td>Monday:</td>
                <?php if($meta['bs_monday_open'] && $meta['bs_monday_close']): ?>
                <td><?php echo $meta['bs_monday_open'] ?></td><td> - </td><td><?php echo $meta['bs_monday_close'] ?></td>
                <?php else: ?>
                <td colspan="3" class="closed">Closed</td>
                <?php endif; ?>
            </tr>
            <tr>
                <td>Tuesday:</td>
                <?php if($meta['bs_tuesday_open'] && $meta['bs_tuesday_close']): ?>
                <td><?php echo $meta['bs_tuesday_open'] ?></td><td> - </td><td><?php echo $meta['bs_tuesday_close'] ?></td>
                <?php else: ?>
                <td colspan="3" class="closed">Closed</td>
                <?php endif; ?>
            </tr>
            <tr>
                <td>Wednesday:</td>
                <?php if($meta['bs_wednesday_open'] && $meta['bs_wednesday_close']): ?>
                <td><?php echo $meta['bs_wednesday_open'] ?></td><td> - </td><td><?php echo $meta['bs_wednesday_close'] ?></td>
                <?php else: ?>
                <td colspan="3" class="closed">Closed</td>
                <?php endif; ?>
            </tr>
            <tr>
                <td>Thursday:</td>
                <?php if($meta['bs_thursday_open'] && $meta['bs_thursday_close']): ?>
                <td><?php echo $meta['bs_thursday_open'] ?></td><td> - </td><td><?php echo $meta['bs_thursday_close'] ?></td>
                <?php else: ?>
                <td colspan="3" class="closed">Closed</td>
                <?php endif; ?>
            </tr>
            <tr>
                <td>Friday:</td>
                <?php if($meta['bs_friday_open'] && $meta['bs_friday_close']): ?>
                <td><?php echo $meta['bs_friday_open'] ?></td><td> - </td><td><?php echo $meta['bs_friday_close'] ?></td>
                <?php else: ?>
                <td colspan="3" class="closed">Closed</td>
                <?php endif; ?>
            </tr>
            <tr>
                <td>Saturday:</td>
                <?php if($meta['bs_saturday_open'] && $meta['bs_saturday_close']): ?>
                <td><?php echo $meta['bs_saturday_open'] ?></td><td> - </td><td><?php echo $meta['bs_saturday_close'] ?></td>
                <?php else: ?>
                <td colspan="3" class="closed">Closed</td>
                <?php endif; ?>
            </tr>
            <tr>
                <td>Sunday:</td>
                <?php if($meta['bs_sunday_open'] && $meta['bs_sunday_close']): ?>
                <td><?php echo $meta['bs_sunday_open'] ?></td><td> - </td><td><?php echo $meta['bs_sunday_close'] ?></td>
                <?php else: ?>
                <td colspan="3" class="closed">Closed</td>
                <?php endif; ?>
            </tr>
        </table>
    </div>
        <?php if($meta['bs_twitter'] || $meta['bs_twitter'] || $meta['bs_twitter']): ?>
        <div><strong>Web</strong></div>
            <?php if($meta['bs_twitter']): ?>
            <a target="_blank" href="<?php echo $meta['bs_twitter'] ?>"><img src="<?php echo Broadstreet_Utility::getImageBaseURL().'twitter.png' ?>" alt="twitter" width="20" /></a>
            <?php endif; ?>
            <?php if($meta['bs_facebook']): ?>
            <a target="_blank" href="<?php echo $meta['bs_facebook'] ?>"><img src="<?php echo Broadstreet_Utility::getImageBaseURL().'facebook.png' ?>" alt="facebook" width="20" /></a>
            <?php endif; ?>
            <?php if($meta['bs_yelp']): ?>
            <a target="_blank" href="<?php echo $meta['bs_yelp'] ?>"><img src="<?php echo Broadstreet_Utility::getImageBaseURL().'yelp.png' ?>" alt="yelp" width="20" /></a>
            <?php endif; ?>
        <?php endif; ?>
    
</div>
<div id="biz-column-2" class="boxed-sizing">
    <?php echo $content; ?>
</div>