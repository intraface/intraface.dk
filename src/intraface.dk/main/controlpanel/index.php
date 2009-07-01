<?php
require '../../include_first.php';

$translation = $kernel->getTranslation('controlpanel');

$modules = $kernel->getModules();

$page = new Intraface_Page($kernel);
$page->start(t('control panel'));
?>
<h1><?php e(t('control panel')); ?></h1>

<ul class="options">
	<li><a href="intranet.php"><?php e(t('intranet')); ?></a></li>
	<li><a href="user.php"><?php e(t('user')); ?></a></li>
	<li><a href="user_preferences.php"><?php e(t('preferences')); ?></a></li>
</ul>

<p class="message"><?php e(t('use these pages to change your settings')); ?></p>

<?php
for ($i = 0, $max = count($modules); $i < $max; $i++) {

	if (!$kernel->intranet->hasModuleAccess(intval($modules[$i]["id"]))) {
		continue;
	}

	if (!$kernel->user->hasModuleAccess(intval($modules[$i]["id"]))) {
		continue;
	}

	$module = $kernel->module($modules[$i]['name']);
	$files = $module->getControlpanelFiles();

	if (count($files) > 0) { ?>

        <div class="controlpanel-item">
		<h2>
        <?php e(t($modules[$i]['name'], $modules[$i]['name'])); ?>
        </h2>
		<ul>
		<?php foreach ($files as $file) { ?>
			<li><a href="<?php e(url($file['url'])); ?>"><?php e(t($file['title'])); ?></a></li>
            <?php
		} ?>
		</ul>
		</div>
        <?php
	}
}
?>

<?php
$page->end();
?>