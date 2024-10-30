<?php
/*
Plugin Name: Content Magnet
Plugin URI: http://www.revolutionvps.net/cm/
Description: Plugin to Automatically Add Content on a Regular Basis, Based on Your Terms or Google Trends
Version: 2.3.9
Author: Terry Lipperd
Author URI: http://www.revolutionvps.net/
*/

// check that php has version 5 or greater
// NOTE, we need to mention plugin's name or sometimes it can be tricky
// to understand what requires php5
if (version_compare(PHP_VERSION, '5.0.0.', '<'))
{
	die("This plugin only works with php 5 or a greater version.");
}

/*
This plugins needs SimpleXML to be enabled in php 5 (it's enabled by default)
All requests via youtube API & Various Networks are made via curl and SimpleXML php mods
*/

// load classes
require_once("classes/CMMain.php");
require_once("classes/CMDesign.php");
require_once("classes/CMModel.php");
require_once("classes/CMMessages.php");
require_once("classes/CMPoster.php");
require_once("classes/CMKeymanager.php");

$ct_main = new CMMain;
$ct_poster = new CMPoster;
define('CT_LV_LIMIT',0);
define('CT_DEBUG', 0);
define('CT_PATH',dirname(__FILE__));
// hooks
register_activation_hook(__FILE__, array($ct_main, 'on_activation')); // on activation
register_deactivation_hook(__FILE__, array($ct_main, 'on_deactivation')); // on deactivation

add_action('admin_menu', array($ct_main, 'add_admin_menu')); // add pages to wp admin
add_filter('cron_schedules', array('CMModel', 'get_schedules')); // filter for wp cron hack
add_action('wp_footer', array($ct_main, 'powered_by_wpl')); // adds powered by link
add_action('ct_post_hook', array($ct_poster, 'post_video')); // action for wp cron
add_action('ct_blog_posts', array($ct_poster, 'blog_posts')); // blog posts hook

add_action('wp_ajax_add_key','prefix_ajax_add_key');       //Add key Antero+
add_action('wp_ajax_delete_key','prefix_ajax_delete_key'); //Delete key Antero+

register_shutdown_function('CTshutdownFunction');
function CTshutDownFunction() {
    $error = error_get_last();
    if ($error['type'] == 1) {
		if(CT_DEBUG==1){
			$logerror='['.date('Y-m-d h:m:s').']'."\n\n";
			$logerror.=print_r($error,true);
			if(file_exists(CT_PATH.'/ct_error_log.txt')){
			$fp=fopen(CT_PATH.'/ct_error_log.txt','a+');
			}else{
			$fp=fopen(CT_PATH.'/ct_error_log.txt','w+');
			}
			fwrite($fp,print_r($logerror,true));
			fclose($fp);
		}
    }
} 

?>