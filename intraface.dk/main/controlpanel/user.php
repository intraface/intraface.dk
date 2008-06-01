<?php
require('../../include_first.php');

$translation = $kernel->getTranslation('controlpanel');

$user = $kernel->user;
$value = $user->get();
$address_value = $user->getAddress()->get();

$page = new Intraface_Page($kernel);
$page->start($translation->get('user settings'));
?>

<h1><?php e(t('user settings')); ?></h1>

<ul class="options">
    <li><a href="user_edit.php"><?php e(t('edit', 'common')); ?></a></li>
    <li><a href="user_preferences.php"><?php e(t('preferences', 'controlpanel')); ?></a></li>
    <li><a href="user_change_password.php"><?php e(t('change password')); ?></a></li>
</ul>

<table class="vcard">
    <caption><?php e(t('information about user')); ?></caption>
    <tbody>
    <tr>
        <th><?php e(t('e-mail for login')); ?></th>
        <td><span class="email"><?php if (!empty($value['email'])) e($value['email']); ?></span></td>
    </tr>
</table>

<table class="vcard">
    <caption><?php e(t('user contact information for intranet').' '.$kernel->intranet->get('name')); ?></caption>
    <tbody>
    <tr>
        <th><?php e(t('name', 'address')); ?></th>
        <td class="fn"><?php if (!empty($address_value['name'])) e($address_value['name']); ?></td>
    </tr>
    <tr>
        <th><?php e(t('e-mail', 'address')); ?></th>
        <td><span class="email"><?php if (!empty($address_value['email'])) e($address_value['email']); ?></span></td>
    </tr>

    <tr>
        <th><?php e(t('address', 'address')); ?></th>
        <td class="street-address"><?php if (!empty($address_value['address'])) e($address_value['address']); ?></td>
    </tr>
    <tr>
        <th><?php e(t('postal code and city', 'address')); ?></th>
        <td><span class="postal-code"><?php if (!empty($address_value['postcode'])) e($address_value['postcode']); ?></span> <span class="locality"> <?php if (!empty($address_value['city'])) e($address_value['city']); ?></span></td>
    </tr>
    <tr>
        <th><?php e(t('country', 'address')); ?></th>
        <td class="country"><?php if (!empty($address_value['country'])) e($address_value['country']); ?></td>
    </tr>

    <tr>
        <th><?php e(t('phone', 'address')); ?></th>
        <td class="tel"><?php if (!empty($address_value['phone'])) e($address_value['phone']); ?></td>
    </tr>
    <tr>
        <th><?php e(t('website', 'address')); ?></th>
        <td class="url"><?php if (!empty($address_value['website'])) e($address_value['website']); ?></td>
    </tr>
    </tbody>
</table>

<?php
$page->end();
?>
