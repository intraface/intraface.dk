<?php
/**
 * Values
 *
 * @author Lars Olesen <lars@legestue.net>
 */

class Values {

	private $value;

	/**
	 * get()
	 * @param  string $key
	 * @return string $value
	 */
	public function get($key = '') {
		if(!empty($key)) {
			if(isset($this->value[$key])) {
				return($this->value[$key]);
			}
			else {
				return '';
			}
		}
		return $this->value;
	}

	/**
	 * setString()
	 * @param  string $key
	 * @return string $value
	 */
	public function setString($key, $value) {
		$this->value[$key] = $value;
		return true;
	}

	/**
	 * addArray()
	 * @param  string $key
	 * @return string $value
	 */
	public function addArray($array) {
		if (!is_array($array) OR empty($array)) {
			return false;
		}
		return $this->value = array_merge($array, $this->value);

	}
}

?>