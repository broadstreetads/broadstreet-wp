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

<?php if ( $cuisines ) : ?>
<?php foreach($cuisines as $cuisine => $count): ?>
	<a href="<?php echo site_url(); ?>?page_id=<?php echo $list_page_id; ?>&k=<?php echo urlencode($cuisine); ?>"><?php echo $cuisine; ?></a> (<?php echo $count; ?>) 
<?php endforeach; ?>
<?php else : ?>
<?php foreach($categories as $category): ?>
	<a href="<?php echo site_url(); ?>?page_id=<?php echo $list_page_id; ?>&k=<?php echo urlencode($category); ?>"><?php echo $category; ?></a> 
<?php endforeach; ?>
<?php endif ?>

<hr/>
<?php echo paginate_links($pagination_args); ?>

<?php $i = 0; foreach($businesses as $business): ?>

<div class="bizyhood-index" style="width: 60%; box-sizing: border-box; padding-right: 5px; float: left">
    <h4><a href="index.php?page_id=<?php echo $view_business_page_id ?>&bizyhood_id=<?php echo $business->bizyhood_id ?>"><?php echo $business->name ?></a></h4>
</div>

<?php $i++; endforeach; ?>
