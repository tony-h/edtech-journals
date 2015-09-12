<?php
/*
 * Syncs the database with a CSV file pulled from Google Sheets 
 *	
 * LICENSE: The MIT License (MIT)
 *
 * @author     Tony Hetrick <tony.hetrick@gmail.com>
 * @copyright  [2015] [edtechjournals.org]
 * @license    http://choosealicense.com/licenses/mit/
*/

# This init section needs to be redone using the WP settings code

#Initializing variables
$google_sheets_id_settings_file = "google_sheets_id.txt";
$google_sheets_id = read_from_settings_file($google_sheets_id_settings_file);

# Get the value from the form POST operation, which is most upto date value
if (get_post_string('google_sheets_id') != "") {
	$google_sheets_id = trim(get_post_string('google_sheets_id'));
}

?>

<div class="wrap">
	<h2>EdTech Journals Plugin Database Sync</h2>
	<p>This sections allows an admin to rebuild or sync the database from a remote CSV file</p>
	<p>Problems? Check the <?php echo get_debug_log_file_url("debug.log"); ?> file. Note, debug mode must be enabled.
	<hr />
	
	<form name="view_journals" method="post" action="<?php echo get_server_path_request(); ?>">
		<input type="hidden" name="view_journals_submit" value="Y">
		<p><b>Enter the Google Spreadsheet ID below.</b></p>
		<label>https://docs.google.com/spreadsheets/d/<input type="text" name="google_sheets_id" value="<?php echo $google_sheets_id; ?>" />/export?format=csv</label>
		<p class="submit">
			<input type="submit" name="Submit" value="Display Journal Entries" /> 
		</p>
	</form>
</div>

<?php
// --------- End of linear HTML process. Functions are below --------- //

$journals_submit = get_post_string('view_journals_submit');
$sync_db_submit = get_post_string('sync_database_submit');

