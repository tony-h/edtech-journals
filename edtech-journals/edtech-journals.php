<?php
/*
 * Plugin Name: EdTech Journals
 * Description: A custom display for the open journals
 * Version: 0.4.0
 * Author: Tony Hetrick (tony.hetrick@gmail.com)
 * Author URI: http://tonyhetrick.com
 * License: The MIT License (MIT)
 */

/*
	The MIT License (MIT)

	Copyright (c) [2015] [edtechjournals.org]

	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the 'Software'), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in all
	copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
	SOFTWARE.
*/

# Wordpress security recommendation
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


# -- Constants / Statics / Globals -- #

// Shortcodes
define('JOURNAL_VIEW_SHORTCODE', 'journal-view');

// Database table prefix for the edtech journals
define('EDTECH_TABLE_PREFIX', 'edtech_');

// Plugin base dir/url constants
define('PLUGIN_BASE_DIR', plugin_dir_path( __FILE__ ));
define('PLUGIN_BASE_URL', plugins_url('', __FILE__));

// One stop shop for changing dirs
define('ADMIN_DIR', PLUGIN_BASE_DIR . '/admin');
define('ADMIN_URL', PLUGIN_BASE_URL . '/admin');
define('INCLUDES_DIR', PLUGIN_BASE_DIR . '/includes');
define('INCLUDES_URL', PLUGIN_BASE_URL . '/includes');
define('LIBRARIES_DIR', PLUGIN_BASE_DIR . '/libraries');
define('LIBRARIES_URL', PLUGIN_BASE_URL . '/libraries');
define('LIGHTBOX_LIB_DIR', PLUGIN_BASE_DIR . '/libraries/fancybox');		
define('LIGHTBOX_LIB_URL', PLUGIN_BASE_URL . '/libraries/fancybox');		
define('LIGHTBOX_DIR', PLUGIN_BASE_DIR . '/lightbox');		
define('LIGHTBOX_URL', PLUGIN_BASE_URL . '/lightbox');		
define('SHORTCODES_DIR', PLUGIN_BASE_DIR . '/shortcodes');
define('SHORTCODES_URL', PLUGIN_BASE_URL . '/shortcodes');
define('TABLES_DIR', PLUGIN_BASE_DIR . '/tables');
define('TABLES_URL', PLUGIN_BASE_URL . '/tables');


// Include the working files
require 'edtech-journals-strings.php';
require_once 'edtech-journals-functions.php';
require_once SHORTCODES_DIR . '/edtech-journals-shortcodes.php';
require_once TABLES_DIR . '/class-edtech-journals-tables.php';


# -- Admin hooks / handles -- #

// If logged in as admin, enable the admin panel
if ( 'is_admin' ) {
	// Load the admin class
	require_once 'admin/class-edtech-journals-admin.php';
}

# -- Plug page custom menu items --#
// Add settings link on plugin page
$plugin = plugin_basename(__FILE__); 
add_filter('plugin_action_links_$plugin', 'plugin_settings_link' );

/*
 * Handles the requests to add a custom links to the plugin page
 */
function plugin_settings_link($links, $admin_page ='') { 
  $settings_link = '<a href="options-general.php?page=edtech-journals-admin.php">Settings</a>'; 
  array_push($links, $settings_link); 
  return $links; 
}

?>