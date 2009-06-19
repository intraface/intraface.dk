<?php

die("SUNE ER FÆRDIG");

function getmicrotime2() {
	$mt = explode( ' ', microtime()); 
	return $mt[1] + $mt[0];
}
$this_time = getmicrotime2();



$runs = 100;
$time = 0;
$url = 'http://devel.intraface.dk/test/stresstest.php';

for ($i = 0; $i < $runs; $i++) {
	
	$ch = curl_init();
	$timeout = 5; // set to zero for no timeout
	curl_setopt ($ch, CURLOPT_URL, $url);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$time += (double)curl_exec($ch);
	curl_close($ch);
	// $file_contents = 
	//  file_get_contents("http://devel.intraface.dk/test/stresstest.php");
}

echo '<br />Had '.$runs.' queries for '.$url.' which took '.$time.' seconds to execute at an average of '.($time/$runs).' second/query'; 

echo '<br />--------------------- CONTENT BEGIN ---------------------------------';

echo '<br />'.nl2br(file_get_contents('stresstest.php'));

echo '<br />--------------------- CONTENT END ---------------------------------';


echo '<br />This page took about ' . round(getmicrotime2()-$this_time,4) . ' seconds to generate.';
?>