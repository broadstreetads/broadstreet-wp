<?php
/*
Plugin Name: Broadstreet
Plugin URI: http://broadstreetads.com
Description: Integrate your Broadstreet ad zones via a widget
Version: 0.0.1
Author: Kenny Katzgrau
Author URI: http://codefury.net
*/

require dirname(__FILE__) . '/Broadstreet/Core.php';

# Start the beast
$engine = new Broadstreet_Core;
$engine->execute();
