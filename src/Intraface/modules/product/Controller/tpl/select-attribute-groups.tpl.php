<h1><?php e(t('Product attribute groups')); ?></h1>

<ul class="options">
    <li><a class="new" href="<?php e(url(null, array('create'))); ?>"><?php e(t('Create attribute group')); ?></a></li>
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
</ul>

<form action="<?php e(url()); ?>" method="post">
<?php if (!empty($deleted)) : ?>
        <p class="message"><?php e(t('An attribute group has been deleted')); ?>. <input type="hidden" name="deleted" value="<?php echo base64_encode(serialize($deleted)); ?>" /> <input name="undelete" type="submit" value="<?php e(t('Cancel')); ?>" /></p>
<?php endif; ?>
</form>

<?php echo $context->getError()->view(); ?>

<?php if ($groups->count() == 0) : ?>
    <p><?php e(t('No attribute groups has been created.')); ?> <a href="<?php e(url(null, array('create'))); ?>"><?php e(t('Create attribute group')); ?></a>.</p>
<?php else : ?>

    <?php echo $content; ?>

<?php endif; ?>
