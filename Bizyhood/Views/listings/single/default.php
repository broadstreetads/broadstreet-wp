<?php
  if (isset($business->total_count) && $business->total_count == 0) {
    global $wp_query;
    $wp_query->set_404();
    status_header( 404 );
    get_header();
    get_template_part( 404 ); 
    get_footer();
    exit();
    
  }
  
  // check and create the backlink
  $backlink = wp_get_referer();
  if (wp_get_referer() == get_site_url() . $_SERVER["REQUEST_URI"] || wp_get_referer() == false) {
    $backlink = get_permalink(Bizyhood_Utility::getOption(Bizyhood_Core::KEY_MAIN_PAGE_ID));
  }
?>
<div class="bh_row bh_business-header">
    <div class="bh_col-md-2">
        <a href="<?php echo $backlink; ?>" class="bh_button"><span class="entypo-left" aria-hidden="true"></span> Back</a>
    </div>
</div><!-- /.row -->
<div class="bh_row" itemscope itemtype="http://schema.org/LocalBusiness">
    <div class="bh_col-md-2">
        <?php if($business->business_logo): ?>
        <div class="bh_business-avatar">
            <img src="<?php echo $business->business_logo->image->url ?>"/>
        </div>
        <?php endif; ?>
        <?php if(!$business->claimed): ?>
        <div class="bh_claim">
            <h6>Is this your business?</h6>
            <a href="<?php echo get_permalink( $signup_page_id ); ?>" class="bh_button">Claim it now</a>
        </div>
        <?php endif; ?>
        <div class="bh_list-group">
            <a href="<?php echo $business->feedback_url ?>" class="bh_list-group-item" target="_blank"><span class="entypo-comment"></span> Customer Feedback</a>
            <?php if($business->events_url): ?>
            <a href="<?php echo $business->events_url ?>" class="bh_list-group-item" target="_blank"><span class="entypo-calendar"></span> Upcoming Events</a>
            <?php endif; ?>
            <?php if($business->promotions_url): ?>
            <a href="<?php echo $business->promotions_url ?>" class="bh_list-group-item" target="_blank"><span class="entypo-tag"></span> Our Promotions</a>
            <?php endif; ?>
        </div>
    </div><!-- /.col-md-2 -->
    <div class="bh_col-md-10 bh_business-details">
        <div class="bh_row">
            <div class="bh_main-content bh_col-md-9">
                <h2 itemprop="name"><?php echo $business->name ?></h2>
                <?php if ( $business->business_images ) : ?>
                <div class="bh_image-gallery">
                    <div itemscope itemtype="http://schema.org/ImageGallery" class="bh_gallery-list clearfix">
                        <?php foreach($business->business_images as $business_image): ?>
                        <figure itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject" class="bh_gallery-thumb">
                            <a href="<?php echo $business_image->image->url; ?>" itemprop="contentUrl" data-size="<?php echo $business_image->image->width; ?>x<?php echo $business_image->image->height; ?>">
                                <img src="<?php echo $business_image->image->url; ?>" itemprop="thumbnail" alt="<?php echo $business_image->title; ?>" />
                            </a>
                            <figcaption><?php echo $business_image->title; ?></figcaption>
                        </figure>
                        <?php endforeach; ?>
                    </div>
	      	</div>
                <?php endif ?>
                <div class="bh_alert">
                    <div class="bh_row">
			<div class="bh_col-md-8">
		            <p>Support your local businesses by giving them feedback.</p>
		        </div>
		        <div class="bh_col-md-4">
		            <a href="<?php echo $business->bizyhood_url ?>" class="bh_button" target="_blank">Give Feedback</a>
		        </div>
                    </div>
                </div>
                <?php if ( $business->description ) : ?>
                <p itemprop="description"><?php echo wpautop($business->description); ?></p>
                <?php endif ?>
            </div><!-- /.col-md-9 -->
            <div class="bh_details bh_col-md-3">
                <div class="bh_section" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
                    <h5>Location</h5>
                    <p itemprop="streetAddress"><?php echo $business->address1 ?><br>
                    <span itemprop="addressLocality"><?php echo $business->locality ?></span>, <span itemprop="addressRegion"><?php echo $business->region ?></span> <span itemprop="postalCode"><?php echo $business->postal_code ?></span><br>
                    <a itemprop="map" href="https://maps.google.com?daddr=<?php echo urlencode($business->address1) ?>+<?php echo urlencode($business->locality) ?>+<?php echo urlencode($business->region) ?>+<?php echo urlencode($business->postal_code) ?>" target="_blank">Get directions &rarr;</a></p>
                </div>
                <?php if($business->hours): ?>
                <div class="bh_section" itemprop="openingHoursSpecification" itemscope itemtype="http://schema.org/OpeningHoursSpecification">
                    <h5>Hours</h5>
                    <dl class="bh_dl-horizontal">
                        <?php foreach($business->hours as $hour): ?>
                        <dt><link itemprop="dayOfWeek" href="http://purl.org/goodrelations/v1#<?php echo $hour->day_name; ?>"><?php echo $hour->day_name; ?>:</dt>
                        <dd><?php if($hour->hours_type == 1): ?><?php foreach($hour->hours as $hour_display): ?><span itemprop="opens" content="<?php echo date('c',strtotime($hour_display[0])); ?>"><?php echo date('g:i a',strtotime($hour_display[0])); ?></span>&ndash;<span itemprop="closes" content="<?php echo date('c',strtotime($hour_display[1])); ?>"><?php echo date('g:i a',strtotime($hour_display[1])); ?></span> <?php endforeach; ?><?php else : ?><?php echo $hour->hours_type_name; ?><?php endif; ?></dd>
                        <?php endforeach; ?>
                    </dl>
                </div>
                <?php endif; ?>
                <?php if($business->telephone): ?>
                <div class="bh_section">
                    <h5>Contact Us</h5>
                    <div class="bh_list-group">
                        <a class="bh_list-group-item">Phone: <span itemprop="telephone"><?php echo $business->telephone; ?></span></a>
                        <?php if($business->website): ?>
                        <a class="bh_list-group-item" itemprop="url" href="<?php echo $business->website; ?>" target="_blank">Visit our Website &rarr;</a>
                        <?php endif; ?>
                        <?php if($business->social_networks): ?>
                        <?php foreach($business->social_networks as $social_network): ?>
                        <a class="bh_list-group-item" itemprop="sameAs" href="<?php echo $social_network->url; ?>"  target="_blank"><?php echo $social_network->cta; ?> &rarr;</a>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div><!-- /.col-md-3 -->
        </div><!-- /.row -->
    </div><!-- /.col-md-10 -->
