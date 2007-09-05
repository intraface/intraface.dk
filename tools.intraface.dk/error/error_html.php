<?php
require('../include_first.php');

if (!empty($_GET['action']) AND $_GET['action'] == 'deletelog') {
	
}



$items[] = array(
            'title' => 'Delete log when finished',
            'description' => 'When you have corrected errors, you have to delete the log.',
            'pubDate' => date('r'),
            'link' => htmlspecialchars($_SERVER['PHP_SELF']).'?action=deletelog',
            'author' => 'Intraface.dk'
        );

echo '<html><body>';

foreach($rss_items AS $item) {
	echo '<p><strong>'.$item['title'].'</strong> '.$item['description'].'<br />'.$item['pubDate'].': <em><a href="'.$item['link'].'">'.$item['link'].'</a></em>';	
}

echo '</body></html>';
?>