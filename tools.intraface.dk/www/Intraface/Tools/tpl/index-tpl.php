<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="da">
<head>
    <title>Intraface Tools</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <style type="text/css">

    </style>
</head>

<body>
<h1>Intraface Developer Tools</h1>

<ul>
    <li><a href="<?php echo url('translation'); ?>">Translationadmin</a> <a href="<?php echo url('translation'); ?>" onClick="window.open('<?php echo url('translation'); ?>', 'translation', 'menubar=yes,width=600,height=600,scrollbars=yes'); return false;">Åbne i lille vindue</a></li>
    <li><a href="<?php echo url('phpinfo'); ?>">PHP info</a></li>
    <li><a href="http://mysql.intraface.dk">Mysql database</a></li>
    <li><a href="<?php echo url('errorlog'); ?>">Unique errors (html)</a> <a href="<?php echo url('errorlog/?show=all'); ?>">All errors (html)</a> <a href="<?php echo url('errorlog/rss'); ?>">Error RSS-feed</a></li>
    <li><a href="http://devel.intraface.dk">Intraface Developer</a></li>
    <li><a href="http://wiki.intraface.dk">Wiki</a></li>
    <li><a href="http://mysql.wiki.intraface.dk">Wiki-database</a></li>
    <li><a href="<?php echo url('log'); ?>">Log</a></li>
</ul>

</body>
</html>