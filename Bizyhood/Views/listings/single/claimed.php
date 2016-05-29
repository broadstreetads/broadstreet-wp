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
  
  $footer_columns = 12;
  if (isset($business->latest_event) && !empty($business->latest_event)) {
    $footer_columns = $footer_columns - 3;
  }
  if (isset($business->news) && !empty($business->news)) {
    $footer_columns = $footer_columns - 3;
  }
  if (isset($business->latest_promotion) && !empty($business->latest_promotion)) {
    $footer_columns = $footer_columns - 3;
  }
  if (isset($business->feedback) && !empty($business->feedback)) {
    $footer_columns = $footer_columns - 3;
  }
  
  $location_column_width = 3;
  if(!isset($business->hours) || empty($business->hours)) {
    $location_column_width = 6;
  }
  
  // Get lat and long by address         
  $address = urlencode($business->address1).'+'.urlencode($business->locality).'+'.urlencode($business->region).'+'.urlencode($business->postal_code);
  $geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$address.'&sensor=false');
  $output= json_decode($geocode);
  $latitude = $output->results[0]->geometry->location->lat;
  $longitude = $output->results[0]->geometry->location->lng;

?>
<div class="bizyhood_wrap claimed" itemscope itemtype="http://schema.org/LocalBusiness">
  <div class="row zero-gutter bh_headline_row">
    <div class="col-md-9">
      <h2 itemprop="name"><?php echo $business->name ?></h2>
    </div>
    <div class="col-md-3">
      <a href="<?php echo get_permalink(Bizyhood_Utility::getOption(Bizyhood_Core::KEY_MAIN_PAGE_ID)); ?>" class="btn-inline pull-right hidden-xs hidden-sm"><span class="entypo-left" aria-hidden="true"></span> See all listings</a>
    </div>
  </div>

  <div class="row zero-gutter">
    <div class="col-md-8">
      <?php 
        if (!empty($business->categories)) {
        
          $categories = array();
          foreach ($business->categories as $category) {
            $categories[] = '
              <p>
                <a class="bh_category_link" href="'. get_permalink( $list_page_id ) .'?cf='. rawurlencode($category) .'" title="'. $category .'">
                  <span class="bh_category_title">'. $category .'</span>
                </a>
              </p>
            ';
          }
      ?>
      Filed in: <?php echo implode(' | ', $categories); ?>
      <?php
        }
      ?>
    </div>
    <div class="col-md-4">
      <a href="<?php echo $business->bizyhood_url ?>" class="btn btn-info btn-block">Do you recommend this business?</a>
    </div>
  </div>
    
  <div class="row rowgrid zero-gutter main_business_info sameheight">
    <div class="col-md-6">
      <div class="column-inner">
        <?php if($business->business_logo) {?>
          <div itemprop="logo" class="bh_business-avatar pull-left">
              <img src="<?php echo $business->business_logo->image->url ?>"/>
          </div>
        <?php } ?>
        <?php if ( $business->description ) { ?>
          <div itemprop="description"><?php echo wpautop($business->description); ?></div>
        <?php } else { ?>
          <div itemprop="description" class="text-muted"><?php echo wpautop('This business has not yet added a description.'); ?></div>
        <?php } ?>
      </div>
      
      <?php if ( $business->business_images ) { ?>
      <div class="column-inner">
        <div class="bh_image-gallery">
            <div itemscope itemtype="http://schema.org/ImageGallery" class="bh_gallery-list clearfix">
                <?php foreach($business->business_images as $business_image) { ?>
                <figure itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject" class="bh_gallery-thumb">
                    <a href="<?php echo $business_image->image->url; ?>" itemprop="contentUrl" data-size="<?php echo $business_image->image->width; ?>x<?php echo $business_image->image->height; ?>">
                        <img src="<?php echo $business_image->image->url; ?>" itemprop="thumbnail" alt="<?php echo $business_image->title; ?>" />
                    </a>
                    <figcaption><?php echo $business_image->title; ?></figcaption>
                </figure>
                <?php } ?>
            </div>
        </div>
      </div>
        <?php } ?>
    </div><!-- /.col-md-6 -->
    
    <div class="col-md-<?php echo $location_column_width; ?>">
    
      <div class="column-inner">
        <?php if($business->telephone) { ?>
          <p>Call us: <a href="tel:<?php echo $business->telephone; ?>" itemprop="telephone"><?php echo $business->telephone; ?></a></p>
        <?php } ?>
        <?php if($business->website) { ?>
          <p>Visit: <a class="bh_site_link" itemprop="url" href="<?php echo $business->website; ?>" target="_blank"><?php echo str_replace(array('http://', 'https://', 'www.'), array('','',''), $business->website); ?></a></p>
        <?php } ?>
        
        <?php if($business->social_networks) { ?>
          <?php foreach($business->social_networks as $social_network) { 
              if (strtolower($social_network->name) == 'google') {
                $social_network->name = 'gplus';
              }
            ?>
            <a class="bh_social_link" itemprop="sameAs" href="<?php echo $social_network->url; ?>" title="<?php echo $social_network->name; ?>" target="_blank">
              <span class="entypo-<?php echo strtolower($social_network->name); ?>"></span>
            </a>
          <?php } ?>
        <?php } ?>
      </div>
      
      <div class="tablesplit"></div>
      
      <div class="column-inner">
        <div class="bh_section" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
            <h5>Location</h5>
            <p class="bh_addresstext">
              <span itemprop="streetAddress"><?php echo $business->address1 ?></span><br />
              <span itemprop="addressLocality"><?php echo $business->locality ?></span>, 
              <span itemprop="addressRegion"><?php echo $business->region ?></span> 
              <span itemprop="postalCode"><?php echo $business->postal_code ?></span>
            </p>
        </div>
        <div class="bh_section bh_map_wrap" itemprop="hasMap" itemtype="http://schema.org/Map">
          <p class="bh_staticmap text-center">
            <a class="clearfix" itemprop="url" href="https://maps.google.com?daddr=<?php echo urlencode($business->address1) ?>+<?php echo urlencode($business->locality) ?>+<?php echo urlencode($business->region) ?>+<?php echo urlencode($business->postal_code) ?>" target="_blank">
              <span itemprop="image">
                <img src="https://maps.googleapis.com/maps/api/staticmap?zoom=14&scale2&size=400x200&maptype=roadmap&markers=color:red%7C<?php echo $latitude; ?>,<?php echo $longitude; ?>&key=<?php echo Bizyhood_Core::GOOGLEMAPS_API_KEY; ?>" />
              </span>
              <span class="bh_directions">Get Directions</span>
            </a>
          </p>
        </div>
      </div>
      
    </div>
    
    
    <?php if($business->hours): ?>
      <div class="col-md-3 business_hours">
        <div class="column-inner">
            <div class="bh_section" itemprop="openingHoursSpecification" itemscope itemtype="http://schema.org/OpeningHoursSpecification">
                <h5>Hours</h5>
                <dl class="bh_dl-horizontal">
                    <?php foreach($business->hours as $hour): ?>
                    <dt><link itemprop="dayOfWeek" href="http://purl.org/goodrelations/v1#<?php echo $hour->day_name; ?>"><?php echo $hour->day_name; ?>:</dt><br />
                    <dd><?php if($hour->hours_type == 1): ?><?php foreach($hour->hours as $hour_display): ?><span itemprop="opens" content="<?php echo date('c',strtotime($hour_display[0])); ?>"><?php echo date('g:i a',strtotime($hour_display[0])); ?></span>&ndash;<span itemprop="closes" content="<?php echo date('c',strtotime($hour_display[1])); ?>"><?php echo date('g:i a',strtotime($hour_display[1])); ?></span> <?php endforeach; ?><?php else : ?><?php echo $hour->hours_type_name; ?><?php endif; ?></dd>
                    <?php endforeach; ?>
                </dl>
            </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
  
  
  <div class="row rowgrid zero-gutter bh_infoboxes sameheight">
  
    <?php if (isset($business->latest_event) && !empty($business->latest_event)) { ?>
      <div class="col-md-3 latest_events bh_infobox event-wrapper" itemscope itemtype="http://schema.org/Event">
            
        <h3>Upcoming Events</h3>
        <div class="column-inner">
        
            <span class="hidden" itemprop="location" itemscope itemtype="http://schema.org/Place">
              <span itemprop="name"><?php echo $business->name ?></span>
              <span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
                <span itemprop="streetAddress"><?php echo $business->latest_event->address1.', '. $business->latest_event->address2 ?></span><br />
                <span itemprop="addressLocality"><?php echo $business->latest_event->locality ?></span>, 
                <span itemprop="addressRegion"><?php echo $business->latest_event->region ?></span> 
                <span itemprop="postalCode"><?php echo $business->latest_event->postal_code ?></span>
              </span>
            </span>

            <span class="hidden">
              <img itemprop="image" src="<?php echo $business->business_logo->image->url ?>"/>
              <span itemprop="description"><?php echo $business->latest_event->description; ?></span>
              <time itemprop="endDate" datetime="<?php echo date('c', strtotime($business->latest_event->end));?>"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $business->latest_event->end ) ); ?></time>
            </span>
            
            <div class="bh_alert">
              <h4 itemprop="name"><?php echo $business->latest_event->name; ?></h4>
              <dl class="bh_dl-horizontal">
                <dt>Date</dt><br />
                <dd><time itemprop="startDate" datetime="<?php echo date('c', strtotime($business->latest_event->start));?>"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $business->latest_event->start ) ); ?></time></dd>
              </dl>
              <a itemprop="url" href="<?php echo $business->latest_event->details_url; ?>" title="<?php echo $business->latest_event->name; ?> details" target="_blank">View Details &rarr;</a>
            </div>
            
            <a itemprop="url" class="btn btn-info" href="<?php echo $business->events_url; ?>" title="All <?php echo $business->name; ?> events">All Events</a>
        </div>
      </div>
    <?php } ?>
    
    
    <?php if (isset($business->news) && !empty($business->news)) { ?>
      <div class="col-md-3 latest_news bh_infobox">
        <h3>In the news</h3>
        <div class="column-inner">
            
            <div class="bh_alert">
              <h4></h4>
              <a href="#" title="read more" target="_blank">Read More &rarr;</a>
            </div>
            
            <a class="btn btn-info" href="#" title="All news">All News</a>
        </div>
      </div>
    <?php } ?>
    
    
    <?php if (isset($business->latest_promotion) && !empty($business->latest_promotion)) { ?>
      <div class="col-md-3 latest_promotion bh_infobox">
        <h3>Promotions</h3>
        <div class="column-inner">
            
            <div class="bh_alert">
              <h4><?php echo $business->latest_promotion->name; ?></h4>
              <dl class="bh_dl-horizontal">
                <dt>Date</dt>
                <dd><time itemprop="startDate" datetime="<?php echo date('c', strtotime($business->latest_promotion->start));?>"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $business->latest_promotion->start ) ); ?></time></dd>
              </dl>
              <a href="<?php echo $business->latest_promotion->details_url; ?>" title="<?php echo $business->latest_promotion->name; ?> details" target="_blank">View Details &rarr;</a>
            </div>
            
            <a class="btn btn-info" href="<?php echo $business->promotions_url; ?>" title="All <?php echo $business->name; ?> events">All Events</a>
        </div>
      </div>
    <?php } ?>
    
    
    <?php if (isset($business->latest_feedback) && !empty($business->latest_feedback)) { ?>
      <div class="col-md-3 latest_feedback bh_infobox">
        <h3>Customer Feedback</h3>
        <div class="column-inner">
            
            <div class="bh_alert">
              <h4><?php echo $business->latest_feedback->name; ?></h4>
            </div>
            
            <a class="btn btn-info" href="<?php echo $business->feedback_url; ?>" title="All <?php echo $business->name; ?> feedback">All Feedback</a>
        </div>
      </div>
    <?php } ?>
    
    
    <?php 
    // if not all 4 columns were used
    if ($footer_columns > 0) {
    ?>

      <div class="col-md-<?php echo $footer_columns; ?> feedback_cta">        
        <div class="bh_table">
          <div class="bh_tablerow">
            <div class="bh_tablecell">
              <div class="bh_alert text-center">
                <h4 class="h2">SUPPORT YOUR LOCAL BUSINESS</h4>
                <a href="<?php echo $business->bizyhood_url ?>" class="btn btn-info" target="_blank">Give Feedback</a>
              </div>
            </div>
          </div>
        </div>
      </div>
      
    <?php
    }
    ?>
    
    

  </div><!-- /.row -->
  
  <a href="<?php echo get_permalink(Bizyhood_Utility::getOption(Bizyhood_Core::KEY_MAIN_PAGE_ID)); ?>" class="btn-inline pull-right"><span class="entypo-left" aria-hidden="true"></span> See all listings</a>
  
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
