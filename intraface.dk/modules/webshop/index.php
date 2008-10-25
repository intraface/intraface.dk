<?php
require('../../include_first.php');

$webshop_module = $kernel->module('webshop');
$settings = $webshop_module->getSetting('show_online');
$translation = $kernel->getTranslation('webshop');
$webshop_module->includeFile('BasketEvaluation.php');

$error = new Intraface_Error();

if (!empty($_POST)) {
    // mangler validering

    $validator = new Intraface_Validator($error);
    $validator->isNumeric($_POST['show_online'], 'show_online skal være et tal');
    //$validator->isNumeric($_POST['discount_limit'], 'discount_limit skal være et tal');
    //$validator->isNumeric($_POST['discount_percent'], 'discount_percent skal være et tal');
    $validator->isString($_POST['confirmation_text'], 'confirmation text is not valid');
    $validator->isString($_POST['webshop_receipt'], 'webshop receipt is not valid', '<p><br/><div><ul><ol><li><h2><h3><h4>');

    if (!$error->isError()) {

        $kernel->setting->set('intranet','webshop.show_online', $_POST['show_online']);
        $kernel->setting->set('intranet','webshop.confirmation_text', $_POST['confirmation_text']);
        $kernel->setting->set('intranet','webshop.webshop_receipt', $_POST['webshop_receipt']);

        header('Location: index.php');
        exit;
    } else {
        $value = $_POST;
    }
} else {
    $value['show_online'] = $kernel->setting->get('intranet','webshop.show_online');
    $value['confirmation_text'] = $kernel->setting->get('intranet','webshop.confirmation_text');
    $value['webshop_receipt'] = $kernel->setting->get('intranet','webshop.webshop_receipt');
}

if (isset($_GET['delete_basketevaluation_id'])) {
    $basketevaluation = new BasketEvaluation($kernel, $_GET['delete_basketevaluation_id']);
    $basketevaluation->delete();
}

$page = new Intraface_Page($kernel);
$page->start($translation->get('webshop'));

?>
<h1><?php e($translation->get('webshop')); ?></h1>

<p class="message">
    <?php e($translation->get('here you edit your settings for the webshop')); ?>
</p>

<ul>
    <li><a href="featuredproducts.php"><?php e($translation->get('choose featured products')); ?></a></li>
</ul>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">

    <?php echo $error->view(); ?>

    <fieldset>
        <legend><?php e($translation->get('what to show in the webshop')); ?></legend>
        <div class="formrow">
        <label>Vis</label>

            <select name="show_online">
            <?php
                foreach ($settings AS $k=>$v) { ?>
                    <option value="<?php e($k); ?>"
                    <?php if (!empty($value['show_online']) AND $k == $value['show_online']) echo ' selected="selected"'; ?>
                    ><?php e($translation->get($v)); ?></option>
                <?php }
            ?>
            </select>
        </div>
    </fieldset>

    <fieldset>
        <legend><?php e($translation->get('which payment methods are available')); ?></legend>

        <?php if ($kernel->setting->get('intranet', 'bank_account_number')): ?>
            <div class="formrow">
                <label for="payment_method_bank_transfer"><?php e(t('bank transfer')); ?></label>
                <input type="checkbox" name="payment_method_bank_transfer" id="payment_method_bank_transfer" value="bank_transfer" />
            </div>
        <?php endif; ?>


         <?php if ($kernel->setting->get('intranet', 'giro_account_number')): ?>
            <div class="formrow">
                <label for="payment_method_giro_payment"><?php e(t('giro payment')); ?></label>
                <input type="checkbox" name="payment_method_giro_payment" id="payment_method_giro_payment" value="giro_payment" />
            </div>
        <?php endif; ?>

        <?php if ($kernel->intranet->hasModuleAccess('onlinepayment')): ?>
            <div class="formrow">
                <label for="payment_method_online_payment"><?php e(t('online payment')); ?></label>
                <input type="checkbox" name="payment_method_online_payment" id="payment_method_online_payment" value="online_payment" />
            </div>
        <?php endif; ?>

    </fieldset>

    <fieldset>
        <legend><?php e($translation->get('order confirmation - including warranty and right of cancellation')); ?></legend>
        <div>
        <label for="confirmation_text"><?php e($translation->get('text')); ?></label><br />
        <textarea name="confirmation_text" cols="80" rows="10"><?php e($value['confirmation_text']); ?></textarea>
        </div>
    </fieldset>

    <fieldset>
        <legend><?php e($translation->get('webshop receipt')); ?></legend>
        <div>
        <label for="webshop_receipt"><?php e($translation->get('text')); ?></label><br />
        <textarea name="webshop_receipt" cols="80" rows="10"><?php e($value['webshop_receipt']); ?></textarea>
        </div>
    </fieldset>

    <p>
        <input type="submit" value="<?php e($translation->get('save', 'common')); ?>" />
    </p>

</form>

<fieldset>
    <legend><?php e($translation->get('basket evaluation')); ?></legend>



    <?php
    $basketevaluation = new BasketEvaluation($kernel);
    $evaluations = $basketevaluation->getList();

    if (count($evaluations) > 0):
        ?>
        <table summary="<?php e($translation->get('basket evaluation')); ?>" class="stripe">
            <caption><?php e($translation->get('basket evaluation')); ?></caption>
            <thead>
                <tr>
                    <th><?php e($translation->get('running index')); ?></th>
                    <th><?php e($translation->get('evaluation')); ?></th>
                    <th><?php e($translation->get('action')); ?></th>
                    <th><?php e($translation->get('go to index after')); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($evaluations AS $evaluation): ?>
                    <tr>
                        <td><?php e($evaluation['running_index']); ?></td>
                        <td><?php
                            e($translation->get('if').' '.$translation->get($evaluation['evaluate_target']).' ');
                            if ($evaluation['evaluate_method'] != 'equals') {
                                e($translation->get('is').' ');
                            }
                            e($translation->get($evaluation['evaluate_method']).' '.$evaluation['evaluate_value']);
                            if ($evaluation['evaluate_value_case_sensitive']) {
                                echo ' [<acronym title="'.e($translation->get('case sensitive')).'">CS</acronym>]';
                            }

                            ?>
                        </td>
                        <td><?php e($translation->get($evaluation['action_action']).' '.$evaluation['action_value'].' '.$translation->get('at').' '.$evaluation['action_quantity'].' '.$translation->get($evaluation['action_unit'])); ?></td>
                        <td><?php e($evaluation['go_to_index_after']); ?></td>
                        <td><a href="edit_basketevaluation.php?id=<?php e($evaluation['id']); ?>" class="edit"><?php e($translation->get('edit', 'common')); ?></a> <a href="index.php?delete_basketevaluation_id=<?php e($evaluation['id']); ?>" class="delete"><?php e($translation->get('delete', 'common')); ?></a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    endif;
    ?>

    <p><a href="edit_basketevaluation.php"><?php e($translation->get('add basket evaluation')); ?></a></p>

</fieldset>

<?php
$page->end();
?>