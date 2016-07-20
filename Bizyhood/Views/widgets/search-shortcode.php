<form method="get" action="<?php echo get_permalink(get_option('Bizyhood_Main_page_ID')); ?>" id="bizyhood_search_<?php echo $widget_id; ?>" class="bizyhood_widget bizyhood_search <?php echo $layout; ?>">
  
  <?php if (isset($_GET['cf'])) { ?>
    <input type="hidden" name="cf" value="<?php echo (urldecode($_GET['cf'])); ?>">
  <?php } ?>

  <div class="form_wrap widget_layout_<?php echo $layout; ?> table_div">
    <div class="tr_div">
      <div class="search_fields search_fields_label td_div" <?php echo  ($color_widget_back != '' ? 'style="background-color: '. $color_widget_back .'; border-color: '. $color_widget_back .';"' : '') ; ?>>
        <label for="keywords_<?php echo  $layout ; ?>" <?php echo  ($color_label_font != '' ? 'style="color: '. $color_label_font .'"' : '') ; ?>><?php echo  __('Search for: ', 'bizyhood') ; ?></label>
      </div>
      <div class="search_fields search_fields_input td_div" <?php echo  ($color_widget_back != '' ? 'style="background-color: '. $color_widget_back.'; border-color: '. $color_widget_back .';"' : '') ; ?>>
        <input class="search_keywords" id="keywords_<?php echo  $layout ; ?>" name="keywords" value="<?php echo  (isset($_GET['keywords']) ? esc_attr(stripslashes($_GET['keywords'])) : '') ; ?>" placeholder="<?php echo  __('restaurants, pizza, real-estate etc', 'bizyhood') ; ?>"  <?php echo  (!empty($input_style) ? 'style="'. implode(' ', $input_style) .'"' : ''); ?>/>
      </div>
      <div class="search_fields search_fields_submit td_div" <?php echo  ($color_widget_back != '' ? 'style="background-color: '. $color_widget_back .'; border-color: '. $color_widget_back .';"' : '') ; ?>>
        <input type="submit" class="button btn bizy_search_submit" value="<?php echo  __('Search', 'bizyhood') ; ?>" <?php echo  (!empty($button_style) ? 'style="'.  implode(' ', $button_style) .'"' : '') ; ?>>
      </div>
      <div class="list_your_business arrow_box td_div" <?php echo  ($color_cta_back != '' ? 'style="background-color: '. $color_cta_back .'; border-color: '. $color_cta_back .';"' : '') ; ?>>
        <a href="<?php echo  get_permalink(get_option('Bizyhood_Signup_page_ID')) ; ?>" title="<?php echo  __('List you business', 'bizyhood') ; ?>" >
          <span class="link_row row1" <?php echo  ($color_cta_font != '' ? 'style="color: '. $color_cta_font .';"' : '') ; ?>><?php echo  esc_attr($row1); ?></span>
          <span class="link_row row2" <?php echo  ($color_cta_font != '' ? 'style="color: '. $color_cta_font .';"' : '') ; ?>><?php echo  esc_attr($row2); ?></span>
        </a>
      </div>
    </div>
  </div>
</form>
    