# If the view journals form has been submitted, process the request
if($journals_submit == 'Y') {
	
	if (count(get_tableList()) <= 0) {
		print_message("No '" . EDTECH_TABLE_PREFIX . "' table exists in the database.
						Please add a database.", "warning");
		return;
	}
	
	// Save the value in a settings file
	write_to_settings_file($google_sheets_id_settings_file, $google_sheets_id);

	// Download the data and get the local file path
	$csv_file_path = get_journal_file_path($google_sheets_id);

	# If file was successfully retrieved, prompt user to continue with sync
	if (isset($csv_file_path) && $csv_file_path != "")
		display_sync_form($csv_file_path);
}

# If the sync database form has been submitted, process the request
if($sync_db_submit == 'Y') {
	
	$table = get_post_string('table');
	$csv_file_name = get_post_string('csv_file_name');
	$db_table_name = $table;
	sync_database($db_table_name, $csv_file_name);
}

/*
 * Displays the Sync DB form once the data has been displayed to the screen
 * @param string $csv_file_name file name of the CSV file to sync the DB with
 */
function display_sync_form($csv_file_name) {
?>

<div id="sync-db" class="wrap">
	<h2>Sync Database</h2>
	<p>If results look correct, please confirm by pressing the button below.</p>
	<form name="sync_database" method="post" action="<?php echo get_server_path_request(); ?>">
		<input type="hidden" name="sync_database_submit" value="Y">
		<input type="hidden" name="csv_file_name" value="<?php echo $csv_file_name; ?>">
		<select name="table">
			<?php echo populate_select_control_from_table_list(); ?>
		</select>
		<p class="submit">
			<input type="submit" name="Submit" value="Sync Database" /> 
		</p>
	</form>
</div>

<?php
}

/*
 * Processes the data in the form and syncs the database
 * @param string $google_sheets_id file path of google sheet to get the CSV file from
 * @return string file name of the CVS file saved from the URL. 
 *				  Relative to the current directory
 */
function get_journal_file_path($google_sheets_id) {

	if ($google_sheets_id == "") {
		print_message("Please set the Google Sheet ID", "error");
		
		return;
	}

	// Construct the URL for the CSV file
	$csv_url = "https://docs.google.com/spreadsheets/d/" . 	
				$google_sheets_id .
				"/export?format=csv";

	$message = "Retrieving file <a href=\"$csv_url\">$csv_url</a>";
	print_message($message);
	
	$downloaded_file_name = "$google_sheets_id.csv";
	$downloaded_file_path = get_plugin_resource_directory() . "/$downloaded_file_name";
	
	# Can comment out this line for testing with local file, once downloaded
	file_put_contents($downloaded_file_path, file_get_contents($csv_url));
	
	# Verify the file was downloaded. If not, display message and return empty string
	if (filesize($downloaded_file_path) <= 1) {
		$message = "File not retrieved. Please verify the URL.";
		print_message($message);
		return "";
	}
	
	# File retrieved, keep processing
	$message = "Retrieved $downloaded_file_name (" . get_file_size($downloaded_file_path) . ")";
	print_message($message, "success");

	$message = "Jump to <a href=\"#sync-db\">Sync Database</a> form";
	print_message($message);
	
	# Read and display the contents of the CSV file
	display_csv_data(get_csv_array($downloaded_file_path));
	
	# Return the downloaded file name to process
	return $downloaded_file_path;
}

/*
 * Gets the array form of the CSV file
 * @param string $table_name name of table to get comment for
 * @return array_map a 2D array of the CSV data  
 */
function get_csv_array($downloaded_file_path) {

	$array = array_map('str_getcsv', file($downloaded_file_path));
	
	return $array;
}

/* 
 * Displays the data to the screen for quick verifications
 * @param array $rows is a 2D array of the CSV data 
 */
function display_csv_data($rows) {

	# Display the results in a table for quick verification
	
	$options = new ShortcodeOptions();
	$options->hideFilterCounterState(true);
	$options->hideColumnControlsState(true);
	
	$caption = "Journal Data";
	array_unshift($rows[0], "#");
	display_table_header($caption, $rows[0], $options);

	# Build the rows
	display_table_row_admin_table($rows);
	
	# Close out the table
	display_table_footer();
}

/*
 * Starts the syncing process of the CSV file the database
 * @param string $db_table_name name of the table to update from the CSV file
 * @param string $csv_file_path file path of the CSV file used to sync the DB with
 */
function sync_database($db_table_name, $csv_file_path) {
	
	$file_name = basename($csv_file_path);
	$message = "Writing data to table <em>$db_table_name</em>";
	print_message($message);
	
	# Get the table comment and other info before we drop it
	$table_comment = get_table_comment($db_table_name);
	
	drop_table($db_table_name);
	create_table($db_table_name, $table_comment, $csv_file_path);
	populate_table($db_table_name, $csv_file_path);
}

/*
 * Creates a table to sync with the CSV file
 * @param string $db_table_name name of the table to update from the CSV file
 * @param string $csv_file_name file name of the CSV file used to sync the DB with
 */
function create_table($db_table_name, $table_comment, $csv_file_name) {
	
	global $wpdb;

	$csv_rows = get_csv_array($csv_file_name);
	
	# Column text is the data from the original source. 
	# The column names in the DB need to be sanitized
	$column_text = $csv_rows[0];
	$column_names = whitespace_to_underscore($column_text);

	$column_sql_statements = ""; 
	
	# Creating a column SQL statement for each column in the CSV file
	for ($i = 0; $i < count($column_names); $i++) {
	
		# Require the value in the first column
		$not_null = "";
		if ($i == 0) {
			$not_null = " NOT NULL ";
		}
		
		$column_sql_statements .= 	"`" . $column_names[$i] . "` text $not_null COMMENT " . 
									"'" . $column_text[$i] . "',\n";
	}
	
	# These fields match Custom Database Tables plugin, so just going with them
	$sql = "CREATE TABLE $db_table_name (
			`ID` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID', 
			$column_sql_statements
			`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Created Date', 
			`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Updated Date', 
			PRIMARY KEY (`ID`)
			) 
			ENGINE=InnoDB 
			DEFAULT CHARSET=utf8 
			COMMENT='$table_comment' ;";	

	$wpdb->query($sql);
}

/*
 * Populates the table with the data from the CSV file
 * @param string $db_table_name name of the table to update from the CSV file
 * @param string $csv_file_name file name of the CSV file used to sync the DB with
 */
function populate_table($db_table_name, $csv_file_name) {

	global $wpdb;

	$rows = get_csv_array($csv_file_name);
	$column_names = whitespace_to_underscore($rows[0]);
	
	$rows_count = count($rows);
	$expected_journal_entries = $rows_count - 1;
	
	$message = "<hr />Populating the database with $expected_journal_entries journals.";
	print_message($message);
	
	$rows_added = 0;
	
	for($i = 1; $i < $rows_count; $i++) {
	
		# KV paired array per each row
		$row_array = array();
		
		# If required data is missing
		$invalid_row = false;
	
		#Loop through each column
		for($j = 0; $j < count($rows[$i]); $j++) {
			if ($rows[$i][0] == "")
				$invalid_row = true;
				
			$row_array[$column_names[$j]] = $rows[$i][$j];
		}
		
		# Try to warn if something goes a miss
		if ($invalid_row) {
			print_message("Warning. Row #$i not added.", "warning");
			continue;
		}
		
		# Add the data to the DB
		$rows_added++;
		$wpdb->insert($db_table_name, $row_array);		
	
		# Progress for large files or slow servers.
		if ($i%50 == 1 && $i > 1)
			print_message("Added " . ($i-1) ." of $expected_journal_entries", "info");		
	}
	
	print_message("Successfully added $rows_added journals", "success");
	
	# Adds a bit of user feedback if some entries were not added
	if ($expected_journal_entries != $rows_added) {
		$rows_not_added = $expected_journal_entries - $rows_added;
		print_message("'$rows_not_added' row(s) were not added. Please double check.", "warning");
	}
}

?>