<?php
require('../include_first.php');
require('ErrorList.php');

$errorlist = new ErrorList;

if (!empty($_GET['action']) AND $_GET['action'] == 'deletelog') {
    $errorlist->delete();
}



echo '<html><body>';

echo '<h1>Errorlog</h1>';
echo '<p><strong>When you have corrected errors, you have to delete the log.</strong> <a href="'.htmlspecialchars($_SERVER['PHP_SELF']).'?action=deletelog">Delete now</a></p>';

if (isset($_GET['show']) && $_GET['show'] != '') {
    $items = $errorlist->get($_GET['show']);
}
else {
    $items = $errorlist->get();
}

foreach ($items AS $item) {
    echo '<p><strong>'.$item['title'].'</strong> '.$item['description'].'<br />'.$item['pubDate'].': <em><a href="'.$item['link'].'">'.$item['link'].'</a></em>';   
}

echo '</body></html>';
?>