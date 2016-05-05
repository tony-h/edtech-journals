<?php
/*
 * The EDJ_Admin class which controls the edtech journals admin code
 *	
 * LICENSE:  GNU General Public License (GPL) version 3
 *
 * @author     Tony Hetrick <tony.hetrick@gmail.com>
 * @copyright  [2016] [edtechjournals.org]
 * @license    https://www.gnu.org/licenses/gpl.html
*/

# Wordpress security recommendation
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


// Initialize the plugin
add_action( 'plugins_loaded', create_function( '', '$edj_Admin = new EDJ_Admin;' ) );


/*
 * The main admin class.  
 * Based on the template code from: 
	http://theme.fm/2011/10/how-to-create-tabs-with-the-settings-api-in-wordpress-2590/

	Add any includes in function plugin_options_page()
	
	To create a new tab: 
		1) Create a new key and assign it to the value of the PHP file: 
			private $abc_admin_key = 'edtech-journals-admin-abc'
		2) Duplicate the function that reads: function register_abc_admin()
		3) Rename it and change data
		4) Duplicate the add_action function call in the constructor (this determines the tab order)
		5) Match the function call to the above name
	
 */
class EDJ_Admin {
	
	 // Keys used for the tab data and settings
	private $plugin_label = 'EdTech Journals';
	private $plugin_url_slug = 'edtech-journals-admin';
	private $journals_admin_key = 'edj-admin-journals';
	private $shortcodes_admin_key = 'edj-admin-shortcodes';
	private $tags_admin_key = 'edj-admin-tags';
	private $database_admin_key = 'edj-admin-database';
	private $plugin_settings_tabs = array();
	
	/*
	 * Register the actions on init
	 */
	function __construct() {
		add_action( 'admin_init', array( &$this, 'register_journals_admin' ) );
		add_action( 'admin_init', array( &$this, 'register_shortcodes_admin' ) );
		add_action( 'admin_init', array( &$this, 'register_database_admin' ) );
		add_action( 'admin_menu', array( &$this, 'add_admin_menus' ) );
		
		# Development/experimental, not ready for prime time
		#add_action( 'admin_init', array( &$this, 'register_tags_admin' ) );
	}
	
	/*
	 * Registers the journal settings via the Settings API,
	 * appends the setting to the tabs array of the object.
	 */
	function register_journals_admin() {
		$this->plugin_settings_tabs[$this->journals_admin_key] = 'Journals';
	}
	
	/*
	 * Registers the shortcodes settings via the Settings API,
	 * appends the setting to the tabs array of the object.
	 */
	function register_shortcodes_admin() {
		$this->plugin_settings_tabs[$this->shortcodes_admin_key] = 'Shortcodes';
	}
	
	/*
	 * Registers the tags settings and appends the
	 * key to the plugin settings tabs array.
	 */
	function register_tags_admin() {
		$this->plugin_settings_tabs[$this->tags_admin_key] = 'Tags';
	}
	
	/*
	 * Registers the database settings and appends the
	 * key to the plugin settings tabs array.
	 */
	function register_database_admin() {
		$this->plugin_settings_tabs[$this->database_admin_key] = 'Database Tools';
	}
	
	/*
	 * Called during admin_menu, adds an option page under Settings, rendered
	 * using the plugin_options_page method.
	 */
	function add_admin_menus() {
		add_options_page(
			$this->plugin_label, 
			$this->plugin_label, 
			'manage_options', 
			$this->plugin_url_slug, 
			array( &$this, 'plugin_options_page' ) );
	}
	
	/*
	 * Plugin Options page rendering goes here, checks
	 * for active tab and replaces key with the related
	 * settings key. Uses the plugin_options_tabs method
	 * to render the tabs.
	 */
	function plugin_options_page() {

	$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->journals_admin_key;
		?> 
		<div class="wrap">
			<?php $this->plugin_options_tabs(); ?>
			<form method="post" action="options.php">
				<?php wp_nonce_field( 'update-options' ); ?>
				<?php settings_fields( $tab ); ?>
				<?php do_settings_sections( $tab ); ?>
			</form>
		</div>
		<?php
		
		// This loads the code for the active tab
		require_once $tab . '.php';

		#--- This is where any common includes for the admin code goes ---#
		include(EDJ_ADMIN_DIR . '/admin-styles.html');
	}
	
	/*
	 * Renders the tabs in the plugin options page
	 */
	function plugin_options_tabs() {

		$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->journals_admin_key;

		screen_icon();
		echo '<h2 class="nav-tab-wrapper">';
		
		foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
			$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
			echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->plugin_url_slug . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
		}
		
		echo '</h2>';
	}	
};
?>