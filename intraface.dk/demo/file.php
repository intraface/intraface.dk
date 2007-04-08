<?php
require '../include_first.php';

if (session_id() == '') {
	session_start();
}
readfile(PATH_CAPTCHA . md5(session_id()) . '.png');

?>