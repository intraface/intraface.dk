<h1><?php e(t('Subscribe users')); ?></h1>

    <ul class="options">
        <li><a href="<?php e(url('../', array('use_stored' => true))); ?>"><?php e(t('Close')); ?></a></li>
    </ul>

<?php if ($context->getMessage()) : ?>

<p><?php echo $context->getMessage(); ?></p>

<?php else : ?>

    <?php echo $contact->error->view(); ?>

<p class="message">Du er ved at tilmelde <?php e(count($contacts)); ?> kontakter til nyhedsbrevet.</p>

<form action="<?php e(url()); ?>" method="post">
    <fieldset>
    <legend><?php e(t('Newsletters')); ?></legend>
    <label><?php e(t('Newsletter')); ?>
        <select name="newsletter_id">
            <option><?php e(t('None')); ?></option>
            <?php foreach ($newsletters as $newsletter) : ?>
                <option value="<?php e($newsletter['id']); ?>"><?php e($newsletter['title']); ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    </fieldset>
    <fieldset>
    <div>
        <input type="submit" name="submit" value="<?php e(t('Subscribe contacts')); ?>" class="save" />
        <a href="<?php e(url('../', array('use_stored' => 'true'))); ?>"><?php e(t('Cancel')); ?></a>
    </div>
    </fieldset>
</form>
<?php endif; ?>
