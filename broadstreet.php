<?php
/*
Plugin Name: Broadstreet
Plugin URI: http://broadstreetads.com
Description: Integrate your Broadstreet ad zones via a widget
Version: 1.0.0
Author: Broadstreet
Author URI: http://broadstreetads.com
*/

require dirname(__FILE__) . '/Broadstreet/Core.php';

# Start the beast
$engine = new Broadstreet_Core;
$engine->execute();
