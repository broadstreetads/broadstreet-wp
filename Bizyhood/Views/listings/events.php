<div class="row bh_business-header">
    <div class="col-md-12 bh_business-header-title">	
        <h3><?php echo ($business_name != '' ? $business_name.' ' : ''); ?>Events</h3>
    </div>
</div>
<?php

  if (!empty($events)) {
    
    $view_business_page_id  = get_page_by_path( "business-overview" )->ID;
    $view_events_page_id    = get_page_by_path( "business-events" )->ID;

    ?>
    <div class="row bh_event-content events_page">	
    <?php
      // echo '<pre>'.print_r($events, true).'</pre>';
      $i = 0;
      foreach($events as $event) {
        
        $single_event_link    = get_permalink( $view_events_page_id ).$event['business_identifier'].'/'.$event['identifier'].'/';
        $business_events_link = get_permalink( $view_events_page_id ).$event['business_identifier'].'/';
        $business_link        = get_permalink( $view_business_page_id ).$event['business_slug'].'/'.$event['business_identifier'].'/';
        
        $event['admission_info'] = (strtolower(trim($event['admission_info'])) == 'free' ? 0 : $event['admission_info']);

        
        
        if ($i != 0 && ($i%2 == false)) {
          ?>
          </div><div class="row bh_event-content events_page">	
          <?php
        }    
        $i++;
        
        // set the default logo
        // $event['business_logo'] = Bizyhood_Utility::getDefaultLogo();
        
        // get date text
        $dates = Bizyhood_Utility::buildDateTextMicrodata($event['start'], $event['end'], 'Event', 'events');
        
        // trim the description if needed
        if (str_word_count($event['description']) > Bizyhood_Core::EXCERPT_MAX_LENGTH) {
          $event['description'] = wp_trim_words($event['description'], Bizyhood_Core::EXCERPT_MAX_LENGTH, ' <a itemprop="url" href="'. $single_event_link .'" title="More about '. $event['name'] .'">more&hellip;</a>');
        }
        
     ?>
        <div class="col-md-6" itemscope itemtype="http://schema.org/Event">
          <div class="bh_border">
          
          <span class="hidden">
            <span itemprop="location" itemscope itemtype="http://schema.org/Place">
              <span itemprop="name"><?php echo $event['name']; ?></span>
              <span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
                <span itemprop="streetAddress"><?php echo $event['address1'].', '. $event['address2']; ?></span><br />
                <span itemprop="addressLocality"><?php echo $event['locality']; ?></span>, 
                <span itemprop="addressRegion"><?php echo $event['region']; ?></span> 
                <span itemprop="postalCode"><?php echo $event['postal_code']; ?></span>
              </span>
            </span>
            <span itemprop="offers" itemscope itemtype="http://schema.org/Offer">
              <a itemprop="url" href="<?php echo $single_event_link; ?>">
                <span itemprop="validFrom" content="<?php echo date('c',strtotime($event['start'])); ?>"><?php echo date('c',strtotime($event['start'])); ?></span> – 
                <span itemprop="validThrough" content="<?php echo date('c',strtotime($event['end'])); ?>"><?php echo date('c',strtotime($event['end'])); ?></span> – 
                <?php if (isset($event['admission_info']) && !empty($event['admission_info'])) { ?>
                <span itemprop="price" content="<?php echo number_format(str_replace('$', '', $event['admission_info']), 2, '.', ' '); ?>"><span itemprop="priceCurrency" content="USD"><?php echo $event['admission_info']; ?></span></span>
                <?php } ?>
              </a>
            </span>
          </span>
            
            
            <div class="row no-gutter">
            <?php
            // removing until we have the data
            /*
              <div class="col-sm-12">
                <a itemprop="url" href="<?php echo $single_event_link; ?>" title="<?php echo 'More about '.$event['name']; ?>">
                  <img itemprop="image" alt="<?php echo $event['name']; ?>" src="<?php echo $event['business_logo']['image']['url']; ?>" width="<?php echo $event['business_logo']['image_width']; ?>" height="<?php echo $event['business_logo']['image_height']; ?>" />
                </a>
              </div>
            */
            ?>
              <div class="col-sm-12">
                <a title="<?php echo htmlentities($event['business_name']); ?>" href="<?php echo $business_link; ?>">
                <span itemprop="name" class="business_name"><?php echo $event['business_name']; ?></span>
                </a>
                
                <span class="event_name"><a href="<?php echo $single_event_link; ?>" title="<?php echo 'More about '. $event['name']; ?>"><?php echo $event['name']; ?></a></span>
                <span class="event_description" itemprop="description"><?php echo $event['description']; ?></span>
                <?php echo $dates; ?>
              </div>
            </div>
            
          </div>
        </div>
      
<?php
      }
    ?>
    </div>
    <?php
  }
?>