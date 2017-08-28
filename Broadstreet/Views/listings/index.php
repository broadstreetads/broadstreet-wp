<style>
    .broadstreet-index ul li {
        margin-bottom: 3px;
    }
    
    .broadstreet-index ul {
        margin-bottom: 12px;
    }
    
    .broadstreet-index h4 {
        margin-bottom: 4px;
        margin-bottom: 8px;
    }
    
</style>

<div class="broadstreet-index-right" style="width: 40%; box-sizing: border-box; padding-right: 5px; float: right">
    <?php dynamic_sidebar('businesses-right-sidebar') ?>
</div>


<?php $i = 0; foreach($cats_to_posts as $cat_id => $data): ?>

<div class="broadstreet-index" style="width: 60%; box-sizing: border-box; padding-right: 5px; float: left">

    <h4><?php echo htmlentities($data['name']) ?></h4>
    
    <ul>
        <?php foreach($data['posts'] as $post): ?>
        <li><a href="<?php echo get_permalink($post->ID) ?>"><?php echo $post->post_title ?></a></li>
        <?php endforeach; ?>
    </ul>
    
</div>

<?php $i++; endforeach;