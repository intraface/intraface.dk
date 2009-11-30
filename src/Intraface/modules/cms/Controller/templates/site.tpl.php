<h1><?php e(__('site')); ?> <?php e($cmssite->get('name')); ?></h1>

<ul class="options">
    <li><a class="edit" href="<?php e(url('edit')); ?>"><?php e(__('edit site settings')); ?></a></li>
    <?php if ($kernel->user->hasSubAccess('cms', 'edit_templates')): ?>
    <li><a class="template" href="<?php e(url('templates')); ?>"><?php e(__('templates')); ?></a></li>
    <?php endif; ?>
    <?php if ($kernel->user->hasSubAccess('cms', 'edit_stylesheet')): ?>
    <li><a class="stylesheet" href="<?php e(url('stylesheet')); ?>"><?php e(__('stylesheet')); ?></a></li>
    <?php endif; ?>
</ul>

<?php if (count($cmspage->getTemplate()->getList()) == 0): ?>

    <p class="message-dependent">
        <?php e(__('you have to create a template')); ?>
        <?php if ($kernel->user->hasSubAccess('cms', 'edit_templates')): ?>
            <a href="<?php e(url('templates/create')); ?>"><?php e(__('create template')); ?></a>.
        <?php else: ?>
            <strong><?php e(__('please ask your administrator to do that')); ?></strong>
        <?php endif; ?>
    </p>

<?php else: ?>

<div class="message">
    <p><?php e(t('your website can consist of the following types of content:')); ?></p>
    <ul>
        <li><?php e(t('pages are your common structure on the website reflecting the navigation on the website. pages can be hierarchically ordered.')); ?></li>
        <li><?php e(t('articles are categorized content which is often persistent. you can categorized it with the use of keywords')); ?></li>
        <li><?php e(t('news is used to tell about new things relating to the content of your website. news does often have a time-limited relevance.')); ?></li>
    </ul>
</div>

<h2><?php e(t('pages')); ?></h2>

<ul class="options">
    <li><a href="<?php e(url('pages', array('type' => 'page'))); ?>"><?php e(t('go to pages'));  ?></a></li>
    <li><a href="<?php e(url('pages/create', array('type' => 'page'))); ?>"><?php e(t('create a new page'));  ?></a></li>
</ul>

<h2><?php e(t('articles')); ?></h2>

<ul class="options">
    <li><a href="<?php e(url('pages', array('type' => 'article'))); ?>"><?php e(t('go to articles'));  ?></a></li>
    <li><a href="<?php e(url('pages/create', array('type' => 'article'))); ?>"><?php e(t('create a new article'));  ?></a></li>
</ul>

<h2><?php e(t('news')); ?></h2>

<ul class="options">
    <li><a href="<?php e(url('pages', array('type' => 'news'))); ?>"><?php e(t('go to news'));  ?></a></li>
    <li><a href="<?php e(url('pages/create', array('type' => 'news'))); ?>"><?php e(t('create a news'));  ?></a></li>
</ul>

<?php endif; ?>