<?php
require('../../include_first.php');

$module = $kernel->module("intranetmaintenance");

$systemmessage = $kernel->useShared('systemmessage');

$intranetnews = new IntranetNews($kernel);
$systemdisturbance = new SystemDisturbance($kernel);

if(isset($_POST['news'])) {
	$systemdisturbance = new SystemDisturbance($kernel, intval($_POST['edit_disturbance']));
	$intranetnews->update($_POST);
}

if(isset($_GET['delete_news'])) {
	$intranetnews = new IntranetNews($kernel, intval($_GET['delete_news']));
	$intranetnews->delete();
}

if(isset($_GET['delete_disturbance'])) {
	$systemdisturbance = new SystemDisturbance($kernel, intval($_GET['delete_disturbance']));
	$systemdisturbance->delete();
}
$page = new Page($kernel);
$page->start("System beskeder");

?>

<h1>System beskeder</h1>

<h2>Forstyrrelser</h2>

<ul class="options" id="add_disturbance">
	<li><a href="edit_disturbance.php">Tilføj ny</a></li>
</ul>

<?php


$disturbance = $systemdisturbance->getList();

?>

<table class="stripe">
	<thead>
		<tr>
			<th>Tidspunkt</th>
			<th>Af</th>
			<th>Vigtig</th>
			<th>Nyhed</th>
			<th>&nbsp;</th>
		</tr>
	</thead>

	<tbody>
		<?php foreach ($disturbance AS $d) { ?>
  		<tr>
  			<td><?php print($d['dk_from_date_time'].' til '.$d['dk_to_date_time']); ?></td>
  			<td><?php print($d['user_name']); ?></td>
				<td><?php print($d['important']); ?></td>
  			<td><?php print(nl2br($d['description'])); ?></td>
				<td><a href="edit_disturbance.php?id=<?php print($d['id']); ?>" class="edit">Ret</a> <a href="messages.php?delete_disturbance=<?php print($d['id']); ?>" class="delete">Slet</a></td>
  		</tr>
		<?php } // end foreach ?>
	</tbody>
</table>

<h2>Nyheder</h2>

<ul class="options" id="add_news">
	<li><a href="edit_news.php">Tilføj ny</a></li>
</ul>

<?php
$news = $intranetnews->getList();
?>

<table class="stripe">
	<thead>
		<tr>
			<th>Dato</th>
			<th>Udgivet af</th>
			<th>Område</th>
			<th>Nyhed</th>
			<th>&nbsp;</th>
		</tr>
	</thead>

	<tbody>
		<?php foreach ($news AS $n) { ?>
  		<tr>
  			<td><?php print($n['dk_date_time']); ?></td>
  			<td><?php print($n['user_name']); ?></td>
  			<td><?php print($n['area']); ?></td>
  			<td><?php print(nl2br($n['description'])); ?></td>
				<td><a href="edit_news.php?id=<?php print($n['id']); ?>" class="edit">Ret</a> <a href="messages.php?delete_news=<?php print($n['id']); ?>" class="delete">Slet</a></td>
  		</tr>
		<?php } // end foreach ?>
	</tbody>
</table>





<?php

$page->end();

?>