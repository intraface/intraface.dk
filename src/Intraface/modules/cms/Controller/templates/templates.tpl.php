<h1><?php e(__('templates')); ?></h1>

<ul class="options">
    <li><a class="new" href="<?php e(url('create')); ?>"><?php e(__('create template')); ?></a></li>
    <li><a href="<?php e(url('../')); ?>"><?php e(__('close', 'common')); ?></a></li>
</ul>

<?php if (count($templates) == 0): ?>
    <p><?php e(__('No templates found')); ?></p>
<?php else: ?>
<table>
<caption><?php e(__('Templates')); ?></caption>
<thead>
<tr>
    <th><?php e(__('Template name')); ?></th>
    <th><?php e(__('Identifier', 'common')); ?></th>
    <th><?php e(__('For page type')); ?></th>
    <th></th>
</tr>
</thead>
<tbody>
<?php
require_once 'Intraface/modules/cms/Page.php';
$page_types = CMS_Page::getTypesWithBinaryIndex();
?>

<?php foreach ($templates AS $s): ?>
    <tr>
        <td><a href="<?php e(url($s['id'])); ?>"><?php e($s['name']);  ?></a></td>
        <td><?php e($s['identifier']); ?></td>
        <td>
            <?php
            $return = '';
            foreach ($page_types AS $page_key => $page_type){
                if ($page_key & $s['for_page_type']) {
                    if ($return != '') $return .= ', ';
                    $return .= t($page_type);
                }
            }
            e($return);
            ?>

        </td>
        <td class="options">
            <a class="edit" href="<?php e($s['id'] . '/edit'); ?>"><?php e(__('edit settings', 'common')); ?></a>
            <a class="delete" href="<?php e(url(null, array('delete' => $s['id']))); ?>"><?php e(__('delete', 'common')); ?></a>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>