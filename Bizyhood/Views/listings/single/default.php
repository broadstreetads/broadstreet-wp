<!-- Hide the placeholder title of this page -->
<style type="text/css">
.entry-title { display:none; }
</style>

<h2><?php echo $business->name ?></h2>
<div id="biz-column-1">
    <div class="basic-info">
        <?php if($business->address1): ?>
            <?php echo Broadstreet_Utility::buildAddressFromMeta($business); ?>
        <?php endif; ?>
        <br />
        <?php if($business->address1): ?>
            <a target="_blank" href="https://maps.google.com/?q=<?php echo urlencode(Broadstreet_Utility::buildAddressFromMeta($business, true)) ?>">View map</a><br />
        <?php endif; ?>
        <?php if($business->telephone): ?>
            <?php echo $business->telephone ?><br />
        <?php endif; ?>
        <?php if($business->website): ?>
            <a target="_blank"  href="<?php echo $business->website ?>">View website</a>
        <?php endif; ?>
    </div>
    <?php # TODO:Implement hours display ?>
    <?php if(false): ?>
        <div class="section-label"><strong>Hours</strong></div>
        <div class="biz-data">
            <ul id="bs-hours-table">
                <li>
                    <div class="day">Monday</div>
                    <?php if($meta['bs_monday_open'] && $meta['bs_monday_close']): ?>
                    <div class="hours"><?php echo $meta['bs_monday_open'] ?></td><td> - </td><td><?php echo $meta['bs_monday_close'] ?></div>
                    <?php else: ?>
                        <div class="hours">Closed</div>
                    <?php endif; ?>
                </li>
                <li>
                    <div class="day">Tuesday</div>
                    <?php if($meta['bs_tuesday_open'] && $meta['bs_tuesday_close']): ?>
                        <div class="hours"><?php echo $meta['bs_tuesday_open'] ?></td><td> - </td><td><?php echo $meta['bs_tuesday_close'] ?></div>
                    <?php else: ?>
                        <div class="hours">Closed</div>
                    <?php endif; ?>
                </li>
                <li>
                    <div class="day">Wednesday</div>
                    <?php if($meta['bs_wednesday_open'] && $meta['bs_wednesday_close']): ?>
                        <div class="hours"><?php echo $meta['bs_wednesday_open'] ?></td><td> - </td><td><?php echo $meta['bs_wednesday_close'] ?></div>
                    <?php else: ?>
                        <div class="hours">Closed</div>
                    <?php endif; ?>
                </li>
                <li>
                    <div class="day">Thursday</div>
                    <?php if($meta['bs_thursday_open'] && $meta['bs_thursday_close']): ?>
                        <div class="hours"><?php echo $meta['bs_thursday_open'] ?></td><td> - </td><td><?php echo $meta['bs_thursday_close'] ?></div>
                    <?php else: ?>
                        <div class="hours">Closed</div>
                    <?php endif; ?>
                </li>
                <li>
                    <div class="day">Friday</div>
                    <?php if($meta['bs_friday_open'] && $meta['bs_friday_close']): ?>
                        <div class="hours"><?php echo $meta['bs_friday_open'] ?></td><td> - </td><td><?php echo $meta['bs_friday_close'] ?></div>
                    <?php else: ?>
                        <div class="hours">Closed</div>
                    <?php endif; ?>
                </li>
                <li>
                    <div class="day">Saturday</div>
                    <?php if($meta['bs_saturday_open'] && $meta['bs_saturday_close']): ?>
                        <div class="hours"><?php echo $meta['bs_saturday_open'] ?></td><td> - </td><td><?php echo $meta['bs_saturday_close'] ?></div>
                    <?php else: ?>
                        <div class="hours">Closed</div>
                    <?php endif; ?>
                </li>
                <li>
                    <div class="day">Sunday</div>
                    <?php if($meta['bs_sunday_open'] && $meta['bs_sunday_close']): ?>
                        <div class="hours"><?php echo $meta['bs_sunday_open'] ?></td><td> - </td><td><?php echo $meta['bs_sunday_close'] ?></div>
                    <?php else: ?>
                        <div class="hours">Closed</div>
                    <?php endif; ?>
                </li>
                
            </ul>
        </div>
    <?php endif; ?>
</div>

<div class="clearfix"></div>