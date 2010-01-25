<h1><?php e(t('Daybook for')); ?> <a href="<?php e(url('../year/' . $context->getYear()->getId())); ?>"><?php e($context->getYear()->get('label')); ?></a></h1>

<?php echo $message; ?>

<ul class="options">
    <li><a href="<?php e(url(null, array('view' => 'classic'))); ?>">Standard</a></li>
    <li><a href="<?php e(url(null, array('view' => 'income'))); ?>">Indtægter</a></li>
    <li><a href="<?php e(url(null, array('view' => 'expenses'))); ?>">Udgifter</a></li>
    <li><a href="<?php e(url(null, array('view' => 'debtor'))); ?>">Betalende debitor</a></li>
</ul>

<?php echo $context->getVoucher()->error->view(); ?>

<form method="post" action="<?php e(url(null, array('view' => $context->query('view')))); ?>" id="accounting-form-state">
 	<input type="hidden" name="id" value="<?php if (isset($values['id'])) e($values['id']); ?>" />

    <fieldset>
        <legend>Indtast</legend>
        <table>
			<?php echo $view; ?>
	    </table>
	</fieldset>
</form>

<?php echo $draft; ?>

<?php echo $cheatsheet; ?>