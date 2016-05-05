/*
 * Handles any plugin specific code with the lightbox 
 *	
 * LICENSE:  GNU General Public License (GPL) version 3
 *
 * @author     Tony Hetrick <tony.hetrick@gmail.com>
 * @copyright  [2016] [edtechjournals.org]
 * @license    https://www.gnu.org/licenses/gpl.html
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