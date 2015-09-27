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

require_once EDJ_SHORTCODES_DIR . EDJ_PLUGIN_SLUG . 'shortcodes.php';	

?>

<div class="wrap">
	<h2>EdTech Journals Plugin Shortcodes</h2>
	<p>This plug makes use of custom shortcodes that render a table based on the database.<p>
	<p>These codes provide a template or starting point to help customize the fields to display. Please note: The <i>columns</i> and <i>titles</i> should contain the same number of elements.</p>
<?php 
	if (isset($_GET['shortcode']) && $_GET['shortcode'] != '') {
		customize_journal_view_shortcode($_GET['shortcode']);
	} else {
		echo display_short_codes(); 
	}
?>
</div>

<?php

// --------- End of linear HTML process. Functions are below --------- //

/**
 * Builds a form for the selection of shortcode data
 *
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
				<table>
					<thead>
						<tr>
							<th align="left">Column</th>
							<th align="left">Filter</th>
						</tr>
					</thead>
<?php insert_column_checkboxes($column_list); ?>
				</table>
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
		display_journal_view_shortcode($table_name, $columns_in_table);
?>
		<a href="<?php echo $_SERVER['REQUEST_URI'] . "&shortcode=$table_name"; ?>">Customize <i><?php echo $table_name; ?></i></a>
		<hr />
<?php
	}	
}

/**
 * Displays the journal view shortcode for a specified table
 *
 * @param string $table_name name of the table to build shortcode for
 * @param string $columns a list of columns to display in the shortcode 
 * @param string $options a list of options to display in the shortcode
 */
 function display_journal_view_shortcode($table_name, $columns, $options = null, $filters = null) {
		
		if ($options == null) {
			$options = array();
		}
		if ($filters == null) {
			$filters = array();
		}

		# Convert the array to a comma delimited string
		$shortcode_options = implode(',', $options);
		$column_filters = implode(',', $filters);
		$column_list = implode(',', $columns);

		# Replace the underscores
		$column_display_list = str_replace('_', ' ', $column_list); 

		echo "<i>$table_name</i> code</h4>";

		# Build journal view shortcode
		$journal_view_shortcode_label = JOURNAL_VIEW_SHORTCODE;

		$journal_view_shortcode = <<<EOD
[$journal_view_shortcode_label table="$table_name" columns="$column_list" 
 titles="$column_display_list" options="$shortcode_options" filters="$column_filters"]
EOD;

		# Display the shortcode
		EDJ_Functions::print_message($journal_view_shortcode, 'edtech-shortcode');
}


/**
 * Displays a form for building a custom shortcode
 *
 * @param string $table_name name of the table to build shortcode for
 */
 function customize_journal_view_shortcode($db_table_name) {

	# Get the list of columns
	$column_list = EDJ_Functions::get_table_column_names($db_table_name);
 
	# Get the options object for the available codes
	$shortcodeOptions = new EDJ_Shortcode_Options();
	$option_list = $shortcodeOptions->getAllOptionsArray(); 
 
	# Display the form to customize the shortcodes
	build_selection_form($db_table_name, $column_list, $option_list);

	# If the user submitted the customize shortcode form, display it.
	if(isset($_POST['build_shortcode']) && $_POST['build_shortcode'] == 'Y') {

		$columns = EDJ_Functions::get_post_array('column');
		$options = EDJ_Functions::get_post_array('option');
		
		$raw_filter_list = EDJ_Functions::get_post_array('filter');
		$filters = array();

		# Filter out the blank values and link the filter to the column
		for($i=0; $i < count($column_list); $i++) {
			
			$filter = $raw_filter_list[$i];

			if ($filter != '') {
				
				# Enter the value in a key value pair
				$kv = $column_list[$i] . '=' . $filter;
				array_push($filters, $kv);
			} 			
		}
	
		# Produce a warning for empty codes
		if (count($columns) == 0) {
			$message = 'Please note: This shortcode contains no data';
			EDJ_Functions::print_message($message, 'warning');
		}
		
		display_journal_view_shortcode($db_table_name, $columns, $options, $filters);
	}
}

/**
 * Prints the checkboxes for each column
 *
 * @param string $column_list list columns to build a checkbox for
 */
 function insert_column_checkboxes($column_list) {

  	$table_row = <<<EOD
<tr>
	<td>%s</td>
	<td>%s</td>
</tr>
EOD;

 	$inputbox = <<<EOD
<input type="text" name="%s" value="%s" data="%s" /><br />
EOD;
 	
	$filter_array = EDJ_Functions::get_post_array('filter');
	
	# loop through each column and build a checkbox and input box
	//foreach ($column_list as $column) {
	for ($i = 0; $i < count($column_list); $i++) {
		
		$column = $column_list[$i];
	
		# If column has been checked from the POST, check it so the user doesn't
		# have to check the boxes again for a different set
		# Default: unchecked
		$checked = '';
		if (in_array($column, EDJ_Functions::get_post_array('column'))) {
			$checked = 'checked';
		}

		# If a filter has been entered from the POST, enter the value so the user doesn't
		# have to re-enter them
		# Default: empty string
		$inputbox_value = '';
		if (isset($filter_array[$i])) {
			$inputbox_value = $filter_array[$i];
		}
		
		# Build the form components and print in a table
		$checkbox = build_checkbox('column[]', $column, $checked, $column);
		$assembled_inputbox = sprintf($inputbox, 'filter[]', $inputbox_value , $column);
		$assembled_table_row = sprintf($table_row, $checkbox, $assembled_inputbox);
		echo $assembled_table_row;		
	} 
}

/**
 * Prints the checkboxes for each column
 *
 * @param string $option_list list options to build a checkbox for
 */
function insert_option_checkboxes($option_list) {

	#loop through option and build a checkbox
	foreach ($option_list as $option) {

		# If column has been check from the POST, check it so the user doesn't
		# have to check the boxes again for a different set
		# Default: unchecked
		$checked = '';
		if (in_array($option, EDJ_Functions::get_post_array('option'))) {
			$checked = 'checked';
		}

		echo build_checkbox('option[]', $option, $checked, $option);
	}
}

/**
 * Returns a string for an input control: <label><input type="" name="" value="" checked/></label>
 *
 * @param string $name name of the checkbox
 * @param string $value value of the checkbox
 * @param string $checked the text "checked" to set the state
 * @param string $display_text text to display next to the control
 * @return string value containing the HTML of a checkbox control
 */
 function build_checkbox($name, $value, $checked, $display_text) {
	 
  	$checkbox = <<<EOD
<label><input type="checkbox" name="$name" 
				value="$value" 
				$checked/>
				$display_text
</label><br />
EOD;

	return $checkbox;
}

?>