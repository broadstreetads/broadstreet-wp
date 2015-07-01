<div class="bh_row bh_business-header">
    <div class="bh_col-md-8 bh_business-header-title">	
        <h3>Businesses</h3>
    </div>
    <div class="bh_col-md-4 bh_business-search">
    <form action="<?php echo site_url(); ?>/index.php" method="get">
        <input type="hidden" name="page_id" value="<?php echo $list_page_id; ?>">
        <input type="search" class="bh_search-field" placeholder="Search businesses..." value="" name="keywords" title="Search for:">
    </form>
    </div>
</div>
<div class="bh_row">			
    <div class="bh_col-md-2 bh_local-nav">
        <h5><?php if ( $cuisines ) : ?>Cuisines<?php else : ?>Categories<?php endif ?></h5>
        <div class="bh_list-group">
        <?php if ( $cuisines ) : ?>
        <?php foreach($cuisines as $cuisine => $count): ?>
            <a class="bh_list-group-item" href="<?php echo site_url(); ?>?page_id=<?php echo $list_page_id; ?>&k=<?php echo urlencode($cuisine); ?>">
                <span class="bh_list-title"><?php echo $cuisine; ?></span>
                <span class="bh_badge"><?php echo $count; ?></span>
            </a> 
        <?php endforeach; ?>
        <?php else : ?>
        <?php foreach($categories as $category): ?>
            <a class="bh_list-group-item" href="<?php echo site_url(); ?>?page_id=<?php echo $list_page_id; ?>&k=<?php echo urlencode($category); ?>">
                <span class="bh_list-title"><?php echo $category; ?></span>
            </a> 
        <?php endforeach; ?>
        <?php endif ?>
        </div>
    </div>
    <div class="bh_col-md-10 bh_results">
        <div class="bh_row">
            <?php $i = 0; foreach($businesses as $business): ?>
            <div class="bh_col-md-4">
                <div class="bh_panel">
                    <a href="<?php echo get_permalink( $view_business_page_id ) ?>?bizyhood_id=<?php echo $business->bizyhood_id ?>" class="bh_block-link">
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
        </div><!-- /.row -->
        <div class="bh_row">
            <div class="bh_col-md-12">
                <?php echo paginate_links($pagination_args); ?>
            </div><!-- /.col-md-12 -->
        </div><!-- /.row -->
    </div>
</div>
