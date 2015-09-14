<?php
/*
 * Builds the tables to display journal information
 *	
 * LICENSE: The MIT License (MIT)
 *
 * @author     Tony Hetrick <tony.hetrick@gmail.com>
 * @copyright  [2015] [edtechjournals.org]
 * @license    http://choosealicense.com/licenses/mit/
*/

# Wordpress security recommendation
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

require_once SHORTCODES_DIR . EDJ_CLASS_PLUGIN_SLUG . 'shortcode-options.php';	

/**
 * EDJ_Functions contains commonly used static functions
 *
 * @since 0.4.0
 *
 */
 Class EDJ_Table {


	/**
	 * Builds the table header
	 * @param string $caption caption of the table
	 * @param array $headers array of column titles to display
	 * @param EDJ_Shortcode_Options $options UI control options
	 */
	function display_table_header($caption, $headers, $options = null) {

		if ($options == null) {
			$options = new EDJ_Shortcode_Options();
		}
		
		# This is only for foo tables, but having the attribute won't hurt
		# data-page=true (show pages)
		# data-page=false (hide pages)
		
		# Default state is true, which enables pagination
		$data_page = 'true';
		
		if (!EDJ_Table::get_pagination_state($options)) {
			$data_page='false';
		}

		# Show filter counter if not set to hide
		if (!$options->hideFilterCounterState()) {
			EDJ_Table::display_filter_counter();
		}
			
		# hides the column control if set
		if (!$options->hideColumnControlsState()) {
			EDJ_Table::display_column_control($headers);
		} 
		
		# disables pagination and shows all table entries
		if ($options->disablePaginationState()) {
			$data_page='false';
		}
		
	?>
		<table class="footable" data-page="<?php echo $data_page; ?>">
			<thead>
				<tr>
	<?php foreach ($headers as $header) { ?>
					<th><?php echo $header ?></th>
	<?php } ?>
				</tr>
			</thead>
			<tbody>
	<?php
	}

	/**
	 * Displays the filter counter HTML
	 */
	function display_filter_counter() {

		global $edtech_strings;

	?>
		<div class="journal-count-container">
			<span><?php echo $edtech_strings->filter_count ?> <span id="journal-count">0</span></span>
		</div>
		<br />
	<?php
	}

	/**
	 * Displays the column control
	  * @param array $headers array of column titles to display
	 */
	function display_column_control($headers) {
	?>
		<div class="column-controls-container">
			<form id="column-controls">
	<?php 
			for ($i = 0; $i < count($headers); $i++) { 
				$separator = " |";
				if ($i+1 == count($headers))
					$separator = '';
	?>
				 <label><input type="checkbox" name="<?php echo $i+1; ?>" checked="checked" /> <?php echo $headers[$i]; ?><span class="separator"><?php echo $separator; ?></span></label> 
	<?php 	
			}
	 ?>	
			</form>
		</div>
	<?php
	}

	/**
	 * Builds a single table row given the column data in a DB object
	 * @param db_object $row a database object containing the row data 
	 * @param array $columns_array array containing the columns names to display the data for
	 * @param string $target_id id of the lightbox
	 * @return string containing the ID for the lightbox URL
	 */
	function display_table_row_for_lightbox($row, $columns_array, $target_id) {
		
# The complete <tr> row
$tr_html_format = <<<EOD

			<tr>
				%s
			</tr>
EOD;

# The complete <td> row
$td_html_format = <<<EOD

				<td>%s</td>
EOD;

#URL for the handling of the lightbox	
$lightbox_url_html_format = <<<EOD
<a class="lightbox-popup" href="#$target_id">%s</a>
EOD;

#URL
$journal_url_html_format = <<<EOD
<a href="%s" target="_blank">%s</a>
EOD;

		$td_html = '';

		for ($i = 0; $i < count($columns_array); $i++) {
		
			$td_data = $row->$columns_array[$i];
			
			# If the first column add link for lightbox.
			# A better solution is to use click event from the <tr>
			if ($i == 0) {
				$td_data = sprintf($lightbox_url_html_format, $td_data);
			}
			
			 #If starts with http, build an <a> tag
			 if (strpos($td_data, 'http') === 0) {

				// EDJ_Functions::get_url_text() function is in edtech-journals-functions.php
				$url_text = EDJ_Functions::get_url_text($columns_array[$i]); 
				$td_data = sprintf($journal_url_html_format, $td_data, $url_text);
			 }		
			
			$td_html .= sprintf($td_html_format, $td_data);
		} 

		echo sprintf($tr_html_format, $td_html);
	}

	/**
	 * Builds a single table row given the column data in a DB object
	 * @param db_object $row a database object containing the row data 
	 * @param array $columns_array array containing the columns names to display the data for
	 */
	function display_table_row($row, $columns_array) {

# The complete <tr> row
$tr_html_format = <<<EOD

		<tr>
			%s
		</tr>
EOD;

# The complete <td> row
$td_html_format = <<<EOD

			<td>%s</td>
EOD;

#URL
$journal_url_html_format = <<<EOD
<a href="%s" target="_blank">%s</a>
EOD;

		$td_html = '';

		for ($i = 0; $i < count($columns_array); $i++) { 
		
			$td_data = $row->$columns_array[$i];
			
			 #If starts with http, build an <a> tag
			 if (strpos($td_data, 'http') === 0) {

				// EDJ_Functions::get_url_text() function is in edtech-journals-functions.php
				$url_text = EDJ_Functions::get_url_text($columns_array[$i]); 
				$td_data = sprintf($journal_url_html_format, $td_data, $url_text);
			 }	
			 
			 # Assemble the HTML
			 $td_html .= sprintf($td_html_format, $td_data);
		} 

		# Dump the assembled HTML to the screen
		echo sprintf($tr_html_format, $td_html);	
	}

	/**
	 * Builds a single table row for the admin page
	 * @param array $rows array containing the data for each row
	 */
	function display_table_row_admin_table($rows) {

# The complete <tr> row
$tr_html_format = <<<EOD

		<tr>
			%s
		</tr>
EOD;

# Template for each <td> instance
$td_html_format = <<<EOD

			<td class="%s">%s</td>
EOD;


		#Loop through each row of the CSV file
		for($i = 1; $i < count($rows); $i++) {

			# The first <td> element is the row index
			$td_html = sprintf($td_html_format, '', $i);

			#Loop through each column
			for($j = 0; $j < count($rows[$i]); $j++) {	
			
				$class = '';

				if ($j == 0)
					$class = "tdleft";
				
				# Each consecutive row contains the actual data
				$td_html .= sprintf($td_html_format, $class, $rows[$i][$j]);
			}
			
			#display full row
			echo sprintf($tr_html_format, $td_html);		
		}
	}

	/**
	 * Display the table footer
	 */
	function display_table_footer() {
	?>
			</tbody>
		</table>
	<?php
	}

	/**
	 * Displays a form for the option of showing the entire table, in pages
	 * (only applicable for footable)
	 */
	function display_pagination_options($options = null) {

		#Required for the strings class
		global $edtech_strings;

		# Determine which box should be checked by default
		$show_data_in_pages_checked = '';
		$show_all_data_checked = '';
		
		if (EDJ_Table::get_pagination_state($options)) {
			$show_data_in_pages_checked = 'checked';
		} else if(!EDJ_Table::get_pagination_state($options)) {
			$show_all_data_checked = 'checked';
		}

	?>
		<div class="instructions">
			<form id="pagination_form" action="<?php echo EDJ_Functions::get_server_path_request(); ?>">
			
	<?php
		# Since the form strips off other GET vars, we need to add them as hidden values here
		foreach ($_GET as $key => $value) {
			if ($key != "pagination") { 
	?>
				<input type='hidden' name='<?php echo $key; ?>' value='<?php echo $value; ?>'/>
	<?php	} 
		}
	?>
			
				<label><input class="selection" type="radio" name="pagination" value="1" <?php echo $show_data_in_pages_checked; ?> /> 
					<?php echo $edtech_strings->show_data_in_pages; ?></label>
				<label><input class="selection" type="radio" name="pagination" value="0" <?php echo $show_all_data_checked; ?>  /> 
					<?php echo $edtech_strings->show_all_data; ?></label>
			</form>
		</div>
		<br />
	<?php
	}


	/**
	 * Gets the state of the pagination variable
	 * (only applicable for footable)
	 * @return boolean value of the pagination state
	 */
	function get_pagination_state($options = null) {

		# Default to true. This is the case if the variable is not set
		$state = true;

		# If options is set, use this value next
		if ($options != null) {
			
			# disables pagination and shows all table entries
			if ($options->disablePaginationState()) {
				$state = false;
			}		
		} 
		
		# If the URL vars are set, this value supersedes the others.
		if (isset($_GET['pagination']) && $_GET['pagination'] == false) {
			$state = false;
		}

		return $state;
	}
 }
?>