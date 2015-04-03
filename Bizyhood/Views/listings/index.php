<style>
    .bizyhood-index ul li {
        margin-bottom: 3px;
    }
    
    .bizyhood-index ul {
        margin-bottom: 12px;
    }
    
    .bizyhood-index h4 {
        margin-bottom: 4px;
        margin-bottom: 8px;
    }
    
</style>

<div class="bizyhood-index-right" style="width: 40%; box-sizing: border-box; padding-right: 5px; float: right">
    <?php dynamic_sidebar('businesses-right-sidebar') ?>
</div>

<?php echo paginate_links($pagination_args); ?>

<?php $i = 0; foreach($businesses as $business): ?>

<div class="bizyhood-index" style="width: 60%; box-sizing: border-box; padding-right: 5px; float: left">
    <h4><a href="index.php?page_id=<?php echo $view_business_page_id ?>&bizyhood_id=<?php echo $business->bizyhood_id ?>"><?php echo $business->name ?></a></h4>
</div>

<?php $i++; endforeach; ?>
