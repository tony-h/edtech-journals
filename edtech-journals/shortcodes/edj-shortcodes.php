<?php
/*
 * Handles any shortcodes
 *	
 * LICENSE:  GNU General Public License (GPL) version 3
 *
 * @author     Tony Hetrick <tony.hetrick@gmail.com>
 * @copyright  [2016] [edtechjournals.org]
 * @license    https://www.gnu.org/licenses/gpl.html
*/

#short codes
add_shortcode(JOURNAL_VIEW_SHORTCODE, 'handle_edj_table_shortcode');

/**
 * Handles the 'journal-view' shortcode
 *
 * @param string $atts attributes of the shortcode
 * @param string $content any prexisting content to append to
 */
function handle_edj_table_shortcode($atts, $content=''){

	# Begin output buffering (captures any echo/print statements)
	ob_start();

	# Include the lightbox client and server side code. This needs to be inside
	# of the event handler to be processed after the main WP code is loaded
	require_once EDJ_INCLUDES_DIR . 'header.inc';
	require_once EDJ_LIGHTBOX_DIR . EDJ_CLASS_PLUGIN_SLUG . 'lightbox.php';
	require_once EDJ_SHORTCODES_DIR . EDJ_CLASS_PLUGIN_SLUG . 'shortcode-options.php';	
	
	extract(shortcode_atts(array(
		'table' => '',		// name of the table to query
		'columns' => '', 	// a column name or Comma-separated list of columns name
		'titles' => '', 	// a column title or Comma-separated list of column titles
		'options' => '', 	// an option or comma-separated list of options
		'filters' => '', 	// a kv filter or comma-separated list of kv filter pairs
	), $atts));

	
	# Extract the shortcode data from the shortcode text strings
	# Remove any additional whitespace from the shortcode values
	# This allows for entry as: val1,val2,val3 or val1, val2, val3
	$columns_array = EDJ_Functions::trim_array_values(explode(',', $columns));
	$titles_array = EDJ_Functions::trim_array_values(explode(',', $titles));
	$options_array = EDJ_Functions::trim_array_values(explode(',', $options));
	$filters_array = EDJ_Functions::trim_array_values(explode(',', $filters));
	
	# Converts array to a KV pair
	parse_str(str_replace(',', '&', implode(',', $filters_array)), $filters_array);
	
	# process the code
	process_edj_shortcode($table, $columns_array, $titles_array, $options_array, $filters_array);
	
	# End output buffering and return the captured text 
    $output = ob_get_contents();
    ob_end_clean();
    return $output;	
}

/**
 * Queries the database for journal entries and processes the data
 *
 * @param string $table name of table to query in the db
 * @param array $columns_array array of columns to display
 * @param array $columns_titles array of column titles to display
 * @param array $options_array array of options
 * @param array $$filters_array array of filters for query
 */
function process_edj_shortcode($table_name, $columns_array, $titles_array, $options_array, $filters_array) {

	# Query the DB and get the data
	global $wpdb;
	$sql = build_edj_shortcode_query($table_name, $filters_array);
	$results = $wpdb->get_results($sql);

	# Get the options
	$shortcodeOptions = new EDJ_Shortcode_Options($options_array);
	
	# Array for the lightbox data. Display after the table
	$lightbox_html_array = array();
	$table_headers = EDJ_Functions::get_table_column_names($table_name);
	
	# Build the table
	$caption = EDJ_Functions::get_table_comment($table_name);
	EDJ_Table::display_table_header($caption, $titles_array, $shortcodeOptions);
	
	# Iterate through each row a build a corresponding <tr> tag
	foreach($results as $row) {
		
		if ($shortcodeOptions->hideLightboxState()) {
			
			EDJ_Table::display_table_row($row, $columns_array);
			
		} else {
			
			$content_id = EDJ_Functions::get_unique_id();
			EDJ_Table::display_table_row_for_lightbox($row, $columns_array, $content_id);
			
			$lightbox_html_array[] = 
				EDJ_Lightbox::build_lightbox_with_db_object($row, $table_headers, $content_id);
		}
	}
	
	# close out the table
	EDJ_Table::display_table_footer();

	#Display option to show pagination or not
	EDJ_Table::display_pagination_options($shortcodeOptions);
	
	# now that the table is build, display the hidden lightbox content
	foreach($lightbox_html_array as $lightbox) {
		echo $lightbox;
	}
}

/**
 * Builds the SQL query for the dataset
 * 
 * @param string $table name of table to query in the db
 * @param array $$filters_array array of filters for query
 * @return string value containing the SQL query
 */
function build_edj_shortcode_query($table_name, $filters_array) {

	/*
		Query parmeters:
		SELECT: * 
			While the table might be a limited dataset, the lightbox shows the full dataset
		WHERE: empty or LIKE
			WHERE (Column1 LIKE '%keyword1%' AND Column2 LIKE '%keyword1%')
	*/
	
	$conditions = '';
	
	# Build the WHERE condition: 
	# WHERE (Column1 LIKE '%keyword1%' AND Column2 LIKE '%keyword1%')
	foreach($filters_array as $k => $v) {
		
		$and_string = '';
		if ($conditions != '') {
			$and_string = ' AND';
		}
			
		$conditions .= "$and_string $table_name.$k LIKE '%$v%'"; 
	}
	
	# If conditions exists, add the WHERE clause
	$where_clause = '';
	if ($conditions != '') {
		$where_clause = "WHERE ($conditions)";
	}
	
	$sql = "SELECT * FROM $table_name $where_clause";
	return $sql;
}

?>