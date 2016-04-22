<h1>Regnskabsår</h1>

<div class="message">
    <p><strong>Regnskabsår</strong>. På denne side kan du enten oprette et nyt regnskab eller vælge hvilket regnskab, du vil begynde at indtaste poster i. Du vælger regnskabet på listen nedenunder.</p>
</div>

<ul class="options">
    <li><a class="new" href="<?php e(url('create')); ?>">Opret regnskabsår</a></li>
</ul>

<?php if (!$context->getYearGateway()->getList()) : ?>
    <p>Der er ikke oprettet nogen regnskabsår. Du kan oprette et ved at klikke på knappen ovenover.</p>
<?php else : ?>
    <form action="<?php e(url('./')); ?>" method="post">
    <input type="hidden" name="_method" value="put" />
    <table>
        <caption>Regnskabsår</caption>
        <thead>
            <tr>
                <th></th>
                <th>År</th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($context->getYearGateway()->getList() as $y) : ?>
        <tr>
            <td><input type="radio" name="id" value="<?php e($y['id']); ?>" <?php if ($context->getYear()->loadActiveYear() == $y['id']) {
                echo ' checked="checked"';
} ?>/></td>
            <td><a href="<?php e(url($y['id'])); ?>"><?php e($y['label']); ?></a></td>
            <td><a href="<?php e(url($y['id'] . '/vat')); ?>"><?php e(t('Vat')); ?></a></td>
            <td><a href="<?php e(url($y['id'] . '/end')); ?>"><?php e(t('End year')); ?></a></td>            
            <td class="options">
                <a class="edit" href="<?php e(url($y['id'] . '/edit')); ?>"><?php e(t('Edit')); ?></a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <input type="submit" value="<?php e(t('Choose')); ?>" />
    </form>
<?php endif; ?>
