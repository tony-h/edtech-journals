<?php
/*
 * General functions or utilities
 *	
 * LICENSE: The MIT License (MIT)
 *
 * @author     Tony Hetrick <tony.hetrick@gmail.com>
 * @copyright  [2015] [edtechjournals.org]
 * @license    http://choosealicense.com/licenses/mit/
*/

# Wordpress security recommendation
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * EDJ_Functions contains commonly used static functions
 *
 * @since 0.4.0
 *
 */
 Class EDJ_Functions {

	/*
	 * Prints a message to the screen in HTML format
	*/
	function print_message($message, $type = "") {
	?>
		<p class="<?php echo $type; ?>"> <?php echo $message; ?></p>
	<?php
	}

	/**
	 * Gets a unique id. Added to a function so the inner code
	 * can be changed without modifying every instance.
	 * @return string a unique id
	 */
	function get_unique_id() {

		return uniqid();
	}

	/*
	 * Returns the file name with the file size in a bytes, KB, or MB
	 * @param $file_path path to the file
	 * @return string file name with the file size as text
	 */
	function get_file_size($file_path) {

		$file_name = basename($file_path); 
		$file_size = filesize($file_path); 

		#1048576 = 1024 * 1024 

		if ($file_size >= 1048576 ) 
			$file_size = (int)($file_size / 1048576) . " MB"; 
		elseif ($file_size >= 1024) 
			$file_size = (int)($file_size / 1024) . " KB"; 
		else 
			$file_size = $file_size . " bytes"; 

		return $file_size;
	}

	/*  
	 * Writes a single setting value to a file. 
	 * Used this instead of writing it to the WP database settings table. 
	 * A quicker solution for a single entry
	 * @param $file_name name of file in which to the write the setting to
	 * @param $value value to write to the file
	 */
	function write_to_settings_file($file_name, $value) {

		$value = trim($value);
		$full_path = EDJ_Functions::get_plugin_resource_directory() . $file_name;
		file_put_contents($full_path, $value);	
	}

	/*  
	 * Reads a single setting value from the file. 
	 * @param $file_name name of file in which read the value from
	 * @return string value from the settings file
	 */
	function read_from_settings_file($file_name) {

		$full_path = EDJ_Functions::get_plugin_resource_directory() . $file_name;

		if (file_exists($full_path))
			return file_get_contents($full_path);
			
		return "";
	}

	/*  
	 * Gets the directory of the resource directory in the plugin directory.
	 * Resource directory contains all settings files or files downloaded by the plugin
	 * @return string value to the fully qualified path of the resource directory
	 */
	function get_plugin_resource_directory() {

		$plugins_resource_dir = EDJ_PLUGIN_BASE_DIR . "/resources";
		
		# If directory does not exist, create it with a default index file.
		if (!file_exists($plugins_resource_dir)) {
			mkdir($plugins_resource_dir);
			file_put_contents($plugins_resource_dir . "/index.php", "<?php die(); ?>");
		}
		
		return $plugins_resource_dir . '/';
	}

	/*
	 * Get the debug log file located in the content dir
	 * @param string $text_for_anchor_tag optional text for the anchor tag
	 * @return string URL or the <a> tag of the debug.log file
	 */
	function get_debug_log_file_url($text_for_anchor_tag = "") {

		$url = content_url() . "/debug.log";

		if ($text_for_anchor_tag == "")
			return $url;

$anchor_tag = <<<EOD
<a target="_blank" href="$url">$text_for_anchor_tag</a>
EOD;

		return $anchor_tag;
	}

	/*
	 * Cleans the text of any white spaces and converts to underscores.
	 *  This is primarily for database headers
	 * @param array $array containing the text to clean
	 * @return array text array with the cleaned text
	 */
	function whitespace_to_underscore($array) {
		
		for ($i = 0; $i < count($array); $i++) {

			# Remove all white spaces
			$array[$i] = trim($array[$i]);
			$array[$i] = str_replace(' ', '_', $array[$i]);
		}

		return $array;
	}

	/*
	 * Converts any underscores in the text to whitespace
	 * @param array/string $array An array or string containing the text to change
	 * @return array/string array or string with the whitespaces
	 */
	function underscore_to_whitespace($array) {
		
		# If a string and not an array of strings
		if (!is_array($array)) {
		
			$value = $array;
		
			# Remove any trailing or leading white spaces
			# Change the rest to ' '
			
			$value = trim($value);
			$value = str_replace('_', ' ', $value);

			return $value;
		} 
		
		for ($i = 0; $i < count($array); $i++) {

			# Remove any trailing or leading white spaces
			# Change the rest to ' '
			
			$array[$i] = trim($array[$i]);
			$array[$i] = str_replace('_', ' ', $array[$i]);
		}

		return $array;
	}

	/**
	 * Get the variable equivalent to $_SERVER['REQUEST_URI']
	 * @return string containing the URI which was given in order to access this page; for instance, '/index.html'
	 */
	function get_server_path_request() {
		
		$uri = str_replace( '%7E', '~', $_SERVER['REQUEST_URI']);
		return $uri;
	}

	/**
	 * Builds a list of <option> tags with table names from the database
	 */
	function populate_select_control_from_table_list() {

		$table_list = EDJ_Functions::get_tableList();	
		$option_html = "<option>%s</option>";
		
		# Loop through each table and build a corresponding <option> tag
		foreach($table_list as $table_name) { 
			echo sprintf($option_html, $table_name);		
		}
	}

	/*
	 * Gets the string value from POST
	 * @param string $name name of the variable in POST: $_POST['my_var']
	 * @returns string of the POST data or an empty string
	 */
	function get_post_string($name) {

		$post_string = "";
		
		# If the POST data contains data, use it. Otherwise, return an empty array
		if (isset($_POST[$name]) && $_POST[$name] != "")
			$post_string = $_POST[$name];

		return $post_string;
	}

	/**
	 * Gets the array from POST
	 * @param string $name name of the variable in POST: $_POST['my_var']
	 * @returns array of the POST data or an empty array
	 */
	function get_post_array($name) {

		$post_array = array();
		
		# If the POST data contains data, use it. Otherwise, return an empty array
		if (isset($_POST[$name]) && count($_POST[$name]) > 0)
			$post_array = $_POST[$name];

		return $post_array;
	}

	/**
	 * Trims the whitespace before and after the value
	 * @param array $array to trim the text from
	 * @returns array with the trimmed whitespace
	 */
	 function trim_array_values($array) {
	 
		for ($i = 0; $i < count($array); $i++) {
			$array[$i] = trim($array[$i]);
		}
		
		return $array;
	 }

	// ------------ Database Functions ------------------ //

	/*
	 * Queries the database and gets a list of accessible tables
	 * @return array containing the list of accessible tables
	 */
	function get_tableList() {

		global $wpdb;
		
		$sql = "SHOW TABLES LIKE '" . EDTECH_TABLE_PREFIX . "%'";
		$results = $wpdb->get_results($sql);

		$table_array = array();
		
		# Loop through each table looking for the tables with our prefix
		foreach($results as $index => $value) {
			foreach($value as $tableName) { 
				$table_array[] = $tableName;
			}
		}
		return $table_array;
	}

	/**
	 * Get table comment
	 * @param string $table_name name of table to get comment for
	 * @return string containing the table comment
	 */
	function get_table_comment($table_name) {

		global $wpdb;
		
		$sql = $wpdb->prepare("SHOW TABLE STATUS LIKE %s", $table_name);
		$results = $wpdb->get_results($sql);

		$title = "";
		
		if (!is_null($results) && count($results) > 0)
			$title = $results[0]->Comment;
			
		return $title;
	}

	/**
	 * Get table column names
	 * @param string $table_name name of table to get columns names from
	 * @return array containing the table column names
	 */
	function get_table_column_names($table_name) {

		global $wpdb;
		
		$sql = "SHOW columns FROM $table_name";
		$results = $wpdb->get_results($sql);
		
		$column_array = array();
		$exclusion_list = array("ID", "created", "updated");
		
		# Get the column names from the DB
		foreach($results as $index => $value) {
			
			$column_name = $value->Field;
			
			# If not in the exclusion list, add it to the column array
			if (!in_array($column_name, $exclusion_list))
				$column_array[] = $column_name;
		}
		return $column_array;
	}

	/*
	 * Drops the table and all of the data from the database
	 * @param string $db_table_name name of the table to drop
	 * @return the error code from $wpdb->query($sql);
	 */
	function drop_table($db_table_name) {

		global $wpdb;
		
		$sql = "DROP TABLE IF EXISTS $db_table_name;";
		return $wpdb->query($sql);
	}

	/*
	 * Creates an empty table
	 * @param string $db_table_name name of the table to create
	 * @param string $table_comment comment header to add to the table
	 * @return the error code from $wpdb->query($sql);
	 */
	function create_empty_table($db_table_name, $table_comment) {

		global $wpdb;
		
		$sql = "CREATE TABLE $db_table_name (
				`ID` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID', 
				`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Created Date', 
				`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Updated Date', 
				PRIMARY KEY (`ID`)
				) 
				ENGINE=InnoDB 
				DEFAULT CHARSET=utf8 
				COMMENT='$table_comment' ;";

		return $wpdb->query($sql);
	}

	// ------------ Common Table/Lightbox Functions ------------------ //

	/*
	 * Gets the text to be displayed in the URL. It uses the table/db header text to
	 * try and determine the appropriate value. 
	 * @param string $header_text optional text to determine the descriptor in the URL
	 */
	function get_url_text($header_text = "") {

		#Required for the strings class
		global $edtech_strings;

		#Special conditions for DOAJ journals. This is the expected
		# value in header to determine if this is the DOAJ column
		$doaj = "DOAJ";
		
		#Using this as the default value.		
		$value = $edtech_strings->journal_site;
		
		# Probe for header text for DOAJ. If found, use this text.
		if (stripos($header_text, $doaj) !== false) {
			$value = $edtech_strings->doaj_entry;
		}
		
		# Return the link text
		return $value;
	}
 }
?>