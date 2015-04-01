<!-- Hide the placeholder title of this page -->
<style type="text/css">
.entry-title { display:none; }
</style>

<h2>Cuisine types</h2>

<?php foreach($cuisines as $cuisine => $count): ?>
	<p><a href="<?php echo site_url(); ?>?page_id=<?php echo $list_page_id; ?>&k=<?php echo urlencode($cuisine); ?>"><?php echo $cuisine; ?></a> (<?php echo $count; ?>)</p>
<?php endforeach; ?>