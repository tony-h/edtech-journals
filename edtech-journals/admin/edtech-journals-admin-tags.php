<?php
/*
 * Accessible through the menu under the WP settings. This and handles the 
 * admin functions for the plugin. 
 *	
 * LICENSE: The MIT License (MIT)
 *
 * @author     Tony Hetrick <tony.hetrick@gmail.com>
 * @copyright  [2015] [edtechjournals.org]
 * @license    http://choosealicense.com/licenses/mit/
*/

?>

<div class="wrap">
	<h2>EdTech Journals Plugin Add Tags</h2>
	<p>Tags are used to categorize and organize data. This extracts data from the database and builds a tag to corresponding elements. <b>Note:</b> While my initial thought was to use tags from Posts -> Tags, these will have to be different as those show posts containing that tag.</p>
	<hr />
	<h3>1) Select table to view tag list</h3>
	<ol>
		<?php display_tables(); ?>
	</ol>
	<h3>2) Select columns</h3>
		<?php display_columns(); ?>
	

	<div>
		<hr />
		<h2></h2>
		<form name="rebuild_tags" method="post" action="<?php echo get_server_path_request(); ?>">
			<input type="hidden" name="rebuild_tags_submit" value="Y">
			<p class="submit">
				<!-- <input type="submit" name="Submit" value="Rebuild Tags" /> -->
			</p>
		</form>	
	</div>	
</div>
	
<?php

/*
 * Displays the list of tables in the DB in which to get the tags from
 */
function display_tables() {

	$table_list = get_tableList();	
	$html = "<li><a href=\"" . $_SERVER['REQUEST_URI'] . "&table=%s\">%s</a></li>";
	
	# Loop through each table and build a corresponding <item> tag
	foreach($table_list as $table_name) { 
		echo sprintf($html, $table_name, $table_name);		
	}
}

/*
 * Displays a list of columns in the DB table in which to select
 */
 function display_columns() {

	if (!isset($_GET["table"])) {
		$message = "No table selected";
		print_message($message, "warning");
		return;
	}
	
	$db_table_name = $_GET["table"];

	$columns_in_table = get_table_column_names($db_table_name);
	build_select_columns_form($columns_in_table);
	
	if(isset($_POST['display_tag_data']) && $_POST['display_tag_data'] == 'Y') {
		
		# If no columns selected, display error
		if (isset($_POST['column']) && count($_POST['column']) > 0) {
			display_tag_data($db_table_name, $_POST['column']);
		}
		else {
			$message = "Please select a column";
			print_message($message, "error");
		}
	}
}

/*
 * Builds a form with checkboxes for the selection of column data
 * @param array $column_list name is a DB object containing the columns in the table
 */
 function build_select_columns_form($column_list) {
?>
		<form name="rebuild_tags" method="post" action="<?php echo get_server_path_request(); ?>">
			<input type="hidden" name="display_tag_data" value="Y">
<?php foreach ($column_list as $column) { 
	
	# If column has been check from the POST, check it so the user doesn't
	# have to check the boxex again for a different set
	$checked = "";
	if (is_selected($column))
		$checked = "checked";
?>
			<label><input type="checkbox" name="column[]" 
							value="<?php echo $column ?>" 
							<?php echo $checked ?>>
							<?php echo $column ?>
			</label><br />
<?php } ?>
			<input type="submit" value="View tags in '<?php echo $_GET["table"] ?>'">
		</form>
<?php
}

/*
 * Builds a form with checkboxes for the selection of column data
 * @param string $column_name is the name of column to check against the POST
 * 				 data.
 * @returns true if the column name is found in the POST data, otherwise false
 */
function is_selected($column_name) {

	if (!isset($_POST['column'])) {
		return false;
	}
	
	foreach($_POST['column'] as $selected){
		if ($selected == $column_name)
			return true;
	}
	
	return false;
}

/*
 * Displays the result of a DB query containing the list of items to turn to tages
 * @param string $db_table_name is the name of table to query
 * @param array $column_list name is a DB object containing the columns in the table
 */
function display_tag_data($db_table_name, $column_list) {

	$tag_data = get_column_data($db_table_name, $column_list);

?>
	<h3>3) Verify data</h3>
	<p>Please note: The data has been extracted based on commas and only unique entries are retrieved from database.</p>
<?php
	
	foreach ($column_list as $header) {
	
		echo "<h4>$header</h4>";
		
		$tag_array = explode(",", $tag_data[$header]);
		foreach($tag_array as $tag){ 
			echo "$tag<br />";
		}		
	}
}

/*
 * Queries the DB and returns an array of the column data
 * @param string $db_table_name is the name of table to query
 * @param array $columns_array is the columns to get the data for
 * @return a 2D KV pair array containing the name of the and the data
 */
function get_column_data($db_table_name, $columns_array) {
	
	$columns_for_query = "";
	
	foreach($columns_array as $selected){
		$columns_for_query .= $selected . ",";
	}
	$columns_for_query = rtrim($columns_for_query, ',');
	
	# Query the DB and get the data
	global $wpdb;
	$sql = "SELECT DISTINCT $columns_for_query FROM $db_table_name";
	$results = $wpdb->get_results($sql);

	# KV paired array per each row
	$column_array = array(array());
		
	for ($i = 0; $i < count($columns_array); $i++) {
		$col_name = $columns_array[$i];
		
		//$row_array = array();
		$cummulative_list = "";
		foreach($results as $row) {
		
			$data = $row->$col_name;
			if ($data != "") {
				$cummulative_list .= $data . ",";
			//	array_push($row_array, $data);
			}
		}	
		
		//$column_array[$col_name] = $row_array;
		$column_array[$col_name] = $cummulative_list;
	}
	
	return $column_array;	
}
?>