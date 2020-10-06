<?php
/**
 * Plugin Name: Plugin Check
 * Plugin URI: #
 * Description: Plugin to check updates of wordpress core and plugins
 * Version: 1.0
 * Author: Marcin Puszka
 * Author URI: http://www.moskitocode.pl
 */

if (!function_exists('add_action')) 
{
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define('CHECK_VERSION', '1.0');
define('CHECK__MINIMUM_WP_VERSION', '4.0');
define('CHECK__PLUGIN_DIR', plugin_dir_path(__FILE__));

if (is_admin() || (defined('WP_CLI') && WP_CLI)) 
{
    require_once(CHECK__PLUGIN_DIR . 'class/checkAdmin.php');
    $check = new CheckAdmin;
}

if (!function_exists('get_plugins')) 
{
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

require_once(CHECK__PLUGIN_DIR . 'class/Plugin.php');
require_once(CHECK__PLUGIN_DIR . 'class/RestApi.php');

$plugin = new Plugin;

if (!get_option('rest_data'))
{
    update_option('rest_data', $plugin->prepare_api_data());
}

if (!wp_next_scheduled('rest_data_api_hook')) 
{ 
    wp_schedule_event(time(), 'hourly', 'rest_data_api_hook'); 
}

add_action('rest_data_api_hook', 'cron_method');

function cron_method()
{   
    if ('1' === get_option('check_plugin_flag'))
    {   
        $plugin  = new Plugin;
        update_option('rest_data', $plugin->prepare_api_data());
    }
    
}

$rest_data = get_option('rest_data');

$restApi = new RestApi(get_option('check_plugin_api_url'), $rest_data);
$restApi->rest_route_content();
