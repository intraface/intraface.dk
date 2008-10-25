<?php
require('../../include_first.php');

$kernel->module('accounting');
$translation = $kernel->getTranslation('accounting');

$year = new Year($kernel);
$year->checkYear();

if (!empty($_GET['message']) AND in_array($_GET['message'], array('hide'))) {
	$kernel->setting->set('user', 'accounting.state.message', 'hide');
}
elseif (!empty($_GET['message2']) AND in_array($_GET['message2'], array('hide'))) {
	$kernel->setting->set('user', 'accounting.state.message2', 'hide');
}

// bogføre poster i kassekladden
if (!empty($_POST['state'])) {
	// hvordan skal dette laves?

	$voucher = new Voucher($year);
	// denne funktion vælger automatisk alle poster i kassekladden
	if (!$voucher->stateDraft()) {
		// $post->error->set('Posterne kunne ikke bogføres');
	}
	/*
	$post = new Post($voucher);
	$posts = $post->getList();
	*/
	header('Location: state.php');
	exit;

}
else {
	$voucher = new Voucher($year);
	$post = new Post($voucher);
}

$posts = $post->getList('draft');
$accounts = $year->getBalanceAccounts();


// starting page
$page = new Intraface_Page($kernel);
$page->start('Bogfør');
?>

<h1>Bogfør <?php e($year->get('label')); ?></h1>

<?php if ($kernel->setting->get('user', 'accounting.state.message') == 'view'): ?>
<div class="message">
	<p><strong>Bogfør</strong>. På denne side bogfører du posterne fra kassekladden. Når du har bogført beløbene, kan du ikke længere redigere i posterne.</p>
	<p><strong>Hvis du laver fejl</strong>. Hvis du har bogført noget forkert, skal du lave et bilag med en rettelsespost, som du så bogfører, så dine konti kommer til at stemme.</p>
	<p><a href="<?php e($_SERVER['PHP_SELF']); ?>?message=hide">Skjul</a></p>
</div>
<?php endif; ?>


<h2>Afstemningskonti</h2>

<?php if ($kernel->setting->get('user', 'accounting.state.message2') == 'view'): ?>
<div class="message">
	<p><strong>Afstemning</strong>. Du bør afstemme dine konti, inden du bogfører. Det betyder, at du fx bør tjekke om beløbene på dit kontoudtog er magen til det beløb, der bliver bogført.</p>
	<p><a href="<?php e($_SERVER['PHP_SELF']); ?>?message2=hide">Skjul</a></p>
</div>
<?php endif; ?>

<?php if (!empty($accounts) AND count($accounts) > 0) { ?>

<table class="stripe">
<caption>Afstemningskonti (<a href="setting.php">skift konti</a>)</caption>
<thead>
	<tr>
		<th scope="col">Kontonummer</th>
		<th scope="col">Kontonavn</th>
		<th scope="col">Startsaldo</th>
		<th scope="col">Bevægelse</th>
		<th scope="col">Slutsaldo</th>
	</tr>
</thead>
<tbody>
	<?php foreach ($accounts AS $account) { ?>
	<tr>
		<td><a href="account.php?id=<?php e($account['id']); ?>"><?php e($account['number']); ?></a></td>
		<td><?php e($account['name']); ?></td>
		<td class="amount"><?php e(amountToOutput($account['saldo_primo'])); ?></td>
		<td class="amount"><?php e(amountToOutput($account['saldo_draft'])); ?></td>
		<td class="amount"><?php e(amountToOutput($account['saldo_ultimo'])); ?></td>
	</tr>
	<?php  } ?>
</tbody>
</table>

<?php } else { ?>

	<p class="message-dependent">Der er ikke angivet nogen afstemningskonti. Du kan angive afstemningskonti under <a href="setting.php">indstillingerne</a>.</p>

<?php } ?>

<h2>Bogfør</h2>

<?php echo $voucher->error->view(); ?>

<?php if (!$year->vatAccountIsSet()): ?>
	<p class="message-dependent">Du skal først <a href="setting.php">sætte momskonti</a>, inden du kan bogføre.</p>
<?php elseif ($voucher->get('list_saldo') > 0): ?>
	<p class="error">Kassekladden balancerer ikke. Du kan ikke bogføre, før den balancerer.</p>
<?php elseif (!empty($posts) AND count($posts) > 0): // der skal kun kunne bogføres, hvis der er nogle poster ?>
<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
	<fieldset>
		<p>Bogfør posterne og tøm kassekladden. Husk, at du ikke længere kan redigere i posterne, når du har klikket på knappen. Bevægelserne kan derefter ses i regnskabet.</p>
		<div><input type="submit" value="Bogfør" name="state" onclick="return confirm('Er du sikker på, at du vil bogføre?');" /></div>
	</fieldset>
</form>
<?php else: ?>
	<p class="message-dependent">Der er ingen poster i kassekladden. Du skal <a href="daybook.php">indtaste poster i kassekladden</a>, inden du kan bogføre.</p>
<?php endif; ?>


<?php
$page->end();
?>
