<?php $pictures = $context->getPicture()->getInstance()->getList('include_hidden'); ?>
<p><img src="<?php e($pictures[4]['file_uri']); ?>" /></p>

<p><a href="<?php e(url('filehandler/selectfile')); ?>"><?php e(t('Select file')); ?></a></p>