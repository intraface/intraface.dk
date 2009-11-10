<?php
require '../../include_first.php';

// $modul = $kernel->module('administration');
$translation = $kernel->getTranslation('controlpanel');

if (!empty($_POST)) {

    $user = new Intraface_User($kernel->user->get('id'));

    $value = $_POST;
    $address_value = $_POST;
    $address_value['name'] = $_POST['address_name'];
    $address_value['email'] = $_POST['address_email'];

    // hvis man ændrer e-mail skal man have en e-mail som en sikkerhedsforanstaltning
    // på den gamle e-mail

    if ($user->update($_POST)) {
        if ($user->getAddress()->validate($address_value) && $user->getAddress()->save($address_value)) {
            header("Location: user.php");
            exit;
        }
    }

} else {
    $user = new Intraface_User($kernel->user->get('id'));
    $value = $user->get();
    $address_value = $user->getAddress()->get();
}

$page = new Intraface_Page($kernel);
$page->start(t('edit user'));
?>

<h1><?php e(t('edit user')); ?></h1>

<ul class="options">
    <li><a href=""><?php e(t('close')); ?></a></li>
</ul>

<?php echo $user->error->view(); ?>
<?php echo $user->getAddress()->error->view(); ?>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">

<fieldset>
    <legend><?php e(t('information about user')); ?></legend>
    <div class="formrow">
        <label for="name"><?php e(t('e-mail', 'address')); ?></label>
        <input type="text" name="email" id="email" value="<?php e($value["email"]); ?>" />
        <p style="clear:both;"><?php e(t('your e-mail is also your username','controlpanel')); ?></p>
    </div>
</fieldset>


<fieldset>
    <legend><?php e(t('address information')); ?></legend>
    <div class="formrow">
        <label for="address_name"><?php e(t('name', 'address')); ?></label>
        <input type="text" name="address_name" id="address_name" value="<?php if (!empty($address_value["name"])) e($address_value["name"]); ?>" />
    </div>
    <div class="formrow">
        <label for="address"><?php e(t('address', 'address')); ?></label>
        <textarea name="address" id="address" rows="2"><?php if (!empty($address_value["address"])) e($address_value["address"]); ?></textarea>
    </div>
    <div class="formrow">
        <label for="postcode"><?php e(t('postal code and city', 'address')); ?></label>
        <input type="text" name="postcode" id="postcode" value="<?php if (!empty($address_value["postcode"])) e($address_value["postcode"]); ?>" size="4" />
        <input type="text" name="city" id="city" value="<?php if (!empty($address_value["city"])) e($address_value["city"]); ?>" />
    </div>
    <div class="formrow">
        <label for="country"><?php e(t('country', 'address')); ?></label>
        <input type="text" name="country" id="country" value="<?php if (!empty($address_value["country"])) e($address_value["country"]); ?>" />
    </div>
    <div class="formrow">
        <label for="address_email"><?php e(t('e-mail', 'address')); ?></label>
        <input type="text" name="address_email" id="address_email" value="<?php if (!empty($address_value["email"])) e($address_value["email"]); ?>" />
    </div>
    <div class="formrow">
        <label for="website"><?php e(t('website', 'address')); ?></label>
        <input type="text" name="website" id="website" value="<?php if (!empty($address_value["website"])) e($address_value["website"]); ?>" />
    </div>
    <div class="formrow">
        <label for="phone"><?php e(t('phone', 'address')); ?></label>
        <input type="text" name="phone" id="phone" value="<?php if (!empty($address_value["phone"])) e($address_value["phone"]); ?>" />
    </div>
</fieldset>

<p><input type="submit" name="submit" value="<?php e(t('save', 'common')); ?>" />
<a href="user.php"><?php e(t('Cancel', 'common')); ?></a></p>

</form>

<?php
$page->end();
?>
