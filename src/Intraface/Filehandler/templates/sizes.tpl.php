<h1><?php e(t('Filehandler settings')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Go back')); ?></a></li>
</ul>

<form action="<?php e(url(null)); ?>" method="post">
	<input type="hidden" name="_method" value="delete" />
    <input type="submit" name="all_files" value="<?php e(t('Delete all instances of all files')); ?>" />
</form>

<?php $instance_manager->error->view(); ?>

<?php
if (!empty($instances) AND count($instances) > 0): ?>
    <table class="stripe">
        <caption><?php e(t('Instance types')); ?></caption>
        <thead>
            <tr>
                <th><?php e(t('Name')); ?></th>
                <th><?php e(t('Maximum width')); ?></th>
                <th><?php e(t('Maximum height')); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($instances as $instance): ?>
                <tr>
                    <td><?php e($instance['name']); ?></td>
                    <td><?php e($instance['max_width']); ?></td>
                    <td><?php e($instance['max_height']); ?></td>
                    <td>
                        <a class="edit" href="<?php e(url(intval($instance['type_key']), array('edit'))); ?>"><?php e(t('edit')); ?></a>
                      <?php if($instance['origin'] == 'overwritten') { ?>
                          <a class="delete" href="<?php e(url(intval($instance['type_key']), array('delete'))); ?>"><?php e(t('reset to standard')); ?></a>
                      <?php } elseif($instance['origin'] == 'custom') { ?>
                          <a class="delete" href="<?php e(url(intval($instance['type_key']), array('delete'))); ?>"><?php e(t('delete')); ?></a>
                      <?php }?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<ul class="options">
    <li><a href="<?php e(url(null, array('add'))); ?>"><?php e(t('Add new instance type')); ?></a></li>
</ul>