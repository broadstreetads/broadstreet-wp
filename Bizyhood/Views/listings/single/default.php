<div class="bh_row bh_business-header">
    <div class="bh_col-md-2">
        <a href="<?php echo wp_get_referer(); ?>" class="bh_button"><span class="entypo-left" aria-hidden="true"></span> Back</a>
    </div>
</div><!-- /.row -->
<div class="bh_row" itemscope itemtype="http://schema.org/LocalBusiness">
    <div class="bh_col-md-2 bh_business-avatar">
    <?php if(false): ?>
        <img src="http://placehold.it/400x320"/>
    <?php endif; ?>
        <div class="bh_list-group">
            <a href="<?php echo $business->feedback_url ?>" class="bh_list-group-item" target="_blank"><span class="entypo-comment"></span> Customer Feedback</a>
            <?php if($business->events_url): ?>
            <a href="<?php echo $business->events_url ?>" class="bh_list-group-item" target="_blank"><span class="entypo-calendar"></span> Upcoming Events</a>
            <?php endif; ?>
            <?php if($business->promotions_url): ?>
            <a href="<?php echo $business->promotions_url ?>" class="bh_list-group-item" target="_blank"><span class="entypo-tag"></span> Our Promotions</a>
            <?php endif; ?>
        </div>
    </div><!-- /.col-md-2 -->
    <div class="bh_col-md-10 bh_business-details">
        <div class="bh_row">
            <div class="bh_main-content bh_col-md-9">
                <h2 itemprop="name"><?php echo $business->name ?></h2>
                <?php if ( $business->description ) : ?>
                <p itemprop="description"><?php echo wpautop($business->description); ?></p>
                <?php else : ?>
                <p>This business has not yet joined our local community. Give them <a href="<?php echo $business->feedback_url ?>" target="_blank">feedback</a> and let them know you'd like to join!</p>
                <?php endif ?>
            </div><!-- /.col-md-9 -->
            <div class="bh_details bh_col-md-3">
                <div class="bh_section" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
                    <h5>Location</h5>
                    <p itemprop="streetAddress"><?php echo $business->address1 ?><br>
                    <span itemprop="addressLocality"><?php echo $business->locality ?></span>, <span itemprop="addressRegion"><?php echo $business->region ?></span> <span itemprop="postalCode"><?php echo $business->postal_code ?></span><br>
                    <a itemprop="map" href="https://maps.google.com?daddr=<?php echo urlencode($business->address1) ?>+<?php echo urlencode($business->locality) ?>+<?php echo urlencode($business->region) ?>+<?php echo urlencode($business->postal_code) ?>" target="_blank">Get directions &rarr;</a></p>
                </div>
                <?php if($business->hours): ?>
                <div class="bh_section" itemprop="openingHoursSpecification" itemscope itemtype="http://schema.org/OpeningHoursSpecification">
                    <h5>Hours</h5>
                    <dl class="bh_dl-horizontal">
                        <?php foreach($business->hours as $hour): ?>
                        <dt><link itemprop="dayOfWeek" href="http://purl.org/goodrelations/v1#<?php echo $hour->day_name; ?>"><?php echo $hour->day_name; ?>:</dt>
                        <dd><?php if($hour->hours_type == 1): ?><?php foreach($hour->hours as $hour_display): ?><span itemprop="opens" content="<?php echo date('c',strtotime($hour_display[0])); ?>"><?php echo date('g:i a',strtotime($hour_display[0])); ?></span>&ndash;<span itemprop="closes" content="<?php echo date('c',strtotime($hour_display[1])); ?>"><?php echo date('g:i a',strtotime($hour_display[1])); ?></span> <?php endforeach; ?><?php else : ?><?php echo $hour->hours_type_name; ?><?php endif; ?></dd>
                        <?php endforeach; ?>
                    </dl>
                </div>
                <?php endif; ?>
                <?php if($business->telephone): ?>
                <div class="bh_section">
                    <h5>Contact Us</h5>
                    <div class="bh_list-group">
                        <a class="bh_list-group-item">Phone: <span itemprop="telephone"><?php echo $business->telephone; ?></span></a>
                        <?php if($business->website): ?>
                        <a class="bh_list-group-item" itemprop="url" href="<?php echo $business->website; ?>" target="_blank">Visit our Website &rarr;</a>
                        <?php endif; ?>
                        <?php if($business->social_networks): ?>
                        <?php foreach($business->social_networks as $social_network): ?>
                        <a class="bh_list-group-item" itemprop="sameAs" href="<?php echo $social_network->url; ?>"  target="_blank"><?php echo $social_network->cta; ?> &rarr;</a>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div><!-- /.col-md-3 -->
        </div><!-- /.row -->
    </div><!-- /.col-md-10 -->
</div><!-- /.row -->
