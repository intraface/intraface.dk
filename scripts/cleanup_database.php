<?php
require_once '../src/intraface.dk/common.php';

$mdb2 = $bucket->get('mdb2');

try {
    $opts = new Zend_Console_Getopt(array(
        'key|k=s' => 'key',
        'foreignkey|fk=s' => 'foreign key',
        'table|t=s' => 'table',
        'foreigntable|ft=s' => 'foreign table'
    ));
    $opts->parse();
} catch (Zend_Console_Getopt_Exception $e) {
    echo $e->getUsageMessage();
    exit(1);
}

if (empty($opts->k) OR empty($opts->fk) OR empty($opts->ft) OR empty($opts->t)) {
    echo 'not used correctly' . "\n";
    exit(1);
}


$key = $opts->k;
$t1 = $opts->ft; // foreing key table
$t2 = $opts->t; // master table
$foreign_key = $opts->fk;

$mdb2 = MDB2::singleton('mysql://root:klan1n@localhost/intraface_real_data');

$sql = "SELECT t1.$key
    FROM $t1 AS t1
    LEFT JOIN $t2 AS t2
    ON t1.$foreign_key = t2.$key
    WHERE t2.$key IS NULL";
$result = $mdb2->query($sql);

if (PEAR::isError($result)) {
    exit($result->getUserInfo());
}

while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $mdb2->query("DELETE FROM $t1 WHERE id = $row[id]");
    echo "DELETE FROM $t1 WHERE $key = $row[id]\n";
}