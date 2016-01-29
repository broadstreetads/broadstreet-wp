<div class="bh_row bh_business-header">
    <div class="bh_col-md-8 bh_business-header-title">	
        <h3>Businesses</h3>
    </div>
    <div class="bh_col-md-4 bh_business-search">
    <form action="<?php echo site_url(); ?>/index.php" method="get">
        <input type="hidden" name="page_id" value="<?php echo $list_page_id; ?>">
        <?php if (isset($_GET['cf'])) { ?>
        <input type="hidden" name="cf" value="<?php echo urlencode($_GET['cf']); ?>">
        <?php } ?>
        <input type="search" class="bh_search-field" placeholder="Search businesses..." name="keywords" title="Search for:" value="<?php echo $keywords; ?>">
    </form>
    </div>
</div>
<div class="bh_row">			
    <div class="bh_col-md-3 bh_local-nav">
        <h5>Categories</h5>
        <div class="bh_list-group">
        <?php 
          $category_count = 0;
          if (!empty($categories)) {
            foreach($categories as $category) {
            
              if ($category_count == 19) {
                echo '
                </div>
                <div class="bh_list-group more_list_wrap">
                  <div class="more_list_inner">
                ';
              }
          ?>
              <a class="bh_list-group-item" href="<?php echo get_permalink( $list_page_id ); ?>?cf=<?php echo urlencode($category['term']).($keywords != '' ? '&amp;keywords='.$keywords : ''); ?>" title="<?php echo $category['term']; ?> (<?php echo $category['count']; ?>)">
                  <span class="bh_list-title"><?php echo $category['term']; ?> (<?php echo $category['count']; ?>)</span>
              </a> 
          <?php 
              $category_count++;
            }
          ?>
          </div>
          
          <?php
            if ( $category_count >= 20 ) {
              echo '
              </div>
              <p class="facet_helper_wrap">
                <a href="" title="more categories" class="facet_helper" id="more_categories">more categories</a>
                <a href="" title="less categories" class="facet_helper" id="less_categories">less categories</a>            
              </p>';
            }
            if ( $cf != '' ) {
              echo '<p class="facet_helper_wrap"><a href="'. get_permalink($list_page_id).( $keywords != '' ? '?keywords='.$keywords : '') .'" title="show all" class="facet_helper">show all</a></p>';
            }
          }
        ?>
    </div>
    <div class="bh_col-md-9 bh_results">
        <div class="bh_row">
            <?php if ( !empty($businesses) ) { ?>
            <?php $i = 0; foreach($businesses as $business): ?>
            <div class="bh_col-md-4">
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
        <div class="bh_row">
            <div class="bh_col-md-12">
                <?php echo paginate_links($pagination_args); ?>
            </div><!-- /.col-md-12 -->
        </div><!-- /.row -->
    </div>
</div>
