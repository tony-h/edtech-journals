<?php
/*
 * The file contains all of the publicly visible string in the plugin for easy access
 * Please note: This does not include strings for the admin section
 *	
 * LICENSE:  GNU General Public License (GPL) version 3
 *
 * @author     Tony Hetrick <tony.hetrick@gmail.com>
 * @copyright  [2016] [edtechjournals.org]
 * @license    https://www.gnu.org/licenses/gpl.html
*/

# To localize, need to use __(string);
# Requires gettext
# http://codex.wordpress.org/Function_Reference/_2
# http://codex.wordpress.org/I18n_for_WordPress_Developers

###-------Read me-------###
# To use this class, add the following line to the function/file in which to display the string
# Then call the string to display:
# 	global $edtech_strings;
# 	$edtech_strings->variable_name

/**
 * This class contains all of the publicly visible strings. 
 */
class EdTechJournalsStrings {

	public $journal_site = "Journal Site";
	public $doaj_entry = "DOAJ Entry";
	public $show_data_in_pages = "Show data in pages";
	public $show_all_data = "Show all data";
	public $filter_count = "Filter count: ";
}

$edtech_strings = new EdTechJournalsStrings();

?>