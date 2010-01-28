<h1><?php e(t('Change price for products')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../', array('use_stored' => 'true'))); ?>"><?php e(t('Close')); ?></a></li>
</ul>

<p class="warning"><?php e(t('Use at own risk. There is no error handling at the moment.')); ?></p>

<form action="<?php e(url()); ?>" method="post">

<fieldset>
	<legend><?php e(t('Change price')); ?></legend>
	<label>
		<?php e(t('Price change')); ?>
		<input type="text" name="price_change" value="" />
	</label>
</fieldset>

<table>
<caption><?php e(t('Ready to change price for')); ?> <? e(count($products)); ?> <?php e(t('products')); ?></caption>
<thead>
	<tr>
		<th></th>
		<th><?php e(t('Number')); ?></th>
		<th><?php e(t('Name')); ?></th>
		<th><?php e(t('Price')); ?></th>
	</tr>
</thead>
<tbody>
<?php foreach ($products AS $p) { ?>

    	<tr>
    		<td><input type="checkbox" name="product_id[]" value="<?php e($p['id']); ?>" /></td>
    		<td><?php e($p['number']); ?></td>
    		<td><?php e($p['name']); ?></td>
            <td><?php e(number_format($p['price'], 2, ",", ".")); ?> kroner excl. moms</td>
        </tr>

<?php } // end foreach ?>
</tbody>
</table>

<div>
    <input type="submit" class="save" value="<?php e(t('Save')); ?>" />
    <a href="<?php e(url('../', array('use_stored' => true))); ?>"><?php e(t('Cancel')); ?></a>
</div>
</form>
