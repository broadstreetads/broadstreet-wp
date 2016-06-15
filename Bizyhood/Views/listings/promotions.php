<div class="row bh_business-header">
    <div class="col-md-12 bh_business-header-title">	
        <h3><?php echo ($business_name != '' ? $business_name.' ' : ''); ?>Promotions</h3>
    </div>
</div>
<?php

  if (!empty($promotions)) {
    
    $view_business_page_id    = get_page_by_path( "business-overview" )->ID;
    $view_promotions_page_id  = get_page_by_path( "business-promotions" )->ID;
        
    ?>
    <div class="row bh_promotion-content promotions_page">	
    <?php
      
      $i = 0;
      foreach($promotions as $promotion) {
        
        $single_promotion_link    = get_permalink( $view_promotions_page_id ).$promotion['business_identifier'].'/'.$promotion['identifier'].'/';
        $business_promotions_link = get_permalink( $view_promotions_page_id ).$promotion['business_identifier'].'/';
        $business_link            = get_permalink( $view_business_page_id ).$promotion['business_slug'].'/'.$promotion['business_identifier'].'/';
                
        if ($i != 0 && ($i%2 == false)) {
          ?>
          </div><div class="row bh_promotion-content promotions_page">	
          <?php
        }    
        $i++;
        
        // set the default logo
        //$promotion['business_logo'] = Bizyhood_Utility::getDefaultLogo();
        
        // get date text
        $dates = Bizyhood_Utility::buildDateText($promotion['start'], $promotion['end'], 'Promotion', 'promotions');
        
        // trim the description if needed
        if (str_word_count($promotion['details']) > Bizyhood_Core::EXCERPT_MAX_LENGTH) {
          $promotion['details'] = wp_trim_words($promotion['details'], Bizyhood_Core::EXCERPT_MAX_LENGTH, ' <a href="'. get_permalink( $view_business_page_id ).$promotion['business_slug'].'/'.$promotion['business_identifier'] .'/" title="'. $promotion['business_name'] .' '. __('promotions', 'bizyhood').'">more&hellip;</a>');
        }
        
     ?>
        <div class="col-md-6">
          <div class="bh_border">
          
            <div class="row no-gutter">
            <?php
            // removing until we have the data
            /*
              <div class="col-sm-12">
                <a href="<?php echo get_permalink( $view_business_page_id ); ?><?php echo $promotion['business_slug'].'/'.$promotion['business_identifier']; ?>/" title="<?php echo $promotion['business_name'] .' '. __('promotions', 'bizyhood'); ?>">
                  <img alt="<?php echo $promotion['name']; ?>" src="<?php echo $promotion['business_logo']['image']['url']; ?>" width="<?php echo $promotion['business_logo']['image_width']; ?>" height="<?php echo $promotion['business_logo']['image_height']; ?>" />
                </a>
              </div>
            */
            ?>
              <div class="col-sm-12">
                <a title="<?php echo htmlentities($promotion['business_name']); ?>" href="<?php echo get_permalink( $view_business_page_id ); ?><?php echo $promotion['business_slug'].'/'.$promotion['business_identifier']; ?>/">
                <span class="business_name"><?php echo $promotion['business_name']; ?></span>
                </a>
                
                
                <span class="promotion_name">
                  <a href="<?php echo $single_promotion_link; ?>" title="<?php echo 'More about '. $promotion['name']; ?>"><?php echo $promotion['name']; ?></a>
                </span>
                <span class="promotion_description"><?php echo $promotion['details']; ?></span>
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