<?php
/**
 * This file is to be included on every page
 *
 * @author Lars Olesen <lars@legestue.net>
 */

if (basename($_SERVER['SCRIPT_NAME']) == basename(__FILE__)) {
	trigger_error('This file cannot be accessed directly', E_USER_ERROR);
}

require('/home/intraface/intraface/common.php');
?>