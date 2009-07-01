<form action="<?php e(url('./')); ?>" method="post">
<table>
<?php foreach ($methods as $key => $method): ?>
    <tr>
        <td><input id="payment-method-<?php e($key); ?>" type="checkbox" value="<?php e($key); ?>" name="method[<?php e($key); ?>]" <?php if (isset($chosen[$key])) echo ' checked="checked"'; ?>/></td>
        <td><label for="payment-method-<?php e($key); ?>"><?php e($method->getDescription()); ?></label></td>
        <td><input type="text" value="<?php if (isset($chosen[$key])) e($chosen[$key]['text']); ?>" name="text[<?php e($key); ?>]" /></td>        
    </tr>
<?php endforeach; ?>
</table>
<p><input type="submit" value="<?php e(__('Save')); ?>" /></p>
</form>