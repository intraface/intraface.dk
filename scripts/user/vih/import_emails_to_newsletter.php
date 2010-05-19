<?php
require_once 'pdoext/connection.inc.php';

$user = 'intraface';
$pass = 'ED!gt@gED!gt@g';
$host = 'mysql.vih.dk';
$dbname = 'vih';

$pdo = new pdoext_Connection('mysql:host='.$host.';dbname='.$dbname, $user, $pass);