<?php
require('../../include_first.php');

$modul = $kernel->module("intranetmaintenance");
$translation = $kernel->getTranslation('intranetmaintenance');


if (isset($_POST["submit"])) {

    $user = new UserMaintenance(intval($_POST["id"]));
    $intranet = new IntranetMaintenance(intval($_POST["intranet_id"]));
    $user->setIntranetId($intranet->get("id"));

    $modules = array();
    if (isset($_POST['module'])) {
        $modules = $_POST["module"];
    } else {
        $modules = array();
    }
    if (isset($_POST['sub_access'])) {
        $sub_access = $_POST["sub_access"];
    } else {
        $sub_access = array();
    }

    $user->flushAccess();

    if (!isset($_POST["intranetaccess"])) {
        // Access to intranet is not set. We show the user, but not with the intranet.
        $user_id = intval($_POST['id']);
        unset($user);
        unset($intranet);
    } else {
        // Sætter adgang til det redigerede intranet. Id kommer tidligere ved setIntranetId
        $user->setIntranetAccess();

        // Hvis en bruger retter sig selv, i det aktive intranet, sætter vi adgang til dette modul
        if ($kernel->user->get("id") == $user->get("id") && $kernel->intranet->get("id") == $intranet->get("id")) {
            // Finder det aktive intranet
            $active_module = $kernel->getPrimaryModule();
            // Giver adgang til det
            $user->setModuleAccess($active_module->getId());
        }

        for ($i = 0, $max = count($modules); $i < $max; $i++) {
            $user->setModuleAccess($modules[$i]);
            if (!empty($sub_access[$modules[$i]])) {
                for ($j = 0, $max1 = count($sub_access[$modules[$i]]); $j < $max1; $j++) {
                    $user->setSubAccess($modules[$i], $sub_access[$modules[$i]][$j]);
                }
            }
        }
        $user_id = $user->get('id');
        $edit_intranet_id = $intranet->get('id');

        header('Location: user.php?id='.$user_id.'&intranet_id='.$edit_intranet_id);
        exit;
    }


} elseif (isset($_GET['return_redirect_id'])) {
    if (isset($_GET['intranet_id'])) {
        $intranet = new IntranetMaintenance($_GET['intranet_id']);
        $edit_intranet_id = $intranet->get('id');
    }
    $redirect = Intraface_Redirect::factory($kernel, 'return');
    if ($redirect->get('identifier') == 'add_user') {
        $user = new UserMaintenance($redirect->getParameter('user_id'));
        $user->setIntranetAccess($intranet->get('id'));
        $user_id = $user->get('id');
    }
} else {
    if (!isset($_GET['id'])) {
        trigger_error("An id is required", E_USER_ERROR);
    }

    $user_id = intval($_GET['id']);
    if (isset($_GET['intranet_id'])) {
        $edit_intranet_id = intval($_GET['intranet_id']);
    }
}

$user = new UserMaintenance($user_id);

$value = $user->get();
$value_address = array();

if (isset($edit_intranet_id)) {

    $intranet = new IntranetMaintenance(intval($edit_intranet_id));
    $user->setIntranetId(intval($intranet->get('id')));
    $address = $user->getAddress();
    if (isset($address)) {
        $value_address = $user->getAddress()->get();
    }
}

$page = new Intraface_Page($kernel);
$page->start($translation->get('User'));
?>

<div id="ColOne">

<h1>
    <?php e($translation->get('User')); ?>: <?php e($value['email']); ?><?php if (isset($intranet)) { ?>, intranet: <?php e($intranet->get('name')); ?><?php } ?>
</h1>

<ul class="options">
    <?php if (isset($intranet)) { ?>
        <li><a href="user_edit.php?id=<?php e($user->get('id')); ?>&amp;intranet_id=<?php e($intranet->get('id')); ?>"><?php e($translation->get('edit', 'common')); ?></a></li>
    <?php } else { ?>
        <li><a href="user_edit.php?id=<?php e($user->get('id')); ?>"><?php e($translation->get('edit', 'common')); ?></a></li>
    <?php } ?>
    <li><a href="users.php"><?php e($translation->get('close', 'common')); ?></a></li>
</ul>

<?php echo $user->error->view(); ?>

