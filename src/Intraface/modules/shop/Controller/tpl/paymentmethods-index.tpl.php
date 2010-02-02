<p><?php e(t('Choose payment method. If you choose more than one payment method, your customers will be able to choose between them when using your online shop.')); ?></p>

<form action="<?php e(url('./')); ?>" method="post">
<table>
<thead>
	<tr>
		<th></th>
		<th><?php e(t('Payment method')); ?></th>
		<th><?php e(t('Alternative text')); ?></th>
	</tr>
</thead>
<tbody>
<?php foreach ($methods as $key => $method): ?>
    <tr>
        <td><input id="payment-method-<?php e($key); ?>" type="checkbox" value="<?php e($key); ?>" name="method[<?php e($key); ?>]" <?php if (isset($chosen[$key])) echo ' checked="checked"'; ?>/></td>
        <td><label for="payment-method-<?php e($key); ?>"><?php e($method->getDescription()); ?></label></td>
        <td><input type="text" value="<?php if (isset($chosen[$key])) e($chosen[$key]['text']); ?>" name="text[<?php e($key); ?>]" /></td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>
<p><input type="submit" value="<?php e(t('Save')); ?>" /></p>
</form>