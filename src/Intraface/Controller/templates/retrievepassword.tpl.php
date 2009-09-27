<h1><?php e(t('Retrieve password')); ?></h1>

<?php if (isset($context->msg)): ?>
	<p class="error"><?php echo $context->msg; ?></p>
<?php endif; ?>

<form method="post" action="<?php e(url(null)); ?>" id="forgotten_email_form">
	<p><?php e(t('Silly you, but luckily we are here to help you.')); ?></p>
	<fieldset>
		<label id="email_label"><?php e(t('Email')); ?></label>
		<input type="text" name="email" id="email"  />
		<input type="submit" name="submit" value="<?php e(t('Help')); ?>" id="submit" />
	</fieldset>
</form>