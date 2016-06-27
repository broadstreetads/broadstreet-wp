<?php
extract($data);

if ($str_start_date < $str_tomorrow_date) {
  // display only the ending date
  ?>
    <span class="hidden" itemprop="startDate" content="<?php echo $c_start_date; ?>"><?php echo $wp_start_date; ?></span>
    <span class="<?php echo $plural; ?>_dates">Until <span itemprop="endDate"  content="<?php echo $c_end_date; ?>"><?php echo $wp_end_date; ?></span></span>
  <?php
} elseif ($str_start_date == $str_end_date) {
  // if it is today
  if ($str_end_date == time()) {
    ?>
      <span class="<?php echo $plural; ?>_dates"><?php echo $single; ?> running today!</span>
      <span class="hidden" itemprop="startDate" content="<?php echo $c_start_date; ?>"><?php echo $wp_start_date; ?></span>
    <?php
  } else {
    ?>
      <span class="hidden" itemprop="startDate" content="<?php echo $c_start_date; ?>"><?php echo $wp_start_date; ?></span>
      <span class="<?php echo $plural; ?>_dates">Valid on <span itemprop="endDate"  content="<?php echo $c_end_date; ?>"><?php echo $wp_end_date; ?></span></span>
    <?php
  }
} else {
  // display both start and ending day
  ?>
    <span class="<?php echo $plural; ?>_dates">Valid from <span itemprop="startDate" content="<?php echo $c_start_date; ?>"><?php echo $wp_start_date; ?></span> to <span itemprop="endDate"  content="<?php echo $c_end_date; ?>"><?php echo $wp_end_date; ?></span></span>
  <?php
}

?>