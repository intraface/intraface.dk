<?php
$values = $post->get();
?>
<h1>Post p� bilag #<?php e($post->voucher->get('number')); ?></h1>

<form method="post" action="<?php e(url()); ?>">
    <input type="hidden" name="id" value="<?php e($post->get('id')); ?>" />
    <input type="hidden" name="voucher_id" value="<?php e($post->voucher->get('id')); ?>" />

    <?php echo $post->error->view(); ?>

    <fieldset>
        <legend>Indtast</legend>
        <table>
            <thead>
                <tr>
                    <th><label for="date">Dato</label></th>
                    <th><label for="text">Tekst</label></th>
                    <th><label for="account">Konto</label></th>
                    <th><label for="debet">Debet</label></th>
                    <th><label for="credit">Kredit</label></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <input tabindex="1" name="date" type="text" size="7" value="<?php if (!empty($values['date'])) e($values['date']);  ?>" />
                    </td>
                    <td>
                        <input tabindex="2" type="text" name="text" id="text" value="<?php if (!empty($values['text'])) e($values['text']); ?>" />
                    </td>
                    <td>
                        <select name="account" tabindex="3">
                            <option value="">V�lg</option>
                            <?php
                                foreach ($account->getList() AS $a):
                                    echo '<option value="'.$a['number'].'"';
                                    if (!empty($values['account_number']) AND $values['account_number'] == $a['number']) echo ' selected="selected"';
                                    echo '>'.$a['name'].'</option>';
                                endforeach;
                            ?>
                        </select>
                    </td>
                    <td>
                        <input tabindex="4" name="debet" id="amount" type="text" size="8"  value="<?php if (!empty($values['debet'])) e(amountToForm($values['debet'])); ?>" />
                    </td>

                    <td>
                        <input tabindex="5" name="credit" id="amount" type="text" size="8"  value="<?php if (!empty($values['credit'])) e(amountToForm($values['credit'])); ?>" />
                    </td>
                    <td>
                        <input tabindex="6" type="submit" id="submit" value="Gem" />
                    </td>
                </tr>
            </tbody>
    </table>
</fieldset>
</form>

<p><a href="<?php e(url('../../../')); ?>"><?php e(t('Cancel')); ?></a></p>
