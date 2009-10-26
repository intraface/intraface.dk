<h1>Flet kontakter</h1>

<?php if (count($context->getSimilarContacts()) == 0) {	?>

<p>Denne kontakt ligner ikke andre, så du kan ikke flette den med nogen.</p>

<?php } else { ?>

    <p><strong>Bemærk</strong>: For at flette kontakter skal du gå ind under den kontakt, du gerne vil beholde. Når du har valgt kontakten, som har alle de rigtige kontaktoplysninger, kan du vælge hvilke kontakter, du gerne vil sammenflette kontakten med. Alle oplysninger om den oprindelige kontakt mistes.</p>

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
    <input type="hidden" value="<?php e($context->getContact()->get('id')); ?>" name="id" />
    <table style="font-size: 0.8em;">
        <caption>Ligner følgende kontakter</caption>
        <thead>
          <tr>
            <th></th>
            <th>Nummer</th>
            <th>Navn</th>
            <th>Adresse</th>
            <th>Postby</th>
            <th>Telefon</th>
            <th>E-mail</th>
            <th>Fakturaer</th>
        </tr>
        </thead>
        <tbody>
        <?php
            foreach ($context->getSimilarContacts() as $c) {
            ?>
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
            <?php
        }
        ?>
        </tbody>
    </table>
    <p><input type="submit" value="Flet kontakter" /></p>
    </form>
<?php } ?>