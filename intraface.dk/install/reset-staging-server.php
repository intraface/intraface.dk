<?php
require_once '../intraface.dk/common.php';
require_once 'Intraface/Install.php';

$install = new Intraface_Install;

if ($install->resetServer()) {
    echo 'staging server reset. Go to <a href="/">login</a>.';
}
else {
    echo 'error';
}


?>