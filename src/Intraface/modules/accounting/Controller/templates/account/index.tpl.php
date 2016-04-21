<h1>Konti <a href="<?php e(url('../')); ?>"><?php e($context->getYear()->get('label')); ?></a></h1>

<div class="message">
    <p><strong>Kontoplan</strong>. Dette er en oversigt over alle dine konti, hvor du kan se saldoen, rette de enkelte konti og slette dem. Hvis du vil se bevægelserne på den enkelte konto, kan du klikke på kontonavnet.</p>
</div>

<ul class="options">
    <li><a href="<?php e(url(null, array('create'))); ?>">Opret konto</a></li>
    <li><a class="excel" href="<?php e(url(null . '.xls')); ?>">Excel</a></li>
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
</ul>
<?php
/*
<form action="<?php e(url(null)); ?>" method="get">
	<fieldset>
		<legend>Avanceret visning</legend>
		<label id="date-from">Fra
			<input type="text" name="from_date" id="date-from" value="<?php e($values['from_date']); ?>" />
		</label>
		<label id="date-to">Til
			<input type="text" name="to_date" id="date-to" value="<?php e($values['to_date']); ?>" />
		</label>
		<input type="submit" value="Vis" />
	</fieldset>
</form>
*/
?>
<?php echo $context->getAccount()->error->view(); ?>

<?php if (count($accounts) == 0) : ?>
    <div class="message-dependent">
        <p>Der er endnu ikke oprettet nogen konti.</p>
        <p>Du kan oprette en standardkontoplan under <a href="<?php e(url('../year/' . $year->get('id'))); ?>">regnskabsåret</a>, eller du kan taste dem manuelt ind ved at klikke på opret konto ovenfor.</p>
    </div>
<?php else : ?>
<table>
    <caption>Kontoplan</caption>
    <thead>
        <tr>
            <th>Kontonummer</th>
            <th>Kontonavn</th>
            <th>Type</th>
            <th>Moms</th>
            <th>Saldo</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($accounts as $account) : ?>

        <tr<?php if ($account['type'] == 'headline') {
            echo ' class="headline"';
} elseif ($account['type'] == 'sum') {
    echo ' class="sum"';
} ?><?php if (!empty($_GET['from_account_id']) and $_GET['from_account_id'] == $account['id']) {
    echo ' id="'.$account['id'].'" class="fade"';
} ?>>
            <?php if ($account['type'] != 'headline' and $account['type'] != 'sum') : ?>
                <td><a href="<?php e(url($account['id'])); ?>"><?php e($account['number']); ?></a></td>
            <?php else : ?>
                <td><?php e($account['number']); ?></td>
            <?php endif; ?>
            <?php if ($account['type'] == 'headline') : ?>
                <td colspan="4" class="headline"><?php e($account['name']); ?></td>
            <?php elseif ($account['type'] == 'sum') : ?>
                <td><?php e($account['name']); ?><?php if ($account['type'] == 'sum') {
                    e(' ('. $account["sum_from"] . ' til ' . $account["sum_to"] . ')');
} ?></td>
            <?php else :?>
                <td><a href="<?php e(url($account['id'])); ?>"><?php e($account['name']); ?></a></td>
            <?php endif; ?>

            <?php if ($account['type'] != 'headline') { ?>
            <td><?php e(t($account['type'])); ?></td>
            <td><?php if ($account['type'] == 'balance, asset' or $account['type'] == 'balance, liability' or $account['type'] == 'operating') {
                e(t($account['vat_shorthand']));
} ?></td>
            <td class="amount"><?php if (isset($account['saldo'])) {
                e(number_format($account['saldo'], 2, ",", "."));
} ?></td>
            <?php } ?>
            <td class="options">
                <a class="edit" href="<?php e(url($account['id'], array('edit'))); ?>">Ret</a>
                <a class="delete" href="<?php e(url($account['id'], array('delete'))); ?>">Slet</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>