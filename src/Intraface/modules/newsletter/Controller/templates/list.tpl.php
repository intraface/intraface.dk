<?php
$value = $context->getValues();
?>

<h1><?php e(t('List')); ?></h1>

<ul class="options">
    <li><a class="edit" href="<?php e(url(null, array('edit'))); ?>"><?php e(t('Edit')); ?></a></li>
    <li><a href="<?php e(url('letters')); ?>"><?php e(t('Letters')); ?></a></li>
    <?php if ($context->getKernel()->user->hasModuleAccess('contact')): ?>
        <li><a href="<?php e(url('subscribers')); ?>"><?php e(t('Subscribers')); ?></a></li>
    <?php endif; ?>
	<li><a href="<?php e(url('log')); ?>"><?php e(t('Log')); ?></a></li>
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
</ul>

<table>
    <caption><?php e(t('Information about the list')); ?></caption>
    <tr>
        <th><?php e(t('Title')); ?></th>
        <td><?php e($value['title']); ?></td>
    </tr>
      <tr>
        <th><?php e(t('Description')); ?></th>
        <td><?php e($value['description']); ?></td>
    </tr>
    <tr>
        <th><?php e(t('Email sender name')); ?></th>
        <td><?php e($value['sender_name']); ?> <?php echo htmlspecialchars('<' . $value['reply_email'] . '>'); ?></td>
    </tr>
<!--
    <tr>
        <th>Privatlivspolitik</th>
        <td><?php e($value['privacy_policy']); ?></td>
    </tr>
    <tr>
        <th>Frameldingsbesked</th>
        <td><?php autohtml($value['unsubscribe_message']); ?></td>
    </tr>
-->
    <tr>
        <th><?php e(t('Subscription message')); ?></th>
        <td><?php e($value['subscribe_message']); ?></td>
    </tr>
</table>
