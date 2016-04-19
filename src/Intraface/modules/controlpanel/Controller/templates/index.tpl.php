<h1><?php e(t('Control panel')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('user')); ?>"><?php e(t('Your profile')); ?></a></li>
    <li><a href="<?php e(url('user/preferences')); ?>"><?php e(t('Preferences')); ?></a></li>
    <li><a href="<?php e(url('user/changepassword')); ?>"><?php e(t('Password')); ?></a></li>
</ul>

<p class="message"><?php e(t('Use these pages to change your settings for the intranet: ')); ?> <?php e(t($context->getKernel()->intranet->getName())); ?>. <?php e(t('You can change settings for the intranet in the ')); ?> <a href="<?php e(url('../administration')); ?>"><?php e(t('adminitration module')); ?></a>.</p>

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
        <?php foreach ($files as $file) : ?>
            <li><a href="<?php e(url('../../../../' . $file['url'])); ?>"><?php e(t($file['title'])); ?></a></li>
        <?php endforeach; ?>
        </ul>
        </div>
        <?php
    }
}
