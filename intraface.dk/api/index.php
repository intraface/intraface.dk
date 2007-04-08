<?php 
require('../include_first.php');
require('Text/Wiki.php');

$wiki = new Text_Wiki();
$wiki->setRenderConf('xhtml', 'wikilink', 'view_url', 'http://devel.intraface.dk/api/');

$text = $wiki->transform('
+ API for intraface.dk

Version: v. 0.5

<!--[[toc]]-->

**The Intraface API makes it possible to integrate other systems with Intraface. Through the API it is easy to retrieve data for other applications, e.g. a webshop, webpage - or it is easy to manipulate data in Intraface.**

The API is based on the XML-RPC-protocol.

The API is still work in progress, so things can still be improved. Let us know if you have any special demands, or if you have any questions for the API.
', 'xhtml');

echo $text;
?>