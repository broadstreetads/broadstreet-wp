<?php foreach($categories as $category): ?>
	<a href="<?php echo site_url(); ?>?page_id=<?php echo $list_page_id; ?>&k=<?php echo urlencode($category); ?>"><?php echo $category; ?></a> 
<?php endforeach; ?>
<hr/>