<h1><?php e(t('Modules')); ?></h1>
<ul>
<?php foreach ($context->getModules() as $module) : ?>
<?php if (!$context->getKernel()->user->hasModuleAccess(intval($module["id"]))) {
    continue;
} ?>
    <li>
            <a href="<?php e(url($module["name"])); ?>"><?php e(t($module["name"])); ?></a>
        </li>
<?php endforeach; ?>
</ul>