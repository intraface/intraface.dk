<?php
require('../../include_first.php');
$module = $kernel->module('newsletter');

if(!$kernel->user->hasModuleAccess('contact')) {
    trigger_error("Du skal have adgang til kontakt-modullet for at se denne side");
}

$list = new NewsletterList($kernel, (int)$_GET['list_id']);
$subscriber = new NewsletterSubscriber($list);


if(isset($_GET['add_contact']) && $_GET['add_contact'] == 1) {
    if($kernel->user->hasModuleAccess('contact')) {
        $contact_module = $kernel->useModule('contact');

        $redirect = Redirect::factory($kernel, 'go');
        $url = $redirect->setDestination($contact_module->getPath()."select_contact.php", $module->getPath()."subscribers.php?list_id=".$list->get('id'));
        $redirect->askParameter('contact_id');
        $redirect->setIdentifier('contact');

        header("Location: ".$url);
        exit;
    } else {
        trigger_error("Du har ikke adgang til modulet contact", ERROR);
    }

}

if(isset($_GET['return_redirect_id'])) {
    $redirect = Redirect::factory($kernel, 'return');
    if($redirect->get('identifier') == 'contact') {
        $subscriber->addContact(new Contact($kernel, $redirect->getParameter('contact_id')));
    }

}
//
if (isset($_GET['delete']) AND intval($_GET['delete']) != 0) {

    $subscriber = new NewsletterSubscriber($list, $_GET['delete']);
    $subscriber->delete();
}
// HACK - Denne bliver her sat uden at de skal være opted in i modsætning til constructor
$subscriber->dbquery = new DBQuery($list->kernel, "newsletter_subscriber", "newsletter_subscriber.list_id=". $list->get("id") . " AND newsletter_subscriber.intranet_id = " . $list->kernel->intranet->get('id') . " AND newsletter_subscriber.active = 1");

$subscriber->dbquery->useCharacter();
$subscriber->dbquery->defineCharacter('character', 'newsletter_subscriber.id');
$subscriber->dbquery->usePaging('paging');
$subscriber->dbquery->setExtraUri('&amp;list_id='.$list->get('id'));


$subscribers = $subscriber->getList();


$page = new Page($kernel);
$page->start('Modtagere');
?>

<h1>Modtagere på listen <?php print($list->get('title')); ?></h1>

<ul class="options">

    <li><a href="subscribers.php?list_id=<?php echo $list->get('id'); ?>&amp;add_contact=1">Tilføj kontakt</a></li>
    <li><a href="list.php?id=<?php echo $list->get('id'); ?>">Luk</a></li>

</ul>

<?php echo $subscriber->error->view(); ?>

<?php if (count($subscribers) == 0): ?>
    <p>Der er ikke tilføjet nogen modtager endnu.</p>
<?php else: ?>

    <?php echo $subscriber->dbquery->display('character'); ?>
<table class="stripe">
    <caption>Breve</caption>
    <thead>
    <tr>
        <th>Navn</th>
        <th>E-mail</th>
        <th>Tilmeldt</th>
        <th>Optin</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($subscribers AS $s): ?>
    <tr>
        <td><?php echo safeToHtml($s['contact_name']); ?></td>
        <td><?php echo safeToHtml($s['contact_email']); ?></td>
        <td><?php echo safeToHtml($s['dk_date_submitted']); ?></td>
        <td><?php echo safeToHtml($s['optin']); ?></td>
        <td>
            <a class="delete" href="subscribers.php?delete=<?php echo intval($s['id']); ?>&amp;list_id=<?php echo $list->get('id'); ?>" title="Dette sletter nyhedsbrevet">Slet</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

    <?php echo $subscriber->dbquery->display('paging'); ?></td>
<?php endif; ?>

<?php
$page->end();
?>