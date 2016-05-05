<?php
/*
 * The EDJ_Shortcode_Options class is storage container for the shortcodes. 
 * It also contains the master shortcode list.
 *	
 * LICENSE:  GNU General Public License (GPL) version 3
 *
 * @author     Tony Hetrick <tony.hetrick@gmail.com>
 * @copyright  [2016] [edtechjournals.org]
 * @license    https://www.gnu.org/licenses/gpl.html
*/

/**
 * This class contains the options for use in the shortcodes. 
 */
 class EDJ_Shortcode_Options {
	
	# Array and option keys. The user sees these values in the config options,
	# so the need to be self-explanatory
	private $_optionsList;
	private $_hideLightboxKey = 'hide-lightbox';
	private $_hideFilterCounterKey = 'hide-filter-counter';
	private $_hideColumnControlsKey = 'hide-column-controls';
	private $_disablePaginationKey = 'disable-pagination';
	
	# Boolean options
	private $_hideLightbox;
	private $_hideFilterCounter;
	private $_hideColumnControls;
	private $_disablePagination;
	
	/**
	 * Instantiates the class with a list of options
	 * @param array $options_array an optional list of options set the values of
	 * 				Default value is false for all options
	 */
	public function __construct($options_array = array()) {
		$this->buildOptionsList();
		$this->setOptionValues($options_array);
	}	
	
	/**
	 * Creates a list of available options
	 */
	private function buildOptionsList() {
		
		# There has to be better way of keeping a single reference to the key and have text...
		# The attempt was to have key=>value pair with human readable Key in 
		$this->_optionsList = array();
		$this->_optionsList[$this->_hideLightboxKey] = $this->_hideLightboxKey; 
		$this->_optionsList[$this->_hideFilterCounterKey] = $this->_hideFilterCounterKey; 
		$this->_optionsList[$this->_hideColumnControlsKey] = $this->_hideColumnControlsKey; 
		$this->_optionsList[$this->_disablePaginationKey] = $this->_disablePaginationKey; 
	}	
	
	/**
	 * Sets a list of options based on the provided options array
	 * @param array $options_array the list of options set the values of
	 */
	private function setOptionValues($options_array) {
		
		if ($options_array == null || count($options_array) < 1)
			return;
		
		foreach ($options_array as $option) {
			
			if ($option == $this->_optionsList[$this->_hideLightboxKey])
				$this->_hideLightbox = true;
			if ($option == $this->_optionsList[$this->_hideFilterCounterKey])
				$this->_hideFilterCounter = true;
			if ($option == $this->_optionsList[$this->_hideColumnControlsKey])
				$this->_hideColumnControls = true;
			if ($option == $this->_optionsList[$this->_disablePaginationKey])
				$this->_disablePagination = true;
		}		
	}
	
	/**
	 * Gets the full list of available options for use
	 * @return array an array of all options
	 */
	public function getAllOptionsArray() {
	
		return $this->_optionsList;	
	}
	
	//---------------------------------------------------------------------------------
	// This section gets or sets the state of any set options
	// I opted for a one function fits all method that gets and sets
	
	/**
	 * Gets or sets the state of hide-lightbox option
	 * @param boolean $value of the state to set
	 * @return boolean a boolean value of the state of value
	 */
	public function hideLightboxState($value = '') {

		if ($value != '')
			$this->_hideLightbox = $value;
	
		return $this->_hideLightbox;	
	}

	/**
	 * Gets or set the state of hide-filter-counter option
	 * @param boolean $value of the state to set
	 * @return boolean a boolean value of the state of value
	 */
	public function hideFilterCounterState($value = '') {

		if ($value != '')
			$this->_hideFilterCounter = $value;
	
		return $this->_hideFilterCounter;	
	}

	/**
	 * Gets or sets the state of hide-column-controls option
	 * @param boolean $value of the state to set
	 * @return boolean a boolean value of the state of value
	 */
	public function hideColumnControlsState($value = '') {

		if ($value != '')
			$this->_hideColumnControls = $value;
	
		return $this->_hideColumnControls;	
	}	

	/**
	 * Gets or sets the state of disable-pagination option
	 * @param boolean $value of the state to set
	 * @return boolean a boolean value of the state of value
	 */
	public function disablePaginationState($value = '') {

		if ($value != '')
			$this->_disablePagination = $value;
	
		return $this->_disablePagination;	
	}	
};

?>