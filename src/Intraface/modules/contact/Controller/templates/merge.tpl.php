<h1><?php e(t('Merge contacts')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
</ul>

<?php if (count($context->getSimilarContacts()) == 0) {     ?>

<p><?php e(t('This contact does not look like anyone else.')); ?></p>

<?php } else { ?>

    <p><strong><?php e(t('Attention')); ?></strong>:
    <?php e(t('When merging contacts, you need to select the contact you want to keep. When you have chosen the correct contact, you can choose which other contacts to merge. All the information about the original contact will be lost.')); ?>
    </p>
    <table style="font-size: 0.8em;">
        <caption>Denne kontakt</caption>
        <thead>
          <tr>
            <th></th>
            <th>Nummer</th>
            <th>Navn</th>
            <th>Adresse</th>
            <th>Postby</th>
            <th>Telefon</th>
            <th>E-mail</th>
        </tr>
        </thead>
        <tbody>
            <tr>
                <td></td>
                <td><?php e($context->getContact()->get('number')); ?></td>
                <td><?php e($context->getContact()->address->get('name')); ?></td>
                <td><?php e($context->getContact()->address->get('address')); ?></td>
                <td><?php e($context->getContact()->address->get('postcode')); ?> <?php e($context->getContact()->get('city')); ?></td>
                <td><?php e($context->getContact()->address->get('phone')); ?></td>
                <td><?php e($context->getContact()->address->get('email')); ?></td>
            </tr>
        </tbody>
    </table>

    <form method="post" action="<?php e(url()); ?>">
    <table style="font-size: 0.8em;">
        <caption><?php e(t('Similar to the following contacts')); ?></caption>
        <thead>
          <tr>
            <th></th>
            <th>Nummer</th>
            <th>Navn</th>
            <th>Adresse</th>
            <th>Postby</th>
            <th>Telefon</th>
            <th>E-mail</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($context->getSimilarContacts() as $c) { ?>
            <tr>
                <td><input type="checkbox" value="<?php e($c['id']); ?>" name="contact[]" /></td>
                <td><a href="<?php e(url('../../' . $c['id'])); ?>"><?php e($c['number']); ?></a></td>
                <td><?php e($c['name']); ?></td>
                <td><?php e($c['address']); ?></td>
                <td><?php e($c['postcode']); ?> <?php e($c['city']); ?></td>
                <td><?php e($c['phone']); ?></td>
                <td><?php e($c['email']); ?></td>
                <td><a href="<?php e(url('../../' . $c['id'] . '/merge')); ?>"><?php e(t('Switch to')); ?></a></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <label>
    <input type="checkbox" value="yes" name="delete_merged_contacts" />
    <?php e(t('Delete merged contacts')); ?>
    </label>

    <p><input type="submit" value="<?php e(t('Merge contacts')); ?>" /></p>
    </form>
<?php } ?>