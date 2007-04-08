<?php
require('../include_first.php');

if (!empty($_GET['action']) AND $_GET['action'] == 'deletelog') {
	unlink(ERROR_LOG);
	touch(ERROR_LOG);
	unlink(ERROR_LOG_UNIQUE);
	touch(ERROR_LOG_UNIQUE);
}

$handle = fopen(ERROR_LOG, "r");
while (!feof($handle)) {
   $buffer = fgets($handle, 4096);
   if (empty($buffer) OR !is_string($buffer)) continue;
   $errors[] = unserialize($buffer);
}
fclose($handle);

$rss_information = array(
	'title' => 'Errorlog for intraface.dk',
	'link' => '',
	'description' => 'This is the error log for intraface.dk. When you have corrected errors in the log, you have to delete the log.',
	'language' => 'da',
	'docs' => ''
);

$rss_items[] = array(
	'title' => 'Delete log when finished',
	'description' => 'When you have corrected errors, you have to delete the log.',
	'pubDate' => date('r'),
	'link' => htmlspecialchars($_SERVER['PHP_SELF']).'?action=deletelog',
	'author' => 'Intraface.dk'
);

if (!empty($errors)) {
	foreach ($errors AS $error) {
	
		$rss_items[] = array(
			'title' => $error['type'] . ': ' . $error['message'],
			'description' => $error['file'] . ' - line ' . $error['line'],
			'pubDate' => $error['date'], // RFC 822
			'link' => PATH_WWW . $error['request'],
			'author' => 'Sikkert Sune :)'
		);
	}
}

echo '<html><body>';

foreach($rss_items AS $item) {
	echo '<p><strong>'.$item['title'].'</strong> '.$item['description'].'<br />'.$item['pubDate'].': <em><a href="'.$item['link'].'">'.$item['link'].'</a></em>';	
}

echo '</body></html>';
?>