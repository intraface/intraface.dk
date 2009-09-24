<?php
$value = $context->getValues();
$value_address = $context->getValues();
?>
<div id="ColOne">

<h1>
    <?php e(__('User')); ?>: <?php e($value['email']); ?><?php if (isset($intranet)) { ?>, intranet: <?php e($intranet->get('name')); ?><?php } ?>
</h1>

<ul class="options">
    <?php if (isset($intranet)) { ?>
        <li><a href="<?php e(url(null, array('edit', 'intranet_id' => $intranet->get('id')))); ?>"><?php e(__('edit', 'common')); ?></a></li>
    <?php } else { ?>
        <li><a href="<?php e(url(null, array('edit'))); ?>"><?php e(__('edit', 'common')); ?></a></li>
    <?php } ?>
    <li><a href="<?php e(url('../')); ?>"><?php e(__('close', 'common')); ?></a></li>
</ul>

<?php if ($context->query('flare')): ?>
	<p class="message"><?php e(__($context->query('flare'))); ?></p>
<?php endif; ?>


<?php echo $context->getUser()->error->view(); ?>

<?php
if ($context->getIntranet()->getId() > 0) {
        $edit_intranet_id = $context->getIntranet()->getId();
    ?>
    <table>
        <tr>
            <th><?php e(__('name', 'address')); ?></th>
            <td><?php if (isset($value_address['name'])) e($value_address["name"]); ?></td>
        </tr>
        <tr>
            <th><?php e(__('address', 'address')); ?></th>
            <td><?php if (isset($value_address['address'])) autohtml($value_address["address"]); ?></td>
        </tr>

        <tr>
            <th><?php e(__('postal code and city', 'address')); ?></th>
            <td><?php if (isset($value_address['postcode'])) e($value_address["postcode"]); ?> <?php if (isset($value_address['city'])) e($value_address["city"]); ?></td>
        </tr>
        <tr>
            <th><?php e(__('country', 'address')); ?></th>
            <td><?php if (isset($value_address['country'])) e($value_address["country"]); ?></td>
        </tr>
        <tr>
            <th><?php e(__('e-mail', 'address')); ?></th>
            <td><?php if (isset($value_address['email'])) e($value_address["email"]); ?></td>
        </tr>
        <tr>
            <th><?php e(__('website', 'address')); ?></th>
            <td><?php if (isset($value_address['website'])) e($value_address["website"]); ?></td>
        </tr>

        <tr>
            <th><?php e(__('phone', 'address')); ?></th>
            <td><?php if (isset($value_address['phone'])) e($value_address["phone"]); ?></td>
        </tr>
    </table>


    <form action="<?php e(url('permission', array('intranet_id' => $context->query('intranet_id')))); ?>" method="post">

    <fieldset>
        <legend>Access to intranet</legend>
        <div>
            <input type="checkbox" name="intranetaccess" id="intranetaccess" value="1" <?php if ($context->getUser()->hasIntranetAccess()) print("checked=\"checked\""); ?> />
            <label for="intranetaccess">Adgang til intranettet</label>
        </div>
    </fieldset>

    <fieldset>
        <legend>Adgang til moduler</legend>
        <?php
        foreach ($context->getModules() as $module) {
            if ($context->getIntranet()->hasModuleAccess(intval($module["id"]))) {
                ?>
                <div>
                    <input type="checkbox" name="module[]" id="module_<?php e($module["name"]); ?>" value="<?php e($module["name"]); ?>" <?php if ($context->getUser()->hasModuleAccess(intval($module["id"]))) print("checked=\"checked\""); ?> />
                    <label for="module_<?php e($module["name"]); ?>"><?php e($module["menu_label"]); ?></label>
                    <?php if (!empty($module["sub_access"]) AND count($module["sub_access"]) > 0): ?>
                      <ol>
                      <?php for ($j = 0; $j < count($module["sub_access"]); $j++): ?>
                          <li><input type="checkbox" name="sub_access[<?php e($module["name"]); ?>][]" id="sub_<?php e($module["sub_access"][$j]["name"]); ?>" value="<?php e($module["sub_access"][$j]["name"]); ?>"<?php if ($context->getUser()->hasSubAccess(intval($module["id"]), intval($module["sub_access"][$j]["id"]))) print(" checked=\"checked\""); ?> />
                          <label for="sub_<?php e($module["sub_access"][$j]["name"]); ?>"><?php e($module["sub_access"][$j]["description"]); ?></label></li>
                      <?php endfor; ?>
                      </ol>
                      <?php endif; ?>
                </div>
                <?php } }
        ?>
    </fieldset>

    <input type="hidden" name="id" value="<?php e($context->getUser()->get("id")); ?>" />
    <input type="hidden" name="intranet_id" value="<?php e($context->getIntranet()->get("id")); ?>" />
    <input type="submit" name="submit" value="Gem" />
    </form>
    <?php
}
?>
</div>

<div id="colTwo">

<table class="stribe">
    <caption>Intranet</caption>
    <thead>
    <tr>
        <th>Navn</th>
        <th></th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($context->getIntranets() as $intranet_value) { ?>
        <tr>
            <td><a href="<?php e(url('../../intranet/' . $intranet_value['id'])); ?>"><?php e($intranet_value['name']); ?></a></td>
            <td><a href="<?php e(url('../../intranet/' . $intranet_value['id'] . '/user/' . $context->getUser()->getId())); ?>"><?php e(__('Show contact information')); ?></a></td>
        </tr>
        <?php } ?>
    </tbody>
</table>

</div>
