<?php
$accounts = $context->getAccounts();
?>

<h1>Bogfør <a href="<?php e(url('../../year/' . $context->getYear()->getId())); ?>"><?php e($context->getYear()->get('label')); ?></a></h1>

<?php if ($context->getKernel()->setting->get('user', 'accounting.state.message') == 'view'): ?>
<div class="message">
	<p><strong>Bogf�r</strong>. P� denne side bogf�rer du posterne fra kassekladden. N�r du har bogf�rt bel�bene, kan du ikke l�ngere redigere i posterne.</p>
	<p><strong>Hvis du laver fejl</strong>. Hvis du har bogf�rt noget forkert, skal du lave et bilag med en rettelsespost, som du s� bogf�rer, s� dine konti kommer til at stemme.</p>
	<p><a href="<?php e($context->url()); ?>?message=hide">Skjul</a></p>
</div>
<?php endif; ?>


<h2>Afstemningskonti</h2>

<?php if ($context->getKernel()->setting->get('user', 'accounting.state.message2') == 'view'): ?>
<div class="message">
	<p><strong>Afstemning</strong>. Du b�r afstemme dine konti, inden du bogf�rer. Det betyder, at du fx b�r tjekke om bel�bene p� dit kontoudtog er magen til det bel�b, der bliver bogf�rt.</p>
	<p><a href="<?php e($context->url()); ?>?message2=hide">Skjul</a></p>
</div>
<?php endif; ?>

<?php if (count($context->getAccounts()) > 0) { ?>

<table class="stripe">
<caption>Afstemningskonti (<a href="<?php e($context->url('../../settings')); ?>">skift konti</a>)</caption>
<thead>
	<tr>
		<th scope="col">Kontonummer</th>
		<th scope="col">Kontonavn</th>
		<th scope="col">Startsaldo</th>
		<th scope="col">Bev�gelse</th>
		<th scope="col">Slutsaldo</th>
	</tr>
</thead>
<tbody>
	<?php foreach ($context->getAccounts() as $account) { ?>
	<tr>
		<td><a href="<?php e(url('../../account/' . $account['id'])); ?>"><?php e($account['number']); ?></a></td>
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

<h2>Bogf�r</h2>

<?php echo $context->getVoucher()->error->view(); ?>

<?php if (!$context->getYear()->vatAccountIsSet()): ?>
	<p class="message-dependent">Du skal f�rst <a href="<?php e(url('../../settings')); ?>">s�tte momskonti</a>, inden du kan bogf�re.</p>
<?php elseif ($context->getVoucher()->get('list_saldo') > 0): ?>
	<p class="error">Kassekladden balancerer ikke. Du kan ikke bogf�re, f�r den balancerer.</p>
<?php elseif (count($context->getPosts()) > 0): // der skal kun kunne bogf�res, hvis der er nogle poster ?>
<form action="<?php e($context->url()); ?>" method="post">
	<fieldset>
		<p>Bogf�r posterne og t�m kassekladden. Husk, at du ikke l�ngere kan redigere i posterne, n�r du har klikket p� knappen. Bev�gelserne kan derefter ses i regnskabet.</p>
		<div><input type="submit" value="Bogfør" name="state" onclick="return confirm('Er du sikker på, at du vil bogføre?');" /></div>
	</fieldset>
</form>
<?php else: ?>
	<p class="message-dependent">Der er ingen poster i kassekladden. Du skal <a href="<?php e($context->url('../')); ?>">indtaste poster i kassekladden</a>, inden du kan bogf�re.</p>
<?php endif; ?>
