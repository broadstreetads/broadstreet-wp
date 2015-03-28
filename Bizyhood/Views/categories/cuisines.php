<!-- Hide the placeholder title of this page -->
<style type="text/css">
.entry-title { display:none; }
</style>

<h2>Cuisine types</h2>

<?php foreach($cuisines as $cuisine => $count): ?>
	<p><?php echo $cuisine; ?> (<?php echo $count; ?>)</p>
<?php endforeach; ?>