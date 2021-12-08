<?php
/*
Plugin Name: Asan Pardakht Api
Plugin URI: https://www.zhaket.com/web/wp-score-gamification
Description: Powerful tool for your business
Author: Yousef Mohammadiani
Version: 1.0.0
Text Domain: avans_plugin
Domain Path: /languages
Author URI: http://mohammadiani.com
*/

use APAPI\Main;

define("ASAAPI_PATH",plugin_dir_path(__FILE__));
define("ASAAPI_URL",plugin_dir_url(__FILE__));

require_once ASAAPI_PATH.'vendor/autoload.php';

new Main;