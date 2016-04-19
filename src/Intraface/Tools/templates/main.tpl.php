<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="da">
<head>
    <title>Intraface Tools</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <style type="text/css">
body {
    font-family: 'Trebuchet MS', Arial;
    border-top: 4px solid green;
    padding: 1em;
    
}

h1 {
    font-size: 1.4em;
}

p.message {
    font-size: 0.8em;
    background-color: #FF3366;
    padding: 0.2em 0.2em 0.2em 0.2em;
    margin: 0.2em 0 0.2em 0;
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


div.formrow {
    clear: both;
    display: block;
    background-color: #DDDDDD;
    padding: 0.2em 0.2em 0.2em 0.2em;
    margin-bottom: 0.7em;
}

div.formrow label {
    display: block;
    vertical-align: top;
    width: 8em;
    float: left;
}

div.formrow textarea {
    width: 80%;
}

div.message {
    font-size: 0.8em;
    background-color: #FF3366;
    padding: 0.2em 0.2em 0.2em 0.2em;
    margin: 0.2em 0 0.2em 0;
}

div.exists {
    font-size: 0.8em;
    background-color: #FFAA33;
    padding: 0.2em 0.2em 0.2em 0.2em;
    margin: 0.2em 0 0.2em 0;
}

div.exists p {
    margin: 0 0 0 0;
}

div.success {
    font-size: 0.8em;
    background-color: #33FF33;
    padding: 0.2em 0.2em 0.2em 0.2em;
    margin: 0.2em 0 0.2em 0;
}

div.success p {
    margin: 0 0 0 0;
}

ul#navigation {
    margin: 0px 0px 20px 0px;
    padding: 0px 0px 10px 0px; 
    border-bottom: 1px solid green;
}

ul#navigation li {
    margin: 0px 20px 0px 0px;
    display: inline;
    list-style-type: none;
    
}

div#content {
    clear: both;
    margin-top: 20px;
}

    </style>
</head>

<body>


 <ul id="navigation">
    <?php foreach ($navigation as $url => $name) : ?>
        <li><a href="<?php e($url); ?>"><?php e($name); ?></a></li>
    <?php endforeach; ?>
</ul>


<div id="content">
    <?php echo $content; ?>
</div>
</body>
</html>