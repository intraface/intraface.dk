<h1><?php e(__('Filehandler settings')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../')); ?>"><?php e(__('Go back')); ?></a></li>
</ul>

<form action="<?php e($this->url(null)); ?>" method="post">
    <input type="submit" name="all_files" value="<?php e(__('Delete all instances of all files')); ?>" />
</form>

<?php $instance_manager->error->view(); ?>

<?php
if (!empty($instances) AND count($instances) > 0): ?>
    <table class="stripe">
        <caption><?php e(__('Instance types')); ?></caption>
        <thead>
            <tr>
                <th><?php e(__('Name')); ?></th>
                <th><?php e(__('Maximum width')); ?></th>
                <th><?php e(__('Maximum height')); ?></th>
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
                        <a class="edit" href="<?php e(url('edit', array('type_key' => intval($instance['type_key'])))); ?>"><?php e(__('edit')); ?></a>
                      <?php if($instance['origin'] == 'overwritten') { ?>
                          <a class="delete" href="<?php e(url('./', array('delete_instance_type_key' => intval($instance['type_key'])))); ?>"><?php e(__('reset to standard')); ?></a>
                      <?php } elseif($instance['origin'] == 'custom') { ?>
                          <a class="delete" href="<?php e(url('./', array('delete_instance_type_key' => intval($instance['type_key'])))); ?>"><?php e(__('delete', 'common')); ?></a>
                      <?php }?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<ul class="options">
    <li><a href="<?php e(url('add')); ?>"><?php e(__('Add new instance type')); ?></a></li>
</ul>