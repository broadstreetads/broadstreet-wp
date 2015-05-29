<div class="bh_row bh_business-header">
    <div class="bh_col-md-2">
        <a href="<?php echo wp_get_referer(); ?>" class="bh_button"><span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span> Back</a>
    </div>
</div><!-- /.row -->
<div class="bh_row">
    <div class="bh_col-md-2 bh_business-avatar">
    <?php if(false): ?>
        <img src="http://placehold.it/400x320"/>
    <?php endif; ?>
        <div class="bh_list-group">
            <a href="<?php echo $business->feedback_url ?>" class="bh_list-group-item"><span class="glyphicon glyphicon-comment"></span> Customer Feedback</a>
            <?php if($business->events_url): ?>
            <a href="<?php echo $business->events_url ?>" class="bh_list-group-item"><span class="glyphicon glyphicon-calendar"></span> Upcoming Events</a>
            <?php endif; ?>
            <?php if($business->promotions_url): ?>
            <a href="<?php echo $business->promotions_url ?>" class="bh_list-group-item"><span class="glyphicon glyphicon-tag"></span> Our Promotions</a>
            <?php endif; ?>
        </div>
    </div><!-- /.col-md-2 -->
    <div class="bh_col-md-10 bh_business-details">
        <div class="bh_row">
            <div class="bh_main-content bh_col-md-9">
                <h2><?php echo $business->name ?></h2>
                <?php if ( $business->description ) : ?>
                <?php echo wpautop($business->description); ?>
                <?php else : ?>
                <p>No description available</p>
                <?php endif ?>
            </div><!-- /.col-md-9 -->
            <div class="bh_details bh_col-md-3">
                <div class="bh_section">
                    <h5>Location</h5>
                    <p><?php echo $business->address1 ?><br>
                    <?php echo $business->locality ?>, <?php echo $business->region ?> <?php echo $business->postal_code ?><br>
                    <a href="https://maps.google.com?daddr=<?php echo urlencode($business->address1) ?>+<?php echo urlencode($business->locality) ?>+<?php echo urlencode($business->region) ?>+<?php echo urlencode($business->postal_code) ?>" target="_blank">Get directions &rarr;</a></p>
                </div>
                <?php if($business->hours): ?>
                <div class="bh_section">
                    <h5>Hours</h5>
                    <dl class="bh_dl-horizontal">
                        <?php foreach($business->hours as $hour): ?>
                        <dt><?php echo $hour->day_name; ?>:</dt>
                        <dd><?php if($hour->hours_type == 1): ?><?php foreach($hour->hours as $hour_display): ?><?php echo date('g:i a',strtotime($hour_display[0])); ?>&ndash;<?php echo date('g:i a',strtotime($hour_display[1])); ?> <?php endforeach; ?><?php else : ?><?php echo $hour->hours_type_name; ?><?php endif; ?></dd>
                        <?php endforeach; ?>
                    </dl>
                </div>
                <?php endif; ?>
                <?php if($business->telephone): ?>
                <div class="bh_section">
                    <h5>Contact Us</h5>
                    <div class="bh_list-group">
                        <a class="bh_list-group-item">Phone: <?php echo $business->telephone; ?></a>
                        <?php if($business->website): ?>
                        <a class="bh_list-group-item" href="<?php echo $business->website; ?>" target="_blank">Visit our Website &rarr;</a>
                        <?php endif; ?>
                        <?php if($business->social_networks): ?>
                        <?php foreach($business->social_networks as $social_network): ?>
                        <a class="bh_list-group-item" href="<?php echo $social_network->url; ?>"  target="_blank"><?php echo $social_network->cta; ?> &rarr;</a>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div><!-- /.col-md-3 -->
        </div><!-- /.row -->
    </div><!-- /.col-md-10 -->
</div><!-- /.row -->
