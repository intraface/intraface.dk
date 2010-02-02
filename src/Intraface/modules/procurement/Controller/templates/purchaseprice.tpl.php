<h1>Angiv indkøbspris</h1>

<?php echo $procurement->error->view(); ?>

<form method="POST" action="<?php e(url()); ?>" id="form_items">

	<table class="stripe">
        <caption>Produkter</caption>
        <thead>
            <tr>
                <th>Varenr</th>
                <th>Navn</th>
                <th>Antal</th>
                <th>Salgspris</th>
                <th>Indkøbspris pr. stk. ex. moms</th>
  		    </tr>
        </thead>
        <tbody>
  		    <?php for ($i = 0, $max = count($items); $i < $max; $i++): ?>
                <tr>
                    <td align="right">
                        <?php e($items[$i]['number']); ?>
                        <input type="hidden" name="items[<?php e($i); ?>][id]" value="<?php e($items[$i]['id']); ?>" />
                    </td>
                    <td><?php e($items[$i]["name"]) ?></td>
                    <td><?php e($items[$i]['quantity']); ?> <?php e(t($items[$i]['unit'], 'product')) ?></td>
                    <td align="right"><?php e($items[$i]["price"]->getAsLocal('da_dk', 2)); ?></td>
                    <td><input type="text" name="items[<?php e($i); ?>][price]" value="0,00" size="8" /></td>
                </tr>
  			<?php endfor; ?>
        </tbody>
    </table>


<input type="submit" name="submit" value="Gem" class="save" />
<a href="<?php e(url('../')); ?>">Spring over</a>
<input type="hidden" name="id" value="<?php e($procurement->get("id")); ?>" />
</form>
