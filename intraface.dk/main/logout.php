<?php
/**
 * Logout
 *
 * @package Intraface
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */
require '../include_first.php';

if ($auth->clearIdentity()) {
    $auth->toLogin('Du er logget ud');
} else {
    trigger_error('could not logout', E_USER_ERROR);
    return false;
}