<?php
/**
 * Det smarteste er at knytte et antal kontakter til de forskellige todo-lister,
 * og så er det dem, man kan vælge imellem. Spørgsmålet er bare hvordan det gøres
 * så det er lidt kvikt.
 *
 *
 */
require('../../include_first.php');

$kernel->module('todo');
$translation = $kernel->getTranslation('todo');
$kernel->useModule('contact');
$kernel->useShared('email');

if (!empty($_POST)) {

    $todo = new TodoList($kernel, $_POST['id']);

    $_POST['contact'] = array_merge($_POST['contact'], $_POST['new_contact']);

    foreach ($_POST['contact'] AS $key=>$value) {

        if (!empty($_POST['contact'][$key])) {

            $todo->addContact($_POST['contact'][$key]);

            $contact = new Contact($kernel, $_POST['contact'][$key]);

            $email = new Email($kernel);
            $var = array(
                'body' => $_POST['body'] . "\n\n" . $contact->getLoginUrl(),
                'subject' => $_POST['subject'],
                'contact_id' => $contact->get('id'),
                'type_id' => 6, // type_id 6 er todo
                'belong_to' => $todo->get('id')
            );


            if ($id = $email->save($var)) {
                $email->send();
                header('Location: index.php?id='.$todo->get('id'));
                exit;
            } else {
                header('Location: todo_email.php?id='. $todo->get('id'));
                exit;
            }
        }
    }


} elseif ($_GET['id']) {
    $todo = new TodoList($kernel, $_GET['id']);
    $value['id'] = $todo->get('id');
    $value['subject'] = 'Todoliste';
    $value['body'] = $kernel->setting->get('user','todo.email.standardtext') . "\n\nMed venlig hilsen\n".$kernel->user->getAddress()->get('name') . "\n" . $kernel->intranet->get('name');

    $contacts = $todo->getContacts();

    $contact = new Contact($kernel);
    $keywords = $contact->getKeywords();
    $contact->getDBQuery()->setKeyword('todo');
    $contact_list = $contact->getList();
}

$page = new Page($kernel);
$page->start('Todo E-mail');
?>
<h1>Hvem skal todo-listen sendes til?</h1>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

    <fieldset>
        <legend>Send til kontakter</legend>

        <?php foreach($contacts AS $contact):  ?>
            <?php
                $c = new Contact($kernel, $contact);
            ?>
            <label><input type="checkbox" value="<?php echo $contact['id']; ?>" name="contact[]" /> <?php echo $c->get('name'); ?></label>
        <?php endforeach; ?>

        <div class="formrow">
        <label>Tilføj en kontaktperson</label>
        <select name="new_contact[]">
        <option value="">Vælg</option>
        <?php foreach($contact_list AS $contact_item): ?>
            <option value="<?php echo $contact_item['id']; ?>"><?php echo $contact_item['name']; ?></option>
        <?php endforeach; ?>
        </div>
        </select>

    </fieldset>

    <fieldset>

        <legend>Indhold i e-mailen</legend>

        <div class="formrow">
            <label for="">Titel</label>
            <input type="text" name="subject" value="<?php echo $value['subject']; ?>" />
        </div>

        <div class="formrow">
            <label for="">Tekst</label>
            <textarea cols="80" rows="6" name="body"><?php echo $value['body']; ?></textarea>
        </div>

        <div>
            <input type="submit" value="Gem" />
            <input type="hidden" value="<?php echo $value['id']; ?>" name="id" />

        </div>

    </fieldset>

</form>

<?php $page->end(); ?>
