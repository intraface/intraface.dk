<?php

require('public_html/config.local.php');
require('public_html/common.php');


$db = MDB2::singleton(DB_DSN);
$db2 = MDB2::singleton(DB_DSN);


$db->setFetchMode(MDB2_FETCHMODE_ASSOC);
$result = $db->query("SELECT id, name, description FROM product_detail");
if (PEAR::isError($result)) {
    die($result->getMessage());
}

$count = 0;
while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    
    $sql = 'INSERT INTO product_detail_translation SET id = '.$row['id'].', lang = "da", name = "'.$row['name'].'", description = "'.$row['description'].'"';
    
    print($sql."\n");
    
    /*
    $update = $db2->exec($sql);
    if (PEAR::isError($update)) {
        echo $update->getMessage()."\n";
    } else {
        $count += $update;
    }*/
}

echo $count."\n";

?>