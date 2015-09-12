/*
 * Handles any plugin specific code with the lightbox 
 *	
 * LICENSE: The MIT License (MIT)
 *
 * @author     Tony Hetrick <tony.hetrick@gmail.com>
 * @copyright  [2015] [edtechjournals.org]
 * @license    http://choosealicense.com/licenses/mit/
*/

$(function() {

	// Set the attributes/events on the lightbox
	setLightBoxOptions();
	
	/**
	 * Sets the lightbox options and behaviour
	 */
	function setLightBoxOptions() {

		// Set the fancybox configurations options
		// http://fancyapps.com/fancybox/#docs
		$("a.lightbox-popup").fancybox({

			autoSize		: true,			
			minWidth		: 350,
			maxWidth		: 800,
			scrolling		: 'visible',
			type			: 'inline',	
		});
	};	

});