<?php
$accounts = $context->getAccounts();
?>

<h1>Bogfør <?php e($context->getYear()->get('label')); ?></h1>

<?php if ($context->getKernel()->setting->get('user', 'accounting.state.message') == 'view'): ?>
<div class="message">
	<p><strong>Bogfør</strong>. På denne side bogfører du posterne fra kassekladden. Når du har bogført beløbene, kan du ikke længere redigere i posterne.</p>
	<p><strong>Hvis du laver fejl</strong>. Hvis du har bogført noget forkert, skal du lave et bilag med en rettelsespost, som du så bogfører, så dine konti kommer til at stemme.</p>
	<p><a href="<?php e($context->url()); ?>?message=hide">Skjul</a></p>
</div>
<?php endif; ?>


<h2>Afstemningskonti</h2>

<?php if ($context->getKernel()->setting->get('user', 'accounting.state.message2') == 'view'): ?>
<div class="message">
	<p><strong>Afstemning</strong>. Du bør afstemme dine konti, inden du bogfører. Det betyder, at du fx bør tjekke om beløbene på dit kontoudtog er magen til det beløb, der bliver bogført.</p>
	<p><a href="<?php e($context->url()); ?>?message2=hide">Skjul</a></p>
</div>
<?php endif; ?>

<?php if (!empty($accounts) AND count($accounts) > 0) { ?>

<table class="stripe">
<caption>Afstemningskonti (<a href="<?php e($context->url('../../settings')); ?>">skift konti</a>)</caption>
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
	<?php foreach ($context->getAccounts() as $account) { ?>
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

	<p class="message-dependent">Der er ikke angivet nogen afstemningskonti. Du kan angive afstemningskonti under <a href="<?php e($context->url('../../settings')); ?>">indstillingerne</a>.</p>

<?php } ?>

<h2>Bogfør</h2>

<?php echo $context->getVoucher()->error->view(); ?>

<?php if (!$context->getYear()->vatAccountIsSet()): ?>
	<p class="message-dependent">Du skal først <a href="setting.php">sætte momskonti</a>, inden du kan bogføre.</p>
<?php elseif ($context->getVoucher()->get('list_saldo') > 0): ?>
	<p class="error">Kassekladden balancerer ikke. Du kan ikke bogføre, før den balancerer.</p>
<?php elseif (!empty($posts) AND count($posts) > 0): // der skal kun kunne bogføres, hvis der er nogle poster ?>
<form action="<?php e($context->url()); ?>" method="post">
	<fieldset>
		<p>Bogfør posterne og tøm kassekladden. Husk, at du ikke længere kan redigere i posterne, når du har klikket på knappen. Bevægelserne kan derefter ses i regnskabet.</p>
		<div><input type="submit" value="Bogfør" name="state" onclick="return confirm('Er du sikker på, at du vil bogføre?');" /></div>
	</fieldset>
</form>
<?php else: ?>
	<p class="message-dependent">Der er ingen poster i kassekladden. Du skal <a href="<?php e($context->url('../')); ?>">indtaste poster i kassekladden</a>, inden du kan bogføre.</p>
<?php endif; ?>
