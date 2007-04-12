<?php
require('../../include_first.php');

$translation = $kernel->getTranslation('controlpanel');

$page = new Page($kernel);
$page->start(safeToHtml($translation->get('control panel')));
?>
<h1><?php echo safeToHtml($translation->get('control panel')); ?></h1>

<ul class="options">
	<li><a href="intranet.php"><?php echo safeToHtml($translation->get('intranet')); ?></a></li>
	<li><a href="user.php"><?php echo safeToHtml($translation->get('user')); ?></a></li>
	<li><a href="user_preferences.php"><?php echo safeToHtml($translation->get('preferences')); ?></a></li>
</ul>

<p class="message"><?php echo safeToHtml($translation->get('use these pages to change your settings')); ?></p>

<?php

$modules = $kernel->getModules();

for($i = 0, $max = count($modules); $i < $max; $i++) {

	if(!$kernel->intranet->hasModuleAccess(intval($modules[$i]["id"]))) {
		continue;
	}

	if(!$kernel->user->hasModuleAccess(intval($modules[$i]["id"]))) {
		continue;
	}

	$module = $kernel->module($modules[$i]['name']);
	$files = $module->getControlpanelFiles();

	if (count($files) > 0) {
		echo '<div class="controlpanel-item">';
		echo '<h2>' . safeToDb($translation->get($modules[$i]['name'], $modules[$i]['name'])) . '</h2>';
		echo '<ul>';
		foreach($files AS $file) {
			echo '<li><a href="'. PATH_WWW .safeToHtml($file['url']).'">'.safeToDb($translation->get($file['title'])).'</a></li>';
		}
		echo '</ul>';
		echo '</div>';
	}
}
?>

<?php
$page->end();
?>

