<?php
/**
 * Validator
 *
 * @author Lars Olesen <lars@legestue.net>
 * @author Sune Jensen <sj@sunet.dk>
 */

require_once 'Ilib/Validator.php';

class Validator extends Ilib_Validator
{
    public function __construct($error)
    {
        $options = array('connection_internet' => CONNECTION_INTERNET);

        parent::__construct($error, $options);
    }
}
