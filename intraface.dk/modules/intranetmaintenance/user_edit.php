<?php
require('../../include_first.php');

$modul = $kernel->module("intranetmaintenance");

if(isset($_POST["submit"])) {

    $user = new UserMaintenance(intval($_POST["id"]));

    if(isset($_POST["intranet_id"]) && intval($_POST["intranet_id"]) != 0) {
        $intranet = new Intranet($_POST["intranet_id"]);
        $intranet_id = $intranet->get("id");
        $address_value = $_POST;
        $address_value["name"] = $_POST["address_name"];
    } else {
        $intranet_id = 0;
        $address_value = array();
    }

    $value = $_POST;

    if($user->update($_POST)) {
        if(isset($intranet)) {
            $user->setIntranetAccess($intranet->get('id'));
            $user->setIntranetId($intranet->get('id'));
            $user->getAddress()->save($address_value);


            header("location: user.php?id=".$user->get("id")."&intranet_id=".$intranet->get("id"));
        } else {
            header("Location: user.php?id=".$user->get("id"));
        }
    }
} else {
    if(isset($_GET["id"])) {
        $user = new UserMaintenance(intval($_GET["id"]));
        $value = $user->get();

        if(isset($_GET['intranet_id'])) {
            $intranet_id = intval($_GET["intranet_id"]);
            $user->setIntranetId($intranet_id);
            $address_value = $user->getAddress()->get();
        } else {
            $intranet_id = 0;
            $address_value = array();
        }
    } else {
        $user = new UserMaintenance();
        if(!isset($_GET['intranet_id'])) {
            trigger_error("When you create an user we require intranet_id", E_USER_ERROR);
        }
        $intranet_id = intval($_GET["intranet_id"]);
        $value = array();
        $address_value = array();
    }
}

$page = new Page($kernel);
$page->start('User');
?>

<h1><?php print('User'); ?></h1>

<?php echo $user->error->view(); ?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<fieldset>
    <legend>Oplysninger om bruger</legend>
    <div class="formrow">
        <label for="name">E-mail</label>
        <input type="text" name="email" id="email" value="<?php if(isset($value['email'])) print(safeToHtml($value["email"])); ?>" />
        <p style="clear:both;">Din e-mail er også dit brugernavn</p>
    </div>
    <div class="formrow">
        <label for="disabled">Deaktiveret</label>
        <input type="checkbox" name="disabled" id="disabled" value="1" <?php if(isset($value['disabled']) && $value["disabled"] == 1) print("checked=\"checked\""); ?> />
    </div>

    <div class="formrow">
        <?php
        // hvis en bruger er valgt skal teksten vises, ellers ikke
        if(isset($_GET["id"])) {
            ?>
            <p>Du kan vælge at angive en ny adgangskode.</p>
            <?php
        }
        ?>
        <label for="password">Adgangskode</label>
        <input type="password" name="password" id="password" />
    </div>
    <div class="formrow">
        <label for="confirm_password">Bekræft adgangskode</label>
        <input type="password" name="confirm_password" id="confirm_password" />
    </div>
</fieldset>
<input type="submit" name="submit" value="Gem" />  or <a href="user.php?id=<?php print(intval($user->get('id'))); ?>">Cancel</a>

<?php

if($intranet_id != 0) {
    ?>
    <fieldset>
        <legend>Adresse oplysninger</legend>
        <div class="formrow">
            <label for="address_name">Navn</label>
            <input type="text" name="address_name" id="address_name" value="<?php if(isset($address_value["name"])) print(safeToHtml($address_value["name"])); ?>" />
        </div>
        <div class="formrow">
            <label for="address">Adresse</label>
            <textarea name="address" id="address" rows="2"><?php if(isset($address_value["address"])) print(safeToHtml($address_value["address"])); ?></textarea>
        </div>
        <div class="formrow">
            <label for="postcode">Postnr og by</label>
            <input type="text" name="postcode" id="postcode" value="<?php if(isset($address_value["postcode"])) print(safeToHtml($address_value["postcode"])); ?>" size="4" />
            <input type="text" name="city" id="city" value="<?php if(isset($address_value["city"])) print(safeToHtml($address_value["city"])); ?>" />
        </div>
        <div class="formrow">
            <label for="country">Land</label>
            <input type="text" name="country" id="country" value="<?php if(isset($address_value["country"])) print(safeToHtml($address_value["country"])); ?>" />
        </div>
        <div class="formrow">
            <label for="address_email">E-mail</label>
            <input type="text" name="address_email" id="address_email" value="<?php if(isset($address_value["email"])) print(safeToHtml($address_value["email"])); ?>" disabled="disabled" />
        </div>
        <div class="formrow">
            <label for="website">Hjemmeside</label>
            <input type="text" name="website" id="website" value="<?php if(isset($address_value["website"])) print(safeToHtml($address_value["website"])); ?>" />
        </div>
        <div class="formrow">
            <label for="phone">Telefon</label>
            <input type="text" name="phone" id="phone" value="<?php if(isset($address_value["phone"])) print(safeToHtml($address_value["phone"])); ?>" />
        </div>
    </fieldset>
    <input type="submit" name="submit" value="Save" /> or <a href="user.php?id=<?php print(intval($user->get('id'))); ?>">Cancel</a>
    <?php
}
?>


<input type="hidden" name="id" id="id" value="<?php print(intval($user->get("id"))); ?>" />
<input type="hidden" name="intranet_id" value="<?php print(intval($intranet_id)); ?>" />

</form>

<?php
$page->end();
?>
