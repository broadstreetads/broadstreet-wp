<?php
/*
Plugin Name: Broadstreet
Plugin URI: http://broadstreetads.com
Description: Integrate Broadstreet business directory and adserving power into your site
Version: 1.46.5
Tested up to: 6.1
Author: Broadstreet
Author URI: http://broadstreetads.com
*/

require dirname(__FILE__) . '/Broadstreet/Core.php';

# Start the beast
$engine = new Broadstreet_Core;
$engine->execute();
