
<h1>Hvem skal todo-listen sendes til?</h1>

<form action="<?php e(url()); ?>" method="post">

    <fieldset>
        <legend>Send til kontakter</legend>

        <?php foreach ($contacts AS $contact):  ?>
            <?php
                $c = new Contact($kernel, $contact);
            ?>
            <label><input type="checkbox" value="<?php e($contact['id']); ?>" name="contact[]" /> <?php e($c->get('name')); ?></label>
        <?php endforeach; ?>

        <div class="formrow">
        <label>Tilføj en kontaktperson</label>
        <select name="new_contact[]">
        <option value="">Vælg</option>
        <?php foreach ($contact_list AS $contact_item): ?>
            <option value="<?php e($contact_item['id']); ?>"><?php e($contact_item['name']); ?></option>
        <?php endforeach; ?>
        </select>
        </div>


    </fieldset>

    <fieldset>

        <legend>Indhold i e-mailen</legend>

        <div class="formrow">
            <label for="">Titel</label>
            <input type="text" name="subject" value="<?php e($value['subject']); ?>" />
        </div>

        <div class="formrow">
            <label for="">Tekst</label>
            <textarea cols="80" rows="6" name="body"><?php e($value['body']); ?></textarea>
        </div>

        <div>
            <input type="submit" value="Gem" />
            <input type="hidden" value="<?php e($value['id']); ?>" name="id" />

        </div>

    </fieldset>

</form>
