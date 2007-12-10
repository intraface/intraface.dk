<?php
require('../../include_first.php');

$kernel->module('accounting');
$translation = $kernel->getTranslation('accounting');

$year = new Year($kernel);
$year->checkYear();

// hvis submitted
if (!empty($_POST)) {

    // måske noget validering
    $year->setSettings($_POST);
    /**
     * Seems to not be used anymore
    $kernel->setting->set('intranet', 'accounting.vat_period', $_POST['vat_period']);
     */
    
    header('Location: setting.php');
    exit;

}
else {
    $setting = $year->getSettings();
}

// find alle konti - kun status
$accounts = array();
$account = new Account($year);
$status_accounts = $account->getList('status');
$drift_accounts = $account->getList('drift');
$buy_accounts = $account->getList('expenses');
$finance_accounts = $account->getList('finance');
$accounts = $account->getList();

$page = new Page($kernel);
$page->start('Indstillinger');
?>

<h1>Indstillinger for <?php echo $year->get('label'); ?></h1>

<div class="message">
    <p><strong>Indstillinger</strong>. På denne side kan du sætte særlige egenskaber for de enkelte konti.</p>
</div>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
    <fieldset>
    <legend>Resultatopgørelseskonto</legend>
        <div class="formrow">
            <label for="result_account">Resultatopgørelse</label>
            <select id="result_account" name="result_account_id">
                <option value="">Vælg</option>
                <?php foreach ($status_accounts AS $account) { ?>
                    <option value="<?php echo $account['id']; ?>"<?php if($setting['result_account_id']==$account['id']) { echo ' selected="selected"'; } ?>><?php echo $account['number']; ?> <?php echo $account['name']; ?></option>
                <?php } ?>
            </select>
        </div>
    </fieldset>

    <fieldset>
    <legend>Kapitalkonto</legend>
        <div class="formrow">
            <label for="capital_account">Kapitalkonto</label>
            <select id="capital_account" name="capital_account_id">
                <option value="">Vælg</option>
                <?php foreach ($status_accounts AS $account) { ?>
                    <option value="<?php echo $account['id']; ?>"<?php if($setting['capital_account_id']==$account['id']) { echo ' selected="selected"'; } ?>><?php echo $account['number']; ?> <?php echo $account['name']; ?></option>
                <?php } ?>
            </select>
        </div>
    </fieldset>


    <fieldset>
        <legend>Opdeling af kontoplanen</legend>
        <div class="formrow">
            <label for="result_account_id_start">Resultat - første driftkonto</label>
            <select id="result_account_id_start" name="result_account_id_start">
                <option value="">Vælg</option>
                <?php foreach ($accounts AS $account) { ?>
                    <option value="<?php echo $account['id']; ?>"<?php if($setting['result_account_id_start']==$account['id']) { echo ' selected="selected"'; } ?>><?php echo $account['number']; ?> <?php echo $account['name']; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="formrow">
            <label for="result_account_id_end">Resultat - sidste driftkonto</label>
            <select id="result_account_id_end" name="result_account_id_end">
                <option value="">Vælg</option>
                <?php foreach ($accounts AS $account) { ?>
                    <option value="<?php echo $account['id']; ?>"<?php if($setting['result_account_id_end']==$account['id']) { echo ' selected="selected"'; } ?>><?php echo $account['number']; ?> <?php echo $account['name']; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="formrow">
            <label for="balance_account_id_start">Balance - første statuskonto</label>
            <select id="balance_account_id_start" name="balance_account_id_start">
                <option value="">Vælg</option>
                <?php foreach ($accounts AS $account) { ?>
                    <option value="<?php echo $account['id']; ?>"<?php if($setting['balance_account_id_start']==$account['id']) { echo ' selected="selected"'; } ?>><?php echo $account['number']; ?> <?php echo $account['name']; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="formrow">
            <label for="balance_account_id_end">Balance - sidste statuskonto</label>
            <select id="balance_account_id_end" name="balance_account_id_end">
                <option value="">Vælg</option>
                <?php foreach ($accounts AS $account) { ?>
                    <option value="<?php echo $account['id']; ?>"<?php if($setting['balance_account_id_end']==$account['id']) { echo ' selected="selected"'; } ?>><?php echo $account['number']; ?> <?php echo $account['name']; ?></option>
                <?php } ?>
            </select>
        </div>

    </fieldset>

    <fieldset>
    <legend>Debitorkonto</legend>
        <div class="formrow">
            <label for="debtor_account">Debitorkonto</label>
            <select id="debtor_account" name="debtor_account_id">
                <option value="">Vælg</option>
                <?php foreach ($status_accounts AS $account) { ?>
                    <option value="<?php echo $account['id']; ?>"<?php if($setting['debtor_account_id']==$account['id']) { echo ' selected="selected"'; } ?>><?php echo $account['number']; ?> <?php echo $account['name']; ?></option>
                <?php } ?>
            </select>
        </div>
    </fieldset>

    <fieldset>
    <legend>Kreditorkonto</legend>
        <div class="formrow">
            <label for="credit_account">Kreditorkonto</label>
            <select id="credit_account" name="credit_account_id">
                <option value="">Vælg</option>
                <?php foreach ($status_accounts AS $account) { ?>
                    <option value="<?php echo $account['id']; ?>"<?php if($setting['credit_account_id']==$account['id']) { echo ' selected="selected"'; } ?>><?php echo $account['number']; ?> <?php echo $account['name']; ?></option>
                <?php } ?>
            </select>
        </div>
    </fieldset>


    <?php	if ($year->get('vat') > 0): ?>
    <fieldset>
    <legend>Momskonti</legend>
        <div class="formrow">
            <p>Følgende konti kan kun vælges mellem statuskonti.</p>
            <label for="vat_in">Indgående moms</label>
            <select id="vat_in" name="vat_in_account_id">
                <option value="">Vælg</option>
                <?php foreach ($status_accounts AS $account) { ?>
                <option value="<?php echo $account['id']; ?>"<?php if($setting['vat_in_account_id']==$account['id']) { echo ' selected="selected"'; } ?>><?php echo $account['number']; ?> <?php echo $account['name']; ?></option>
                <?php } ?>
            </select> Købsmoms
        </div>
        <div class="formrow">
            <label for="vat_out">Udgående moms</label>
            <select id="vat_out" name="vat_out_account_id">
                <option value="">Vælg</option>
                <?php foreach ($status_accounts AS $account) { ?>
                <option value="<?php echo $account['id']; ?>"<?php if($setting['vat_out_account_id']==$account['id']) { echo ' selected="selected"'; } ?>><?php echo $account['number']; ?> <?php echo $account['name']; ?></option>
                <?php } ?>
            </select> Salgsmoms
        </div>
        <div class="formrow">
            <label for="vat_abroad">Moms af varekøb mv. i udlandet</label>
            <select id="vat_abroad" name="vat_abroad_account_id">
                <option value="">Vælg</option>
                <?php foreach ($status_accounts AS $account) { ?>
                <option value="<?php echo $account['id']; ?>"<?php if($setting['vat_abroad_account_id']==$account['id']) { echo ' selected="selected"'; } ?>><?php echo $account['number']; ?> <?php echo $account['name']; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="formrow">
            <label for="vat_balance">Momsafregning</label>
            <select id="vat_balance" name="vat_balance_account_id">
                <option value="">Vælg</option>
                <?php foreach ($status_accounts AS $account) { ?>
                <option value="<?php echo $account['id']; ?>"<?php if($setting['vat_balance_account_id']==$account['id']) { echo ' selected="selected"'; } ?>><?php echo $account['number']; ?> <?php echo $account['name']; ?></option>
                <?php } ?>
            </select>
        </div>
        <!--
        <div class="formrow">
            <label for="vat_free">Konto for momsfrit salg</label>
            <select id="vat_free" name="vat_free_account_id">
                <option value="">Vælg</option>
                <?php foreach ($drift_accounts AS $account) { ?>
                <option value="<?php echo $account['id']; ?>"<?php if($setting['vat_free_account_id']==$account['id']) { echo ' selected="selected"'; } ?>><?php echo $account['number']; ?> <?php echo $account['name']; ?></option>
                <?php } ?>
            </select>
        </div>
        -->

    </fieldset>
        <!--
    <fieldset>
        <legend>Konti til udenlandshandel</legend>
        <p>Følgende konti kan kun vælges mellem driftskonti.</p>
-->
<!--
        <div class="formrow">
            <label for="eu_buy">Køb i EU-lande</label>
            <select id="eu_buy" name="eu_buy_account_id">
                <option value="">Vælg</option>
                <?php foreach ($drift_accounts AS $account) { ?>
                <option value="<?php echo $account['id']; ?>"<?php if(!empty($setting['eu_buy_account_id']) AND $setting['eu_buy_account_id']==$account['id']) { echo ' selected="selected"'; } ?>><?php echo $account['number']; ?> <?php echo $account['name']; ?></option>
                <?php } ?>
            </select>
        </div>
        -->
        <!--
        <div class="formrow">
            <label for="eu_sale">Salg til andre EU-lande</label>
            <select id="eu_sale" name="eu_sale_account_id">
                <option value="">Vælg</option>
                <?php foreach ($drift_accounts AS $account) { ?>
                <option value="<?php echo $account['id']; ?>"<?php if($setting['eu_sale_account_id']==$account['id']) { echo ' selected="selected"'; } ?>><?php echo $account['number']; ?> <?php echo $account['name']; ?></option>
                <?php } ?>
            </select>
        </div>
        -->
        <!--
        <div class="formrow">
            <label for="abroad_buy">Varekøb i udlandet</label>
            <select id="abroad_buy" name="abroad_buy_account_id">
                <option value="">Vælg</option>
                <?php foreach ($drift_accounts AS $account) { ?>
                <option value="<?php echo $account['id']; ?>"<?php if(!empty($setting['abroad_buy_account_id']) AND $setting['abroad_buy_account_id']==$account['id']) { echo ' selected="selected"'; } ?>><?php echo $account['number']; ?> <?php echo $account['name']; ?></option>
                <?php } ?>
            </select>
        </div>
        --><!--
    </fieldset>-->
    <?php endif; ?>


    <fieldset class="select">
    <legend>Konti til varekøb i EU (ikke Danmark)</legend>
        <p>Du kan vælge mellem alle driftskonti.</p>
        <?php foreach ($buy_accounts AS $account) { ?>
        <div>
            <input type="checkbox" name="buy_eu_accounts[]" id="buy_eu_account_<?php echo $account['id']; ?>" value="<?php echo $account['id']; ?>" <?php if(is_array($setting['buy_eu_accounts']) AND in_array($account['id'], $setting['buy_eu_accounts'])) { echo ' checked="checked"'; } ?>/> <label for="buy_eu_account_<?php echo $account['id']; ?>"><?php echo $account['number']; ?>  <?php echo $account['name']; ?></label>
        </div>
        <?php } ?>
    </fieldset>

    <fieldset class="select">
    <legend>Konti til varekøb uden for EU</legend>
        <p>Du kan vælge mellem alle driftskonti.</p>
        <?php foreach ($buy_accounts AS $account) { ?>
        <div>
            <input type="checkbox" name="buy_abroad_accounts[]" id="buy_abroad_account_<?php echo $account['id']; ?>" value="<?php echo $account['id']; ?>" <?php if(is_array($setting['buy_abroad_accounts']) AND in_array($account['id'], $setting['buy_abroad_accounts'])) { echo ' checked="checked"'; } ?>/> <label for="buy_abroad_account_<?php echo $account['id']; ?>"><?php echo $account['number']; ?>  <?php echo $account['name']; ?></label>
        </div>
        <?php } ?>
    </fieldset>



    <fieldset class="select">
    <legend>Afstemningskonti</legend>
        <p>Afstemningskonti vises under bogfør. Du kan vælge mellem finanskonti.</p>
        <!--<p class="message"><strong>Bemærk:</strong> Udregningerne tager foreløbig ikke højde for moms på momskonti, hvis du laver om på standardinstillingerne.</p>-->
        <?php foreach ($finance_accounts AS $account) { ?>
        <div>
            <input type="checkbox" name="balance_accounts[]" id="balance_account_<?php echo $account['id']; ?>" value="<?php echo $account['id']; ?>" <?php if(is_array($setting['balance_accounts']) AND in_array($account['id'], $setting['balance_accounts'])) { echo ' checked="checked"'; } ?>/> <label for="balance_account_<?php echo $account['id']; ?>"><?php echo $account['number']; ?>  <?php echo $account['name']; ?></label>
        </div>
        <?php } ?>
    </fieldset>

    <div>
        <input type="submit" name="submit" value="Gem" />
    </div>

</form>

<?php
$page->end();
?>
