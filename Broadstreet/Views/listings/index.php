<style>
    .broadstreet-index ul li {
        margin-bottom: 3px;
    }
    
    .broadstreet-index ul {
        margin-bottom: 12px;
    }
    
    .broadstreet-index h4 {
        margin-bottom: 8px;
    }
    
</style>

<?php $i = 0; foreach($cats_to_posts as $cat_id => $data): ?>

<div class="broadstreet-index" style="width: 50%; box-sizing: border-box; padding-right: 5px; float: <?php echo $i % 2 == 0 ? 'left' : 'right' ?>;">

    <h4><?php echo htmlentities($data['name']) ?></h4>
    
    <ul>
        <?php foreach($data['posts'] as $post): ?>
        <li><a href="<?php echo get_permalink($post->ID) ?>"><?php echo htmlentities($post->post_title) ?></a></li>
        <?php endforeach; ?>
    </ul>
    
</div>

<?php $i++; endforeach; ?>
