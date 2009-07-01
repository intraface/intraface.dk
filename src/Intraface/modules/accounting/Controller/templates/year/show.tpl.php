<?php
$values = $context->getYear()->get();
?>

<h1>Regnskab <?php e($context->getYear()->get('label')); ?></h1>

<ul class="options">
    <li><a class="edit" href="<?php e(url('edit')); ?>">Ret</a></li>
    <li><a class="setting" href="<?php e('../settings'); ?>">Indstillinger</a></li>
</ul>

<form action="<?php e(url('./')); ?>" method="post">

    <?php echo $context->getYear()->error->view(); ?>

    <input type="hidden" name="id" value="<?php e($values['id']); ?>" />
<?php if ($context->getAccount()->anyAccounts()): ?>
    <fieldset>
        <legend>Vælg og gå til regnskabet</legend>
        <div>
            <input type="submit" name="start" id="start" value="Vælg og gå til regnskabet" />
        </div>
    </fieldset>
<?php endif; ?>
<table>
    <caption>Oplysninger om regnskabsåret</caption>
    <tr>
        <th>Navn</th>
        <td><?php echo htmlentities($values['label']); ?></td>
    </tr>
    <tr>
        <th>Fra dato</th>
        <td><?php echo htmlentities($values['from_date_dk']); ?></td>
    </tr>
    <tr>
        <th>Til dato</th>
        <td><?php echo htmlentities($values['to_date_dk']); ?></td>
    </tr>
    <tr>
        <th>Sidste års regnskab</th>
        <td>
            <?php
                if (!empty($values['last_year_id']) AND $values['last_year_id'] > 0) {
                    $last_year = new Year($kernel, $values['last_year_id']);
                    e($last_year->get('label'));
                } else {
                    e('Ingen');
                }
            ?>
        </td>
    </tr>
    <tr>
        <th>Låst</th>
        <td>
            <?php
                if (!empty($values['locked']) AND $values['locked'] == 1) {
                    e('Ja');
                } else {
                    e('Nej');
                }
            ?>

        </td>
    </tr>
    <tr>
        <th>Moms</th>
        <td>
            <?php
                if (!empty($values['vat']) AND $values['vat'] == 1) {
                    echo 'Ja';
                }
                else {
                    echo 'Nej';
                }
            ?>
        </td>
    </tr>
</table>

<?php if (!$context->getAccount()->anyAccounts()): ?>
    <fieldset>
        <legend>Kontoplan</legend>
        <p>Du skal oprette en kontoplan for året. Du kan først begynde at gemme poster i kassekladden, når du har oprettet en kontoplan.</p>

        <div>
            <input type="submit" name="manual_accountplan" value="Jeg vil oprette kontoplanen manuelt" class="confirm" />
        </div>

        <div>
            <input type="submit" name="standard_accountplan" value="Jeg vil bruge standardkontoplanen" class="confirm" />
        </div>
        <?php if (count($context->getYearGateway()->getList()) - 1 > 0): // der skal trækkes en fra, for man kan ikke oprette kontoplaner fra sig selv ?>
        <div>
            <label for="accountplan_years">Jeg vil overføre kontoplanen fra</label>
            <select name="accountplan_year" id="accountplan_years">
                <option value="">Vælg...</option>
                <?php
                    foreach ($context->getYearGateway()->getList() as $y) {
                        if ($y['id'] == $context->getYear()->get('id')) continue;
                        echo '<option ';
                        if (!empty($values['accountplan_years']) AND $values['accountplan_years'] == $y['id']) echo ' selected="selected"';
                        echo 'value="'.$y['id'].'">'.$y['label'].'</option>';
                    }
                ?>
            </select>
            <input type="submit" name="transfer_accountplan" value="Hent" class="confirm" />
        </div>
        <?php endif; ?>
    </fieldset>
<?php else: ?>
    <h2>Kontoplan</h2>
    <p>Du kan finde dine konti under <a href="<?php e(url('../accounts')); ?>">kontoplanen</a>.</p>

    <?php if (!$context->getYear()->vatAccountIsSet()): ?>

        <p class="message-dependent">Du mangler at sætte nogle indstillinger. <a href="<?php (url('../settings')); ?>">Sæt indstillingerne</a>.</p>

    <?php elseif (!$context->getVatPeriod()->periodsCreated()): ?>
        <!--
        <p class="message-dependent">Du skal oprette momsperioder for året. <a href="vat_period.php">Opret momsperioder</a>.</p>
        -->
    <?php endif; ?>

    <h2>Primobalance</h2>
    <fieldset>
    <legend>Primobalance</legend>
    <p>På primobalancen kan du sætte de beløb, dit regnskab starter med. Listen vælger automatisk alle dine statuskonti fra kontoplanen. Statuskonti er de konti, som ikke nulstilles ved årets udløb.</p>
    <div>
        <input type="submit" name="primobalance" id="primobalance" value="Rediger primobalancen" />
    </div>
    </fieldset>
<?php endif; ?>
</form>
