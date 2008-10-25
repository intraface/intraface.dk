<?php
require('../../include_first.php');

$module = $kernel->module("intranetmaintenance");

$systemmessage = $kernel->useShared('systemmessage');

$intranetnews = new IntranetNews($kernel);
$systemdisturbance = new SystemDisturbance($kernel);

if (isset($_POST['news'])) {
	$systemdisturbance = new SystemDisturbance($kernel, intval($_POST['edit_disturbance']));
	$intranetnews->update($_POST);
}

if (isset($_GET['delete_news'])) {
	$intranetnews = new IntranetNews($kernel, intval($_GET['delete_news']));
	$intranetnews->delete();
}

if (isset($_GET['delete_disturbance'])) {
	$systemdisturbance = new SystemDisturbance($kernel, intval($_GET['delete_disturbance']));
	$systemdisturbance->delete();
}
$page = new Intraface_Page($kernel);
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
  			<td><?php e($d['dk_from_date_time'].' til '.$d['dk_to_date_time']); ?></td>
  			<td><?php e($d['user_name']); ?></td>
				<td><?php e($d['important']); ?></td>
  			<td><?php autohtml($d['description']); ?></td>
				<td><a href="edit_disturbance.php?id=<?php e($d['id']); ?>" class="edit">Ret</a> <a href="messages.php?delete_disturbance=<?php e($d['id']); ?>" class="delete">Slet</a></td>
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
		<?php foreach ($news as $n) { ?>
  		<tr>
  			<td><?php e($n['dk_date_time']); ?></td>
  			<td><?php e($n['user_name']); ?></td>
  			<td><?php e($n['area']); ?></td>
  			<td><?php autohtml($n['description']); ?></td>
				<td><a href="edit_news.php?id=<?php e($n['id']); ?>" class="edit">Ret</a> <a href="messages.php?delete_news=<?php e($n['id']); ?>" class="delete">Slet</a></td>
  		</tr>
		<?php } // end foreach ?>
	</tbody>
</table>





<?php

$page->end();

?>