<?php
/*
Plugin Name: Broadstreet
Plugin URI: http://broadstreetads.com
Description: Integrate Broadstreet business directory and adserving power into your site
Version: 1.50.1
Tested up to: 6.6.1
Author: Broadstreet
Author URI: http://broadstreetads.com
*/

require dirname(__FILE__) . '/Broadstreet/Core.php';

# Start the beast
$engine = new Broadstreet_Core;
$engine->execute();
