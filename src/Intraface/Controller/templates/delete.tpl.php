<form action="<?php e(url('.', array('delete'))); ?>" method="post">
<div><?php e(t('Are you sure, you want to delete?')); ?></div>
<div><input type="hidden" name="_method" value="delete" /><input type="submit" name="delete" value="<?php e(t('Delete')); ?>" /></div>
</form>