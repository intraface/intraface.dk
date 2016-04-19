<?php foreach ($context->getPictures() as $picture) : ?>
    <?php $pictures = $picture->getInstance()->getList('include_hidden'); ?>
    <p><img src="<?php e($pictures[4]['file_uri']); ?>" /></p>
<?php endforeach; ?>

<p><a href="<?php e(url('filehandler/selectfile')); ?>"><?php e(t('Add file')); ?></a></p>