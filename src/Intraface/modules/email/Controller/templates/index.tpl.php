<h1><?php e(t('E-mails')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url(null)); ?>"><?php e(t('All')); ?></a></li>
    <li><a href="<?php e(url(null, array('filter' => 'new'))); ?>"><?php e(t('Newest')); ?></a></li>
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
</ul>

<?php if (count($emails) == 0) : ?>

    <p><?php e(t('no e-mails has been sent')); ?></p>

<?php else : ?>
    <?php if ($queue > 0) : ?>
        <p><?php e(t('E-mails are in queue - the will be sent soon')); ?></p>
    <?php endif; ?>

    <?php echo $gateway->getDBQuery()->display('character'); ?>

    <table>
    <caption><?php e(t('E-mails')); ?></caption>
    <thead>
        <tr>
            <th><?php e(t('Sent')); ?></th>
            <th><?php e(t('Subject')); ?></th>
            <th><?php e(t('Contact')); ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($emails as $email) : ?>
    <tr>
        <td><?php e($email['date_sent_dk']); ?></td>
        <td><a href="<?php e(url($email['id'])); ?>"><?php e($email['subject']); ?></a></td>
        <td><a href="<?php e($contact_module->getPath()); ?><?php e($email['contact_id']); ?>"><?php e($email['contact_name']); ?></a></td>
        <td>
        <?php if (!empty($email['status']) and $email['status'] != 'sent') : ?>
            <a class="edit" href="<?php e(url($email['id'], array('edit'))); ?>"><?php e(t('edit')); ?></a>
            <a class="delete" href="<?php e(url($email['id'], array('delete'))); ?>"><?php e(t('delete')); ?></a>
        <?php else : ?>
            <?php e(t($email['status'])); ?>
        <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>

    <?php echo $gateway->getDBQuery()->display('paging'); ?>

<?php endif; ?>
