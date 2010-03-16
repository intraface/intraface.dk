<h1><?php e(t('Contact subscriptions')); ?></h1>

<table class="stripe">
    <caption><?php e(t('Email lists')); ?></caption>
    <thead>
    <tr>
        <th><?php e(t('Name')); ?></th>
        <th>&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($lists as $list): ?>
    <tr>
        <td><a href="<?php e(url('lists/' . $list['list']->get('id'))); ?>"><?php e($list['list']->get('title')); ?></a></td>
        <td class="options">
            <a class="delete" href="<?php e(url('lists/' . $list['list']->get('id') . '/subscriber/' . $list['subscriber_id'], array('remove'))); ?>"><?php e(t('Remove')); ?></a>
        </td>

    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
