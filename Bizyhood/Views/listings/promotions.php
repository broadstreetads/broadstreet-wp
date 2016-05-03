<div class="row bh_business-header">
    <div class="col-md-8 bh_business-header-title">	
        <h3>Promotions</h3>
    </div>
    <div class="col-md-4 bh_business-search">
    <form action="<?php echo site_url(); ?>/index.php" method="get">
        <input type="hidden" name="page_id" value="<?php echo $list_page_id; ?>">
        <input type="search" class="bh_search-field" placeholder="Search businesses..." name="keywords" title="Search for:" value="">
    </form>
    </div>
</div>
<?php

  if (!empty($promotions)) {
    
    $view_business_page_id = get_page_by_path( "business-overview" )->ID;
        
    ?>
    <div class="row bh_promotion-content promotions_page">	
    <?php
      
      $i = 0;
      foreach($promotions as $promotion) {
                
        if ($i != 0 && ($i%2 == false)) {
          ?>
          </div><div class="row bh_promotion-content promotions_page">	
          <?php
        }    
        $i++;
        
        // set the default logo
        $promotion['business_logo']['image']['url'] = Bizyhood_Utility::getImageBaseURL().'placeholder-logo.jpg';
        $promotion['business_logo']['image_width'] = Bizyhood_Core::BUSINESS_LOGO_WIDTH;
        $promotion['business_logo']['image_height'] = Bizyhood_Core::BUSINESS_LOGO_HEIGHT;
        
        // check date
        $start_date = date('Y-m-d', strtotime($promotion['start'])); // start date
        $tomorrow_date = date('Y-m-d', strtotime('+ 1 days')); // tomorrow date
        $end_date = date('Y-m-d', strtotime($promotion['end'])); // end date
        
        if (strtotime($start_date) < strtotime($tomorrow_date)) {
          // display only the ending date
          $dates = '<span class="promotions_dates">Until '. date_i18n( get_option( 'date_format' ), strtotime($end_date)) .'</span>';
        } elseif (strtotime($start_date) == strtotime($end_date)) {
          // if it is today
          if (strtotime($end_date) == date('Y-m-d', time())) {
            $dates = '<span class="promotions_dates">Promotion running today!</span>';
          } else {
            $dates = '<span class="promotions_dates">Valid on '. date_i18n( get_option( 'date_format' ), strtotime($end_date)) .'</span>';
          }
        } else {
          // display both start and ending day
          $dates = '<span class="promotions_dates">Valid from '. date_i18n( get_option( 'date_format' ), strtotime($start_date)) .' to '. date_i18n( get_option( 'date_format' ), strtotime($end_date)) .'</span>';
        }
        
        // trim the description if needed
        if (str_word_count($promotion['details']) > Bizyhood_Core::EXCERPT_MAX_LENGTH) {
          $promotion['details'] = wp_trim_words($promotion['details'], Bizyhood_Core::EXCERPT_MAX_LENGTH, ' <a href="'. get_permalink( $view_business_page_id ).$promotion['business_slug'].'/'.$promotion['business_identifier'] .'/" title="'. $promotion['business_name'] .' '. __('promotions', 'bizyhood').'">more&hellip;</a>');
        }
        
     ?>
        <div class="col-md-6">
          
          
          <div class="row no-gutter">
            <div class="col-sm-12">
              <a href="<?php echo get_permalink( $view_business_page_id ); ?><?php echo $promotion['business_slug'].'/'.$promotion['business_identifier']; ?>/" title="<?php echo $promotion['business_name'] .' '. __('promotions', 'bizyhood'); ?>">
                <img alt="<?php echo $promotion['name']; ?>" src="<?php echo $promotion['business_logo']['image']['url']; ?>" width="<?php echo $promotion['business_logo']['image_width']; ?>" height="<?php echo $promotion['business_logo']['image_height']; ?>" />
              </a>
            </div>
            <div class="col-sm-12">
              <a title="<?php echo htmlentities($promotion['business_name']); ?>" href="<?php echo get_permalink( $view_business_page_id ); ?><?php echo $promotion['business_slug'].'/'.$promotion['business_identifier']; ?>/">
              <span class="business_name"><?php echo $promotion['business_name']; ?></span>
              </a>
              
              <span class="promotion_name"><?php echo $promotion['name']; ?></span>
              <span class="promotion_description"><?php echo $promotion['details']; ?></span>
              <?php echo $dates; ?>
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