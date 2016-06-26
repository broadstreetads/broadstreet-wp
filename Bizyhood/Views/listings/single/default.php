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
  
  // Get lat and long by address         
  $address = urlencode($business->address1).'+'.urlencode($business->locality).'+'.urlencode($business->region).'+'.urlencode($business->postal_code);
  $geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$address.'&sensor=false');
  $output= json_decode($geocode);
  $latitude = $output->results[0]->geometry->location->lat;
  $longitude = $output->results[0]->geometry->location->lng;
  
  // check and create the backlink
  $backlink = wp_get_referer();
  if (wp_get_referer() == get_site_url() . $_SERVER["REQUEST_URI"] || wp_get_referer() == false) {
    $backlink = get_permalink(Bizyhood_Utility::getOption(Bizyhood_Core::KEY_MAIN_PAGE_ID));
  }
  
?>
<div class="bizyhood_wrap unclaimed" itemscope itemtype="http://schema.org/LocalBusiness">
  <div class="row zero-gutter bh_headline_row">
    <div class="col-md-9">
      <h2 itemprop="name"><?php echo $business->name ?></h2>
    </div>
    <div class="col-md-3">
      <a href="<?php echo $backlink; ?>" class="btn-inline pull-right hidden-xs hidden-sm"><span class="entypo-left" aria-hidden="true"></span> Back</a>
    </div>
  </div>

  
    
  <div class="row rowgrid zero-gutter main_business_info sameheight">
    <div class="col-md-8 feedback_cta">
      <div class="bh_table">
        <div class="bh_tablerow">
          <div class="bh_tablecell">
          
            <div class="bh_alert text-center">
              <h4 class="h2">IS THIS YOUR BUSINESS?</h4>
              <a href="https://business.bizyhood.com/accounts/signup/" class="btn btn-info" <?php echo $colors['style']; ?> target="_blank">Claim it now!</a>
            </div>
          </div>  
        </div>  

              
        <div class="tablesplit"></div>
              

          <div class="bh_tablerow">
            <div class="bh_tablecell">
            
              <div class="bh_alert text-center">
                <h4 class="h2">SUPPORT YOUR LOCAL BUSINESS</h4>
                <a href="<?php echo $business->bizyhood_url ?>" class="btn btn-info" <?php echo $colors['style']; ?> target="_blank">Give Feedback</a>
              </div>
            </div>
          </div>
        </div>      
    </div><!-- /.col-md-4 -->
    
    
    <div class="col-md-4">
    
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
          <p class="bh_staticmap text-center" <?php echo $colors['style']; ?>>
            <a class="clearfix" <?php echo $colors['stylefont']; ?> itemprop="url" href="https://maps.google.com?daddr=<?php echo urlencode($business->address1) ?>+<?php echo urlencode($business->locality) ?>+<?php echo urlencode($business->region) ?>+<?php echo urlencode($business->postal_code) ?>" target="_blank">
              <span itemprop="image">
                <img src="https://maps.googleapis.com/maps/api/staticmap?zoom=14&scale2&size=400x200&maptype=roadmap&markers=color:red%7C<?php echo $latitude; ?>,<?php echo $longitude; ?>&key=<?php echo Bizyhood_Core::GOOGLEMAPS_API_KEY; ?>" />
              </span>
              <span class="bh_directions">Get Directions</span>
            </a>
          </p>
        </div>
      </div>
      
    </div>
  </div><!-- /.row -->
  
  <a href="<?php echo $backlink; ?>" class="btn-inline pull-right"><span class="entypo-left" aria-hidden="true"></span> Back</a>