<?php
/**
 * Burde kunne vises på dato - og bør i øvrigt indkludere dbQuery();
 *
 */
require('../../include_first.php');

if (empty($_GET['id']) OR !is_numeric($_GET['id'])) {
	// burde nok være en 404
	header('Location: accounts.php');
	exit;
}

$kernel->module('accounting');
$translation = $kernel->getTranslation('accounting');

$year = new Year($kernel);
$year->checkYear();

$account = new Account($year, (int)$_GET['id']);

$saldo = 0;
$posts = array();
// primosaldo
$primo = $account->getPrimoSaldo();
$posts[0]['id'] = '';
$posts[0]['date'] = '';
$posts[0]['voucher_number'] = '';
$posts[0]['text'] = 'Primosaldo';
$posts[0]['debet'] = $primo['debet'];
$posts[0]['credit'] = $primo['credit'];
$posts[0]['saldo'] = $primo['debet'] - $primo['credit'];

$posts = array_merge($posts, $account->getPosts());

$page = new Intraface_Page($kernel);
$page->start('Kontooversigt');
?>

<h1><?php e($account->get('number')); ?>: <?php e($account->get('name')); ?></h1>

<ul class="options">
	<li><a href="account_edit.php?id=<?php e($account->get('id')); ?>">Ret</a></li>
	<li><a href="accounts.php?from_account_id=<?php e($account->get('id')); ?>">Luk</a></li>
</ul>

<!-- Følgende bør vises her, men kunne skjules med en indstilling
<table>
	<tr>
		<th rowspan="2">Beskrivelse</th>
		<td rowspan="2"><?php e($account->get('comment')); ?></td>
	</tr>
	<tr>
		<th>Type</th>
		<td><?php e($account->get('type')); ?></td>	</tr>
	<tr>
		<th>Moms</th>
		<td><?php e($account->get('vat')); ?></td>
	</tr>
</table>
-->

<p><?php e(t('vat')); ?>: <?php e(t($account->get('vat'))); ?> <?php if ($account->get('vat') != 'none'): ?><?php e(number_format($account->get('vat_percent'), 2, ',', '.').'%'); ?><?php endif; ?></p>

<?php if (!empty($posts) AND is_array($posts) AND count($posts) > 0) { ?>
	<table>
		<caption>Konti</caption>
		<thead>
			<tr>
					<th>Dato</th>
					<th>Bilag</th>
					<th>Tekst</th>
					<th>Debet</th>
					<th>Kredit</th>
					<th>Saldo</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($posts AS $post) { $saldo = $saldo + $post['debet'] - $post['credit']; ?>
			<tr>
				<td><?php if (isset($post['dk_date'])) e($post['dk_date']); ?></td>
				<td><?php if (isset($post['voucher_id'])): ?><a href="voucher.php?id=<?php e($post['voucher_id']); ?>"><?php e($post['voucher_number']); ?></a><?php endif; ?></td>
				<td><?php e($post['text']); ?></td>
				<td class="amount"><?php e(amountToOutput($post['debet'])); ?></td>
				<td class="amount"><?php e(amountToOutput($post['credit'])); ?></td>
				<td class="amount"><?php e(amountToOutput($saldo)); ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>

<?php } else { ?>
	<p>Der er endnu ikke bogført nogle poster på denne konto.</p>
<?php } // else ?>

<?php
$page->end();
?>