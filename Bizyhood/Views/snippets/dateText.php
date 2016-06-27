<?php
extract($data);

if (strtotime($str_start_date) < strtotime($str_tomorrow_date)) {
  // display only the ending date
  ?>
  <span class="<?php echo $plural; ?>_dates">Until <?php echo $wp_end_date; ?></span>
  <?php
} elseif ($str_start_date == $str_end_date) {
  // if it is today
  if ($str_end_date == time()) {
    ?>
    <span class="<?php echo $plural; ?>_dates"><?php echo $single; ?> running today!</span>
    <?php 
  } else {
    ?>
    <span class="<?php echo $plural;?>_dates">Valid on <?php echo $wp_end_date; ?></span>
    <?php
  }
} else {
  // display both start and ending day
  ?>
  <span class="<?php echo $plural; ?>_dates">Valid from <?php echo $wp_start_date;?> to <?php echo $wp_end_date; ?></span>
  <?php
}

?>