</div><!-- /.row -->
<!-- Root element of PhotoSwipe. Must have class pswp. -->
<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
    <!-- Background of PhotoSwipe. It's a separate element as animating opacity is faster than rgba(). -->
    <div class="pswp__bg"></div>
    <!-- Slides wrapper with overflow:hidden. -->
    <div class="pswp__scroll-wrap">
        <!-- Container that holds slides. 
            PhotoSwipe keeps only 3 of them in the DOM to save memory.
            Don't modify these 3 pswp__item elements, data is added later on. -->
        <div class="pswp__container">
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
        </div>
        <!-- Default (PhotoSwipeUI_Default) interface on top of sliding area. Can be changed. -->
        <div class="pswp__ui pswp__ui--hidden">
            <div class="pswp__top-bar">
                <!--  Controls are self-explanatory. Order can be changed. -->
                <div class="pswp__counter"></div>
                <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>
                <button class="pswp__button pswp__button--share" title="Share"></button>
                <button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>
                <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
                <!-- Preloader demo http://codepen.io/dimsemenov/pen/yyBWoR -->
                <!-- element will get class pswppreloaderactive when preloader is running -->
                <div class="pswp__preloader">
                    <div class="pswp__preloader__icn">
                      <div class="pswp__preloader__cut">
                        <div class="pswp__preloader__donut"></div>
                      </div>
                    </div>
                </div>
            </div>
            <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                <div class="pswp__share-tooltip"></div> 
            </div>
            <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)">
            </button>
            <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)">
            </button>
            <div class="pswp__caption">
                <div class="pswp__caption__center"></div>
            </div>
        </div>
    </div>
</div>
