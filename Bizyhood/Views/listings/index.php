<?php 
  if ($search_widget != 'off') {
    echo do_shortcode('[bh-search]');
  }
?>
<div class="row bh_business-header">
    <div class="col-md-12 bh_business-header-title">	
        <h3>Businesses</h3>
    </div>
</div>
<div class="row">
    <?php if ($show_category_facets) { ?>
    <div class="col-md-3 bh_local-nav">
        <h5>Categories</h5>
        <?php 
          $category_count = 0;
          if (!empty($categories)) {
            ?>
            <div class="bh_list-group">
            <?php
            foreach($categories as $category) {
            
              if ($category_count == 19) {
                ?>
                </div>
                <div class="bh_list-group more_list_wrap">
                  <div class="more_list_inner">
                <?php
              }
          ?>
              <a class="bh_list-group-item <?php echo (isset($_GET['cf']) && rawurlencode($_GET['cf']) == rawurlencode($category['term']) ? 'selected_category' : ''); ?>" href="<?php echo get_permalink( $list_page_id ); ?>?cf=<?php echo rawurlencode($category['term']).($keywords != '' ? '&amp;keywords='.$keywords : ''); ?>" title="<?php echo $category['term']; ?> (<?php echo $category['count']; ?>)">
                  <span class="bh_list-title"><?php echo (strlen($category['term']) > Bizyhood_Core::CATEGORIES_LENGTH ? substr($category['term'], 0, 35).'&hellip;' : $category['term']); ?> (<?php echo $category['count']; ?>)</span>
              </a> 
          <?php 
              $category_count++;
            }
          ?>
          </div>
          
          <?php
            if ( $category_count >= 20 ) {
              ?>
              </div>
              <p class="facet_helper_wrap">
                <a href="" title="more categories" class="facet_helper" id="more_categories">more categories</a>
                <a href="" title="less categories" class="facet_helper" id="less_categories">less categories</a>            
              </p>
              <?php
            }
          }
        ?>
    </div>
    <?php } ?>
    <div class="col-md-<?php echo ($show_category_facets ? 9 : 12); ?> bh_results">
        <div class="row">
            <?php if ( !empty($businesses) ) { ?>
            <?php $i = 0; foreach($businesses as $business): ?>
            <div class="col-md-4">
                <div class="bh_panel">        
                    <a href="<?php echo get_permalink( $view_business_page_id ); ?><?php echo sanitize_title($business->name).'-'.sanitize_title($business->locality).'-'.sanitize_title($business->region).'-'.sanitize_title($business->postal_code) .'/'.$business->bizyhood_id ?>/" class="bh_block-link">
                        <h5><?php echo $business->name ?></h5>
                        <div class="bh_address">
                            <p><?php echo $business->address1 ?></p>
                            <p><?php echo $business->locality ?>, <?php echo $business->region ?> <?php echo $business->postal_code ?></p>
                            <p><?php echo $business->telephone ?></p>
                        </div>
                    </a>
                </div>
            </div><!-- /.col-md-4 -->
            <?php $i++; endforeach; ?>
            <?php } else { ?>
            <p>There were no results for your search.</p>
            <?php } ?>
        </div><!-- /.row -->
        <div class="row">
            <div class="col-md-12">
                <?php echo paginate_links($pagination_args); ?>
            </div><!-- /.col-md-12 -->
        </div><!-- /.row -->
    </div>
</div>