<?php
if (isset($intranet)) {
    ?>
    <table>
        <tr>
            <th><?php e($translation->get('name', 'address')); ?></th>
            <td><?php if (isset($value_address['name'])) e($value_address["name"]); ?></td>
        </tr>
        <tr>
            <th><?php e($translation->get('address', 'address')); ?></th>
            <td><?php if (isset($value_address['address'])) autohtml($value_address["address"]); ?></td>
        </tr>

        <tr>
            <th><?php e($translation->get('postal code and city', 'address')); ?></th>
            <td><?php if (isset($value_address['postcode'])) e($value_address["postcode"]); ?> <?php if (isset($value_address['city'])) e($value_address["city"]); ?></td>
        </tr>
        <tr>
            <th><?php e($translation->get('country', 'address')); ?></th>
            <td><?php if (isset($value_address['country'])) e($value_address["country"]); ?></td>
        </tr>
        <tr>
            <th><?php e($translation->get('e-mail', 'address')); ?></th>
            <td><?php if (isset($value_address['email'])) e($value_address["email"]); ?></td>
        </tr>
        <tr>
            <th><?php e($translation->get('website', 'address')); ?></th>
            <td><?php if (isset($value_address['website'])) e($value_address["website"]); ?></td>
        </tr>

        <tr>
            <th><?php e($translation->get('phone', 'address')); ?></th>
            <td><?php if (isset($value_address['phone'])) e($value_address["phone"]); ?></td>
        </tr>
    </table>


    <form action="user.php" method="post">

    <fieldset>
        <legend>Access to intranet</legend>

        <div>
            <input type="checkbox" name="intranetaccess" id="intranetaccess" value="1" <?php if ($user->hasIntranetAccess()) print("checked=\"checked\""); ?> />
            <label for="intranetaccess">Adgang til intranettet</label>
        </div>
    </fieldset>

    <fieldset>
        <legend>Adgang til moduler</legend>

        <?php
        $module = new ModuleMaintenance;
        $modules = $module->getList();

        for ($i = 0; $i < count($modules); $i++) {
            if ($intranet->hasModuleAccess(intval($modules[$i]["id"]))) {
                ?>
                <div>
                    <input type="checkbox" name="module[]" id="module_<?php e($modules[$i]["name"]); ?>" value="<?php e($modules[$i]["name"]); ?>" <?php if ($user->hasModuleAccess(intval($modules[$i]["id"]))) print("checked=\"checked\""); ?> />
                    <label for="module_<?php e($modules[$i]["name"]); ?>"><?php e($modules[$i]["menu_label"]); ?></label>
                    <?php if (!empty($modules[$i]["sub_access"]) AND count($modules[$i]["sub_access"]) > 0): ?>
                      <ol>
                      <?php for ($j = 0; $j < count($modules[$i]["sub_access"]); $j++): ?>
                          <input type="checkbox" name="sub_access[<?php e($modules[$i]["name"]); ?>][]" id="sub_<?php e($modules[$i]["sub_access"][$j]["name"]); ?>" value="<?php e($modules[$i]["sub_access"][$j]["name"]); ?>"<?php if ($user->hasSubAccess(intval($modules[$i]["id"]), intval($modules[$i]["sub_access"][$j]["id"]))) print(" checked=\"checked\""); ?> />
                          <label for="sub_<?php e($modules[$i]["sub_access"][$j]["name"]); ?>"><?php e($modules[$i]["sub_access"][$j]["description"]); ?></label>
                      <?php endfor; ?>
                      </ol>
                      <?php endif; ?>
                </div>
                <?php } }
        ?>
    </fieldset>

    <input type="hidden" name="id" value="<?php e($user->get("id")); ?>" />
    <input type="hidden" name="intranet_id" value="<?php e($intranet->get("id")); ?>" />
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
    <?php
    $intranet = new IntranetMaintenance();
    $intranet->getDBQuery($kernel)->setFilter('user_id', $user->get('id'));

    $intranets = $intranet->getList();

    foreach ($intranets as $intranet_value) {
        ?>
        <tr>
            <td><a href="intranet.php?id=<?php e($intranet_value['id']); ?>"><?php e($intranet_value['name']); ?></a></td>
            <td><a href="user.php?id=<?php e($user->get('id')); ?>&amp;intranet_id=<?php e($intranet_value['id']); ?>"><?php e($translation->get('Show contact information')); ?></a></td>
        </tr>
        <?php
    }
    ?>
    </tbody>
</table>

</div>

<?php
$page->end();
?>
