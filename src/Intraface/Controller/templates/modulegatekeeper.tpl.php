<h1><?php e(__('Modules')); ?></h1>
<ul>
<?php foreach ($context->getModules() as $module): ?>
<?php if (!$context->getKernel()->user->hasModuleAccess(intval($module["id"]))) continue; ?>
	<li>
			<a href="<?php e(url($module["name"])); ?>"><?php e(t($module["menu_label"])); ?></a>
		</li>
<?php endforeach; ?>
</ul>