<?php
require '../../common.php';

// set_include_path('IntrafacePublic/Shop/Frontend/web/'. PATH_SEPERATOR .)

require('startpage.php');

require('IntrafacePublic/Shop/Frontend/web/index.php');

/*
if(isset($_GET['page']) && $_GET['page'] != '') {
	$page = $_GET['page'];
}
else {
	$page = 'index.php';
}

print_r($_GET);

if(strstr($_GET['page'], '/')) {
	trigger_error("You can not use / [slash] in filename", E_USER_ERROR);
	die;
}

$path = 'IntrafacePublic/Shop/Frontend/web/'.$page;

require($path);

/*
if(!@) {
	trigger_error("Invalid file ".$path, E_USER_ERROR);
}
*/ 

require('endpage.php');

?>
