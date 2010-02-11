<h1><?php e(t($page_types_plural[$type])); ?> <?php e(t('on')); ?> "<?php e($cmssite->get('name')); ?>"</h1>

<?php if (count($cmspage->getTemplate()->getList()) == 0): ?>

    <p class="message-dependent">
        <?php e(t('you have to create a template')); ?>
        <?php if ($kernel->user->hasSubAccess('cms', 'edit_templates')): ?>
            <a href="<?php e(url('../template/create')); ?>"><?php e(t('create template')); ?></a>.
        <?php else: ?>
            <strong><?php e(t('you cannot create templates')); ?></strong>
        <?php endif; ?>
    </p>

<?php else: ?>

<ul class="options">
    <?php foreach ($cmspage->getTypes() AS $page_type): ?>
        <li>
            <?php if ($page_type == $type): ?>
                <strong><?php e(t($page_types_plural[$page_type])); ?></strong>
            <?php else: ?>
                <a  href="<?php e(url(null, array('type' => $page_type))); ?>"><?php e(t($page_types_plural[$page_type])); ?></a>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>

<ul class="options">
    <li><a class="new" href="<?php e(url(null, array('create' => '', 'type' => $type)));?>"><?php e(t('create '.$type)); ?></a></li>
    <li><a  href="<?php e(url('../')); ?>"><?php e(t('go to site overview')); ?></a></li>
</ul>

<?php if ($type == 'page'): ?>
    <?php
    if (!is_array($pages) OR count($pages) == 0): ?>
        <p><?php e(t('No pages found')); ?></p>
    <?php else: ?>
    	<?php include 'page/pages.tpl.php'; ?>
    <?php endif; ?>
<?php elseif ($type == 'article'): ?>
    <?php
    if (!is_array($articles) OR count($articles) == 0): ?>
        <p><?php e(t('No articles found')); ?></p>
    <?php else: ?>
    	<?php include 'page/articles.tpl.php'; ?>
    <?php endif; ?>
<?php elseif ($type == 'news'): ?>
    <?php
    if (!is_array($news) OR count($news) == 0): ?>
        <p><?php e(t('No news found')); ?></p>
    <?php else: ?>
    	<?php include 'page/articles.tpl.php'; ?>
    <?php endif; ?>
<?php endif; ?>

<p>
<?php endif; ?>
