<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="da">
<head>
    <title>Intraface Tools</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <style type="text/css">
body {
    font-family: 'Trebuchet MS', Arial;
}

h1 {
    font-size: 1.4em;
}

h2 {
    font-size: 1.2em;
}

div.search {
    border: 3px solid #999999;
    padding: 5px;
    margin-bottom: 20px;
}

table {
    width: 100%;
}

caption {
    background-color: #6666FF;
}

th {
    border-bottom: 3px solid green;
}

td {
    border-bottom: 1px solid black;
}


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