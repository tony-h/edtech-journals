<?php
/*
 * Contains database operations that allow the user to perform basic database operations
 *	
 * LICENSE: The MIT License (MIT)
 *
 * @author     Tony Hetrick <tony.hetrick@gmail.com>
 * @copyright  [2015] [edtechjournals.org]
 * @license    http://choosealicense.com/licenses/mit/
*/
	
?>
	<div class="wrap">
		<h2>EdTech Journals Database Tools</h2>
		<p>Here you can perform the basic database operations of creating or deleting tables</p>
		<p>To verify the results after an operation, click the 'Database Tools' and check the drop down menu.</p>
		<hr />
		<div id="delete-table" class="wrap admin-form">
			<h2>Delete an existing table</h2>

<?php	

	# Handles to sequence of deleting a table
	# 1) Shows the list of tables
	# 2) Show confirmation form
	# 3) Processes the request
	if(isset($_POST['confirm_delete_table_submit']) && $_POST['confirm_delete_table_submit'] == 'Y') {
		if(isset($_POST['Submit_Yes']) && $_POST['Submit_Yes'] == 'Yes') {
			$delete_table_name = $_POST["delete-table"];
			delete_table($delete_table_name);
		} else {
			show_delete_table_form();
		}		
	} elseif(isset($_POST['delete_table_submit']) && $_POST['delete_table_submit'] == 'Y') {		
		$delete_table_name = $_POST["delete-table"];
		show_confirm_delete_table_form($delete_table_name);
	} else {
		show_delete_table_form();
	}

?>
			
		</div> <!-- End delete -->	
		<div id="create-table" class="wrap admin-form">
			<h2>Create a new table</h2>
<?php

	# Handles to sequence of creating a table
	# 1) Shows initial form
	# 2) Show confirmation form
	# 3) Processes the request	
	if(isset($_POST['confirm_create_table_submit']) && $_POST['confirm_create_table_submit'] == 'Y') {
		if(isset($_POST['Submit_Yes']) && $_POST['Submit_Yes'] == 'Yes') {
			$create_table_name = $_POST["create-table"];
			create_table($create_table_name);
		} else {
			show_create_table_form();
		}	
	} elseif(isset($_POST['create_table_submit']) && $_POST['create_table_submit'] == 'Y') {
		$prefix = EDTECH_TABLE_PREFIX;
		
		$create_table_name = $prefix . $_POST["create-table"];
		$create_table_name = sanitize_table_name($create_table_name);
		
		if ($create_table_name == $prefix) {
			$message = "'$create_table_name' is not a valid table name";
			print_message($message, "error");
			show_create_table_form();
		} else {
			show_confirm_create_table_form($create_table_name);
		}		
	} else {
		show_create_table_form();
	}

?>		
		</div><!-- End create -->
	</div>

<?php	

// --------- End of linear HTML process. Functions are below --------- //

/**
 * Shows the delete table form
 */
function show_delete_table_form() {
?>
		<p>Select a table and press the delete button.</p>
		<form name="delete_table" method="post" action="<?php echo get_server_path_request(); ?>">
			<input type="hidden" name="delete_table_submit" value="Y">
			<select name="delete-table">
				<?php echo populate_select_control_from_table_list(); ?>
			</select>
			<p class="submit">
				<input type="submit" name="Submit" value="Delete table" /> <br />
				You will be prompted to confirm.
			</p>
			</hr>
		</form>
<?php
}

/**
 * Shows the confirm delete table form
  * @param table_name name of table to delete
 */
function show_confirm_delete_table_form($table_name) {
?>
		<form name="delete_table_confirm" method="post" action="<?php echo get_server_path_request(); ?>">		
			<p>Are you sure you want to permanently delete table <code><?php echo $table_name; ?></code>?
			<input type="hidden" name="confirm_delete_table_submit" value="Y">
			<input type="hidden" name="delete-table" value="<?php echo $table_name; ?>">
			<input type="submit" name="Submit_Yes" value="Yes" /> 
			<input type="submit" name="Submit_No" value="No" /> 
		</form>
<?php
}

/**
 * Deletes the table from the database
 */
function delete_table($table_name) {
?>
	<p>Deleting table <code><?php echo $table_name; ?></code>. . . 
<?php

	$return_value = drop_table($table_name);
	
	if ($return_value != 0) {
		$message = "Deleted table '$table_name'";
		print_message($message, "success");
	} else {
		$message = "The operation failed to delete table '$table_name'. Most likely this is a SQL error.";
		print_message($message, "error");
	}
}

/**
 * Show the create new table form
 */
function show_create_table_form() {	
?>
		<ol>
			<li>Enter the name of the table (no prefix)</li>
			<li>Please only use alpha numeric characters: <code>a-z 1-9</code></li>
			<li>All spaces will automatically be replaced with underscores: <code>' ' -> '_'</code></li>
			<li>All caps will automatically be changed to lower case: <code>'ABC' -> 'abc'</code></li>
		</ol>
		<p></p>
		<form name="create_table" method="post" action="<?php echo get_server_path_request(); ?>">
			<input type="hidden" name="create_table_submit" value="Y">
			<label>Enter name of the table: <code><span class="table-prefix"><?php echo EDTECH_TABLE_PREFIX; ?></span></code><input type="text" name="create-table" /></label>
			<p class="submit">
				<input type="submit" name="Submit" value="Create table" /> <br />
				You will be prompted to confirm.
			</p>
		</form>
<?php
}

/**
 * Shows the confirm form for creating a new table
 */
function show_confirm_create_table_form($table) {

?>
		<form name="delete_table_confirm" method="post" action="<?php echo get_server_path_request(); ?>">		
			<p>Are you sure you want to create table <code><?php echo $table; ?></code>?
			<input type="hidden" name="confirm_create_table_submit" value="Y">
			<input type="hidden" name="create-table" value="<?php echo $table; ?>">
			<input type="submit" name="Submit_Yes" value="Yes" /> 
			<input type="submit" name="Submit_No" value="No" /> 
		</form>
<?php
}

/**
 * Cleans up the table name to add underscores, lower case, etc.
  * @param table_name name of table to sanitize
  * @return string containing the table name to create
 */
function sanitize_table_name($table_name) {
	
	$table_name = trim($table_name);
	$table_name = str_replace(' ', '_', $table_name);	
	$table_name = strtolower($table_name);
	
	return $table_name;
}

/**
 * Creates an empty table in the database
 * @param table_name name of table to create 
 * @param table_comments table comments about the new table 
 */
function create_table($table_name, $table_comment = "") {
?>
	<p>Creating table <code><?php echo $table_name; ?></code>. . . 
<?php
	$return_value = create_empty_table($table_name, $table_comment);

	if ($return_value != '') {
		$message = "Created table '$table_name'";
		print_message($message, "success");
	} else {
		$message = "The operation failed to create table '$table_name'. 
			Please verify only a-z and 1-9 are used or if the table already exists.";
		print_message($message, "error");
		$message = "Operation resulted in error '$return_value'.";
		print_message($message, "error");
	}
}

?>