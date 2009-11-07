<h1><?php e(t('Control panel')); ?></h1>

<ul class="options">
	<li><a href="<?php e(url('intranet')); ?>"><?php e(t('intranet')); ?></a></li>
	<li><a href="<?php e(url('user')); ?>"><?php e(t('user')); ?></a></li>
	<li><a href="<?php e(url('preferences')); ?>"><?php e(t('preferences')); ?></a></li>
</ul>

<p class="message"><?php e(t('use these pages to change your settings')); ?></p>

<?php
foreach ($context->getModules() as $module) {

	if (!$context->getKernel()->intranet->hasModuleAccess(intval($module["id"]))) {
		continue;
	}

	if (!$context->getKernel()->user->hasModuleAccess(intval($module["id"]))) {
		continue;
	}

	$m = $context->getKernel()->useModule($module['name']);
	$files = $m->getControlpanelFiles();

	if (count($files) > 0) { ?>

        <div class="controlpanel-item">
		<h2>
        <?php e(t($module['name'], $module['name'])); ?>
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
