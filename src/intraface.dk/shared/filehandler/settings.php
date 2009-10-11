<?php
require('../../include_first.php');

$translation = $kernel->getTranslation('filehandler');
$shared_filehandler = $kernel->useShared('filehandler');
$shared_filehandler->includeFile('InstanceManager.php');


if (!empty($_GET['delete_instance_type_key'])) {
    $instance_manager = new InstanceManager($kernel, (int)$_GET['delete_instance_type_key']);
    $instance_manager->delete();
}

$filehandler = new Filehandler($kernel);
$instance_manager = new InstanceManager($kernel);



$page = new Intraface_Page($kernel);
$page->start(__('filehandler settings'));

?>
<h1><?php e(__('filehandler settings')); ?></h1>

<?php echo $instance_manager->error->view(); ?>

<?php
// $filehandler->createInstance();
// $instances = $filehandler->instance->getTypes();

$instances = $instance_manager->getList();
if (count($instances) > 0): ?>
    <table class="stripe">
        <caption><?php e(__('instance types')); ?></caption>
        <thead>
            <tr>
                <th><?php e(__('name')); ?></th>
                <th><?php e(__('maximum width')); ?></th>
                <th><?php e(__('maximum height')); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($instances AS $instance): ?>
                <tr>
                    <td><?php e($instance['name']); ?></td>
                    <td><?php e($instance['max_width']); ?></td>
                    <td><?php e($instance['max_height']); ?></td>
                    <td>
                      <?php
                      echo '<a class="edit" href="edit_instance_type.php?type_key='.intval($instance['type_key']).'">'.__('edit', 'common').'</a> ';

                      if ($instance['origin'] == 'overwritten') {
                          echo '<a class="delete" href="settings.php?delete_instance_type_key='.intval($instance['type_key']).'">'.__('reset to standard').'</a>';
                      }
                      elseif ($instance['origin'] == 'custom') {
                          echo '<a class="delete" href="settings.php?delete_instance_type_key='.intval($instance['type_key']).'">'.__('delete', 'common').'</a>';
                      }
                      ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <ul class="options">
        <li><a href="edit_instance_type.php"><?php e(__('Add new instance type')); ?></a><li>
    </ul>
<?php endif; ?>

<?php
$page->end();
?>
