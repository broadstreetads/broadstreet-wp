<?php foreach($cuisines as $cuisine => $count): ?>
	<a href="<?php echo site_url(); ?>?page_id=<?php echo $list_page_id; ?>&k=<?php echo urlencode($cuisine); ?>"><?php echo $cuisine; ?></a> (<?php echo $count; ?>) 
<?php endforeach; ?>
<hr/>