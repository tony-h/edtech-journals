/*
 * Handles any plugin specific code with the table. While there is some legitimate code here,
 * most of it is UI hacks for Footable. It's quite ugly, but seems to work.
 *	
 * LICENSE:  GNU General Public License (GPL) version 3
 *
 * @author     Tony Hetrick <tony.hetrick@gmail.com>
 * @copyright  [2016] [edtechjournals.org]
 * @license    https://www.gnu.org/licenses/gpl.html
*/


$(function() {

	 // Initializes the column control form.
	initColumnVisibilityForm();
	 
	// Initializes the pagination form
	initPaginationForm();

	// Override any CSS styles in the table
	setOverrideCSS();

	// Fixes/modifies the filter box
	fixFilterBox();

	// FooTables page/tab event listeners
	getFooTablePageEventListeners();
	
	// On FooTable page/button rebuilds, update pages shown and get new event listeners
	$("th.footable-sortable").click(displayFooTablePages);
	$("th.footable-sortable").click(getFooTablePageEventListeners);
	$(":input.footable-filter").change(displayFooTablePages);
	$(":input.footable-filter").change(getFooTablePageEventListeners);
	$(":input.footable-filter").keypress(filterKeyPressProcessJournalCount);
	$(":input.footable-filter").change(filterChangedProcessJournalCount);
	
	// Set the initial viewable pages at the bottom of the table
	displayFooTablePages();

	// Initializes the pagination form
	initPaginationForm();
	
	// Display the number of journals
	updateJournalNumber() ;
	
	// ------------------ Start Functions ------------------------- //
	
	/**
	 * Initializes the column control form and ties into necessary events
	 */
	 function initColumnVisibilityForm() {
		var controls = $("#column-controls input[type=checkbox]");
		controls.click(columnControlsClicked);
	}
	
	/**
	 * On value change, show or hide the column
	 */
	function columnControlsClicked(event) {
	
		if (this.checked) {
			$('th:nth-child(' + this.name + ')').show();
			$('td:nth-child(' + this.name + ')').show();
		} else {
			$('th:nth-child(' + this.name + ')').hide();
			$('td:nth-child(' + this.name + ')').hide();
		}
	}
	
	/**
	 * Initializes the pagination form and ties into necessary events
	 * The pagination form allows the user to disable the pagination and show all entries
	 */
	function initPaginationForm() {

		// Get event for pagination selection
		var controls = $("#pagination_form .selection");
		controls.click(paginationClicked);
		
		// Get the value from the URL variable, if it exists
		var paginationValue = getUrlParameter("pagination");
		
		// Loop through each control to determine if it should be checked, or not
		controls.each(function(idx, e) {
			var e = $(e);			
			if (e.val() == paginationValue)
				e.prop( "checked", true );
		});
	}

	/**
	 * On value changed, submit the form
	 */
	function paginationClicked() {
		$("#pagination_form").submit();
	}
	
	/**
	 * Ties into the FooTable page/tab clickable event handles
	 * This need to be updated anytime the pages/tabs are rebuilt. This happens
	 * when a column is sorted or the table is searched.
	 */
	function getFooTablePageEventListeners() {
		$("div.pagination li.footable-page").click(displayFooTablePages);
		$("div.pagination li.footable-page-arrow").click(displayFooTablePages);		
	}
	
	/**
	 * Hack to fix/modify the filter box
	 */ 
	function fixFilterBox() {

		// Once FooTable puts the filter box in, move it up one position so the other 
		// UI elements line up
		filterBox = $('div.footable-filter-container');
		filterBox.insertBefore(filterBox.prev());	
	
		// This fixes the search box positioning for Firefox. 
		// It is missing a clear statement 
		$("<div>", {
		  "style": "clear:both",
		}).insertBefore("table.footable");
	}	
	
	/**
	 * Sets any custom CSS that is run after the footable table code. This is the final override.
	 */	
	 function setOverrideCSS() {
		$(".footable>tfoot .pagination").css("text-align", "inherit");
		$(".footable .pagination > ul").css("background-color", "inherit");
	}	
	
	/**
	 * Limits the viewable page selectors for FooTables.
	 * IE nor Firefox will display it correctly
	 */
	function displayFooTablePages() {
	
		// Get the pages and actives pages
		var pages = $("div.pagination li.footable-page");
		var activePage = $("div.pagination li.active");
		var totalPages = pages.length;
		
		// Number of pages to display
		var pagesShown = 0;
		
		// Get the initial start and end page
		var startPageNumber = parseInt(activePage.text());
		var lastPageNumber = parseInt(startPageNumber) + pagesShown;
		
		// Adjust the start/end pages to the min/max limits
		if (lastPageNumber > totalPages)
			lastPageNumber = totalPages;

		if (startPageNumber + pagesShown > totalPages)
			startPageNumber = totalPages - pagesShown;
		
		// Hide all pages initially
		pages.hide();
		
			
		// Show only pages within the viewable range
		for (var i = startPageNumber - 1; i < lastPageNumber; i++) {
			$(pages[i]).show();
		}
	}

	/**
	 * Gets a names parameter from the URL
	 * Code is from: https://stackoverflow.com/questions/19491336/get-url-parameter-jquery
	*/
	function getUrlParameter(sParam)
	{
		var sPageURL = window.location.search.substring(1);
		var sURLVariables = sPageURL.split('&');
		for (var i = 0; i < sURLVariables.length; i++) 
		{
			var sParameterName = sURLVariables[i].split('=');
			if (sParameterName[0] == sParam) 
			{
				return sParameterName[1];
			}
		}
	}
	
	// ------------------ Journal count section ------------------------- //
	/* 
		This section handles the filter number above the filter box
		The process sounds simple, but its not. It would be simple if we could get the FooTables object

		Since the events naturally fire at the same time, this code finishes processing while FooTable
		is still working. Our code needs to run after FooTable has been completed 

		Procedure:
		1) Tie into events (keydown and textbox changed)
		2) Start a timer when keydown event is received
		3) At every timer iteration, it takes a snapshot of the table to provide a realtime update
		4) When the textbox loses focus, stop the timer.
		
	*/

	// The ID of the timer for the search box. This indicates that a search is active.
	// this ID is required to stop the particular instance of the timer
	var activeFiltering;

	
	/**
	 * The filter box was changed and the box lost focus. Update the final journal
	 * number and clear the timer
	 */
	 function filterChangedProcessJournalCount() {
	
		updateJournalNumber();
		clearInterval(activeFiltering);
	}

	/**
	 * The filter box fired a key press event. Clear any existing timer events, update the final journal
	 * number, and start a new timer to reflect the real-time journal numbre
	 */
	function filterKeyPressProcessJournalCount() {
	
		// Interval between updates
		var itemInterval = 1000;
		
		// Controls the timer
		setTimeout(function(){

			// Stop any existing timers
			clearInterval(activeFiltering);

			// Perform and immediate result and start the timer
			updateJournalNumber();
			activeFiltering = setInterval(updateJournalNumber, itemInterval);

		}, itemInterval);
	}	

	/**
	 * Calculates the numer of journals viewable in the table
	 */
	 function updateJournalNumber() {
	
		var totalJournals =  $("table.footable > tbody > tr").length;
		var filteredCount = $("table.footable > tbody > tr[class='footable-filtered']").length;
		var displayed = totalJournals-filteredCount;
	
		$("#journal-count").text(displayed);
	}
});