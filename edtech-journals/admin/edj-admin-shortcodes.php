<?php
/*
 * Displays the shortcodes available for use and allows the user to customize a shortcode 
 *	
 * LICENSE: The MIT License (MIT)
 *
 * @author     Tony Hetrick <tony.hetrick@gmail.com>
 * @copyright  [2015] [edtechjournals.org]
 * @license    http://choosealicense.com/licenses/mit/
*/

# Wordpress security recommendation
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

require_once SHORTCODES_DIR . EDJ_PLUGIN_SLUG . 'shortcodes.php';	

?>

<div class="wrap">
	<h2>EdTech Journals Plugin Shortcodes</h2>
	<p>This plug makes use of custom shortcodes that render a table based on the database.<p>
	<p>These codes provide a template or starting point to help customize the fields to display. Please note: The <i>columns</i> and <i>titles</i> should contain the same number of elements.</p>
<?php 
	if (isset($_GET['shortcode']) && $_GET['shortcode'] != '') {
		customize_shortcode($_GET['shortcode']);
	} else {
		echo display_short_codes(); 
	}
?>
</div>

<?php

// --------- End of linear HTML process. Functions are below --------- //

/**
 * Displays a list of shortcodes available for use
 */
function display_short_codes() {

	$table_list = EDJ_Functions::get_tableList();
	$count = count($table_list);
	
	if ($count <= 0) {
		EDJ_Functions::print_message("No '" . EDTECH_TABLE_PREFIX . "' table exists in the database.
				Please add a database.", 'warning');
		return;
	}
	
	# Loop through each table name and build the shortcode
	foreach($table_list as $table_name) { 
		
		# Build a default shortcode (with everything in it
		$columns_in_table = EDJ_Functions::get_table_column_names($table_name);
		display_shortcode($table_name, $columns_in_table);
?>
		<a href="<?php echo $_SERVER['REQUEST_URI'] . "&shortcode=$table_name"; ?>">Customize <i><?php echo $table_name; ?></i></a>
		<hr />
<?php
	}	
}

/**
 * Displays a shortcode for a specified table
 * @param string $table_name name of the table to build shortcode for
 * @param string $columns a list of columns to display in the shortcode 
 * @param string $options a list of options to display in the shortcode
 */
 function display_shortcode($table_name, $columns, $options = null) {
		
		if ($options == null)
			$options = array();
		
		$column_count = count($columns);

		$column_list = '';
		foreach($columns as $columnName) { 
			$column_list .= $columnName . ', ';
		}
		
		# Remove the trailing comma and space
		$column_list = trim($column_list);
		$column_list = rtrim($column_list, ',');
		
		# Replace the underscores
		$column_display_list = str_replace('_', ' ', $column_list); 
		
		# Convert the array to a comma delimited string
		$shortcode_options = implode(',', $options);
		
		echo "<i>$table_name</i> code</h4>";

		$journal_view_shortcode = JOURNAL_VIEW_SHORTCODE;
$shortcode = <<<EOD
[$journal_view_shortcode table="$table_name" columns="%s" titles="%s" options="%s"]
EOD;
		
		# Assemble the components for the shortcodes
		$display_all_columns = sprintf($shortcode, $column_list, $column_display_list, $shortcode_options);
		EDJ_Functions::print_message($display_all_columns, 'edtech-shortcode');
}


/**
 * Displays a form for building a custom shortcode
 * @param string $table_name name of the table to build shortcode for
 */
 function customize_shortcode($db_table_name) {

	# Get the list of columns
	$column_list = EDJ_Functions::get_table_column_names($db_table_name);
 
	# Get the options object for the available codes
	$shortcodeOptions = new EDJ_Shortcode_Options();
	$option_list = $shortcodeOptions->getAllOptionsArray(); 
 
	build_selection_form($db_table_name, $column_list, $option_list);
	
	if(isset($_POST['build_shortcode']) && $_POST['build_shortcode'] == 'Y') {

		$columns = EDJ_Functions::get_post_array('column');
		$options = EDJ_Functions::get_post_array('option');
	
		# Produce a warning for empty codes
		if (count($columns) == 0) {
			$message = 'Please note: This shortcode contains no data';
			EDJ_Functions::print_message($message, 'warning');
		}
		
		display_shortcode($db_table_name, $columns, $options);
	}
}

/**
 * Builds a form for the selection of shortcode data
 * @param string $table_name name of the table to build shortcode for
 * @param string $column_list list columns to display for selection
 * @param string $option_list list options to display for selection
 */
 function build_selection_form($table_name, $column_list, $option_list) {
 ?>
		<h3>Customizing shortcode for <i><?php echo $table_name; ?></i></h3>
		<form class="shortcodes" name="rebuild_tags" method="post" action="<?php echo EDJ_Functions::get_server_path_request(); ?>">
			<input type="hidden" name="build_shortcode" value="Y">
			
			<div class="checkboxes">
				<b>Columns to display</b><br />
<?php insert_column_checkboxes($column_list); ?>
			</div>
			<div class="options">
				<b>Other Options</b><br />
<?php insert_option_checkboxes($option_list); ?>
			</div>
			<input type="submit" value="Generate shortcode for '<?php echo $table_name; ?>'">
		</form>
<?php
}

/**
 * Prints the checkboxes for each column
 * @param string $column_list list columns to build a checkbox for
 */
 function insert_column_checkboxes($column_list) {

	# loop through each column and build a checkbox
	foreach ($column_list as $column) { 
	
		# If column has been check from the POST, check it so the user doesn't
		# have to check the boxes again for a different set
		# Default: unchecked
		$checked = '';
		if (in_array($column, EDJ_Functions::get_post_array('column')))
			$checked = 'checked';

		build_check_box('column[]', $column, $checked, $column);		
	} 
}

/**
 * Prints the checkboxes for each column
 * @param string $option_list list options to build a checkbox for
 */
function insert_option_checkboxes($option_list) {

	#loop through option and build a checkbox
	foreach ($option_list as $option) {

		# If column has been check from the POST, check it so the user doesn't
		# have to check the boxes again for a different set
		# Default: unchecked
		$checked = '';
		if (in_array($option, EDJ_Functions::get_post_array('option')))
			$checked = 'checked';

		build_check_box('option[]', $option, $checked, $option);
	}
}

/**
 * Prints the an input control: <label><input type="" name="" value="" checked/></label>
 * @param string $name name of the checkbox
 * @param string $value value of the checkbox
 * @param string $checked the text "checked" to set the state
 * @param string $display_text text to display next to the control
 */
 function build_check_box($name, $value, $checked, $display_text) {
?>
		<label><input type="checkbox" name="<?php echo $name; ?>" 
						value="<?php echo $value ?>" 
						<?php echo $checked ?>/>
						<?php echo $display_text ?>
		</label><br />
<?php
}


?>