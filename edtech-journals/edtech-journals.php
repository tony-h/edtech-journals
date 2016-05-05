<?php
/*
 * Plugin Name: EdTech Journals
 * Description: A custom display for the open journals
 * Version: 0.4.0
 * Author: Tony Hetrick (tony.hetrick@gmail.com)
 * Author URI: http://tonyhetrick.com
 * LICENSE:  GNU General Public License (GPL) version 3
 */

/*
	GNU General Public License (GPL) version 3

	Copyright (c) [2016] [edtechjournals.org]

	edtech-journals WordPress plugin is free software: you can redistribute
	it and/or modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation version 3 of the License.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program. If not, see <http://www.gnu.org/licenses/>
*/

# Wordpress security recommendation
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


# -- Constants / Statics / Globals -- #

// Shortcodes
define('JOURNAL_VIEW_SHORTCODE', 'journal-view');

// Database table prefix for the edtech journals
define('EDTECH_TABLE_PREFIX', 'edtech_');

define('EDJ_PLUGIN_SLUG', 'edj-');
define('EDJ_CLASS_PLUGIN_SLUG', 'class-edj-');

// Plugin base dir/url constants
define('EDJ_PLUGIN_BASE_DIR', plugin_dir_path( __FILE__ ));
define('EDJ_PLUGIN_BASE_URL', plugins_url('', __FILE__));

// One stop shop for changing dirs and urls
define('EDJ_ADMIN_DIR', EDJ_PLUGIN_BASE_DIR . 'admin/');
define('EDJ_INCLUDES_DIR', EDJ_PLUGIN_BASE_DIR . 'includes/');
define('EDJ_LIBRARIES_DIR', EDJ_PLUGIN_BASE_DIR . 'libraries/');
define('EDJ_LIGHTBOX_LIB_DIR', EDJ_PLUGIN_BASE_DIR . 'libraries/fancybox/');		
define('EDJ_LIGHTBOX_DIR', EDJ_PLUGIN_BASE_DIR . 'lightbox/');		
define('EDJ_SHORTCODES_DIR', EDJ_PLUGIN_BASE_DIR . 'shortcodes/');
define('EDJ_TABLES_DIR', EDJ_PLUGIN_BASE_DIR . 'tables/');

define('EDJ_ADMIN_URL', EDJ_PLUGIN_BASE_URL . '/admin');
define('EDJ_INCLUDES_URL', EDJ_PLUGIN_BASE_URL . '/includes');
define('EDJ_LIBRARIES_URL', EDJ_PLUGIN_BASE_URL . '/libraries');
define('EDJ_LIGHTBOX_LIB_URL', EDJ_PLUGIN_BASE_URL . '/libraries/fancybox');		
define('EDJ_LIGHTBOX_URL', EDJ_PLUGIN_BASE_URL . '/lightbox');		
define('EDJ_SHORTCODES_URL', EDJ_PLUGIN_BASE_URL . '/shortcodes');
define('EDJ_TABLES_URL', EDJ_PLUGIN_BASE_URL . '/tables');


// Include the working files
require EDJ_PLUGIN_SLUG . 'strings.php';
require_once EDJ_PLUGIN_SLUG . 'functions.php';
require_once EDJ_SHORTCODES_DIR . EDJ_PLUGIN_SLUG . 'shortcodes.php';
require_once EDJ_TABLES_DIR . EDJ_CLASS_PLUGIN_SLUG . 'tables.php';


# -- Admin hooks / handles -- #

// If logged in as admin, enable the admin panel
if ( 'is_admin' ) {

	// Load the admin class
	require_once EDJ_ADMIN_DIR . EDJ_CLASS_PLUGIN_SLUG . 'admin.php';
	
	$filter = 'plugin_action_links_' . plugin_basename(__FILE__); 
	add_filter($filter, 'edj_plugin_settings_link' );
}

/*
 * Handles the requests to add a custom links to the plugin page
 */
function edj_plugin_settings_link($links, $admin_page ='') { 
  $settings_link = '<a href="options-general.php?page=edtech-journals-admin.php">Settings</a>'; 
  array_push($links, $settings_link); 
  return $links; 
}

?>
