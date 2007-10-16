<?php
require('../../include_first.php');
require('../ErrorList.php');

$errorlist = new ErrorList;

if(!isset($_GET['code']) || $_GET['code'] != 'intrafaceerrorrssfeedisprotected') {
    
}

$now = date("D, d M Y H:i:s T");

$output = "<?xml version=\"1.0\"?>
    <rss version=\"2.0\">
        <channel>
            <title>Errorlog for intraface.dk</title>
            <link>http://tools.intraface.dk/error/</link>
            <description>This is the error log for intraface.dk</description>
            <language>en-us</language>
            <pubDate>".$now."</pubDate>
            <lastBuildDate>".$now."</lastBuildDate>
            <docs></docs>
            <managingEditor>support@intraface.dk</managingEditor>
            <webMaster>support@intraface.dk</webMaster>";
            

$items = $errorlist->get();

if(is_array($items) && count($items) > 0) {
    foreach ($items as $line) {
        $output .= "
            <item>
                <title>".htmlentities($line['title'])."</title>
                <link>http://tools.intraface.dk/error/</link>
                <description>".htmlentities(strip_tags($line['description'])).".<br />URL: ".htmlentities($line['link'])."</description>
            </item>";
    }
}

$output .= "
        </channel>
    </rss>";

header("Content-Type: application/rss+xml");
echo $output;

?>
