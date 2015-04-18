<div class="bh_row bh_business-header">
    <div class="bh_col-md-2">
        <a href="<?php echo wp_get_referer(); ?>" class="bh_button"><span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span> Back</a>
    </div>
</div><!-- /.row -->
<div class="bh_row">
    <div class="bh_col-md-2 bh_business-avatar">
    <?php if(false): ?>
        <img src="http://placehold.it/400x320"/>
        <div class="bh_list-group">
            <a href="feedback.html" class="bh_list-group-item"><span class="glyphicon glyphicon-comment"></span> Customer Feedback</a>
            <a href="#" class="bh_list-group-item"><span class="glyphicon glyphicon-calendar"></span> Upcoming Events</a>
            <a href="#" class="bh_list-group-item"><span class="glyphicon glyphicon-tag"></span> Our Promotions</a>	
        </div>
    <?php endif; ?>
    </div><!-- /.col-md-2 -->
    <div class="bh_col-md-10 bh_business-details">
            <div class="bh_row">
                    <div class="bh_main-content bh_col-md-9">
                        <h2><?php echo $business->name ?></h2>
                        <?php if(false): ?>
                        <ol class="bh_breadcrumb">
                            <li>Services and Supplies</li>
                            <li>Personal Care</li>
                            <li>Spas</li>
                        </ol>
                        <?php endif; ?>
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
                            <?php if(false): ?>
                            <div class="bh_section">
                                    <h5>Hours</h5>
                                    <dl class="bh_dl-horizontal">
                                            <dt>Sun:</dt>
                                            <dd>Closed</dd>
                                            <dt>Mon:</dt>
                                            <dd>Closed</dd>
                                            <dt>Tue:</dt>
                                            <dd>9am - 6pm</dd>
                                            <dt>Wed:</dt>
                                            <dd>9am - 6pm</dd>
                                            <dt>Thu:</dt>
                                            <dd>10am - 9pm</dd>
                                            <dt>Fri:</dt>
                                            <dd>9am - 6pm</dd>
                                            <dt>Sat:</dt>
                                            <dd>8am - 5pm</dd>
                                    </dl>
                            </div>
                            <?php endif; ?>
                            <?php if(false): ?>
                            <div class="bh_section">
                                    <h5>Contact Us</h5>
                                    <div class="bh_list-group">
                                        <a class="bh_list-group-item">Phone: (732) 536-8500</a>
                                        <a class="bh_list-group-item" href="#">Visit our Website &rarr;</a>
                                        <a class="bh_list-group-item" href="#">Like us on Facebook &rarr;</a>
                                        <a class="bh_list-group-item" href="#">Follow us on Twitter &rarr;</a>     
                                    </div>
                            </div>
                            <?php endif; ?>
                    </div><!-- /.col-md-3 -->
            </div><!-- /.row -->
    </div><!-- /.col-md-10 -->
</div><!-- /.row -->
