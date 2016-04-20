<h1><?php e(t('Letters')); ?> <?php e($context->getList()->get('title')); ?></h1>

<ul class="options">
    <li><a class="new" href="<?php e(url(null, array('new'))); ?>"><?php e(t('Create')); ?></a></li>
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>

</ul>

<?php echo $context->getLetter()->error->view(); ?>

<?php if (count($context->getLetters()) == 0) : ?>
    <p><?php e(t('No letters has been created.')); ?></p>
<?php else : ?>
<table class="stripe">
    <caption><?php e(t('Letters')); ?></caption>
    <thead>
    <tr>
        <th><?php e(t('Subject')); ?></th>
        <th><?php e(t('Status')); ?></th>
        <th><?php e(t('Subscribers')); ?></th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($context->getLetters() as $letter) : ?>
    <tr>
        <td><a href="<?php e(url($letter['id'])); ?>"><?php e($letter['subject']); ?></a></td>
        <td><?php e(t($letter['status'])); ?></td>
        <td>
            <?php
            if ($letter['status'] == 'sent') :
                e($letter['sent_to_receivers']);
            else :
                    e(t('Not sent'));
            endif;
            ?>
        </td>
        <td class="buttons">
            <?php if ($letter['status'] != 'sent') : ?>
                <a href="<?php e(url($letter['id'] . '/send')); ?>"><?php e(t('Send')); ?></a>
                <a class="edit" href="<?php e(url($letter['id'], array('edit'))); ?>"><?php e(t('Edit')); ?></a>
                <a class="delete" href="<?php e(url($letter['id'], array('delete'))); ?>"><?php e(t('Delete')); ?></a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
