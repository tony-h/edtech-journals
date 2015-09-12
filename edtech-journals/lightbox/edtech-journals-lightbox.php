<?php
/*
 * Build the lightbox to display the journal information 
 *	
 * LICENSE: The MIT License (MIT)
 *
 * @author     Tony Hetrick <tony.hetrick@gmail.com>
 * @copyright  [2015] [edtechjournals.org]
 * @license    http://choosealicense.com/licenses/mit/
*/


/**
 * Builds a single lightbox
 * @param db_object $row a database object containing the row data 
 * @param array $table_headers array containing the columns/header names to display 
 * 				the data for
 * @param string $content_id id of the content linked from the <a> tag
 * @return string assembled HTML data ready for display
 */
function build_lightbox_with_db_object($row, $table_headers, $content_id) {

	$journal_name = $row->$table_headers[0];
	
# The entire lightbox HTML. Contains 2 columns
$lightbox_html = <<<EOD

	<div style="display:none">
		<div id="$content_id" class="journal-lightbox">
			<h3>$journal_name</h3>
			<hr />
				%s
				%s
		</div>
	</div>
EOD;

# A lightbox column: Takes 2 args 1) The class 2) The data
$lightbox_col_html = <<<EOD

			<div class="%s">%s</div>
EOD;

# The formatting of the inner HTML for each item
# The lightbox removes most block elements, so keeping it simple
$inner_html_format = "%s<br />";

#URL formatter
$journal_url_html_format = <<<EOD
<a href="%s" target="_blank">%s</a>
EOD;

	# Build the HTML for each column
	$category_inner_html = build_category_column($table_headers, $inner_html_format);
	$value_inner_html = build_value_column($table_headers, $row, $inner_html_format, $journal_url_html_format);
	
	# Put the HTML into the column container
	$category_column_html = sprintf($lightbox_col_html, "category-column", $category_inner_html);
	$value_column_html = sprintf($lightbox_col_html, "value-column", $value_inner_html);
	
	# Assembly the full HTML and return
	return sprintf($lightbox_html, $category_column_html, $value_column_html);
}

/**
 * Builds the category HTML column
 * @param array $table_headers array containing the columns/header names to display 
 * 				the data for
 * @param string $inner_html_format format of the inner HTML
 * @return string assembled HTML data containing the category data
 */
 function build_category_column($table_headers, $inner_html_format) {

	# The inner for the category column
	$category_html = "";
	
	for ($i = 1; $i < count($table_headers); $i++) {
		 
		# Put the whitespace back in for readable display
		$category = underscore_to_whitespace($table_headers[$i]) . ":";
		 
		# Put the data into the inner HTML
		$category_html .= sprintf($inner_html_format, $category);
	} 

	# Return the inner html for the category column
	return $category_html;
}

/**
 * Builds the value HTML column
 * @param array $table_headers array containing the columns/header names to display 
 * 				the data for
 * @param db_object $row a database object containing the row data 
 * @param string $inner_html_format format of the inner HTML
 * @param string $journal_url_html format of the <a> tag
 * @return string assembled HTML data containing the journal value data
 */
function build_value_column($table_headers, $row, $inner_html_format, $journal_url_html) {

	# The inner html for the values of the journal data
	$value_html = "";
	
	for ($i = 1; $i < count($table_headers); $i++) {
		 
		 # The journal metadata/value
		 $value = $row->$table_headers[$i];
		 
		 #If starts with http, build an <a> tag
		 if (strpos($value, 'http') === 0) {
		 
			// get_url_text() function is in edtech-journals-functions.php
			$url_text = get_url_text($table_headers[$i]); 
			$value = sprintf($journal_url_html, $value, $url_text);
		 }
		 
		# Put the data into the inner HTML
		$value_html .= sprintf($inner_html_format, $value);
	} 

	return $value_html;
}


?>