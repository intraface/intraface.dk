<h1><?php e(__('Import contacts')); ?></h1>

<?php // echo $contact->error->view(); ?>

<?php if (isset($success) && isset($errors)): ?>

    <fieldset>
        <legend><?php e(__('imported contacts')); ?></legend>

        <div><?php e(sprintf(__('%d records was imported successfully'), $success)); ?></div>
    </fieldset>

    <h3><?php e(__('errors')); ?></h3>

    <?php if (count($errors) == 0): ?>
        <div><?php e(__('lucky you - no errors in import')); ?></div>
    <?php else: ?>

        <?php foreach ($errors AS $error): ?>
            <div><?php e(sprintf(__('error in line %d. unable to import %s <%s>'), $error['line'], $error['name'], $error['email'])); ?></div>
            <?php echo $error['error']->view($translation); ?>
        <?php endforeach; ?>
    <?php endif; ?>

<?php else: ?>

    <form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">


    <fieldset>
        <legend><?php e(__('data')); ?></legend>

        <div><?php e(sprintf(__('there are %d records to import'), count($data))); ?></div>

    </fieldset>

    <fieldset>
        <legend><?php e(__('keywords')); ?></legend>

        <div class="formrow">
            <label for="keyword"><?php e(__('keywords')); ?></label>
            <input type="text" name="keyword" id="keyword" value="" /> <?php e(__('separated by comma')); ?>
        </div>
    </fieldset>

    <?php /* if ($kernel->user->hasModuleAccess('newsletter')): ?>
        <?php
        $module_newsletter = $kernel->useModule('newsletter');
        $newsletter_list = new NewsletterList($kernel);
        $list = $newsletter_list->getList();
        ?>
        <fieldset>
            <legend><?php e(__('newsletter')); ?></legend>

            <?php foreach ($list AS $newsletter): ?>
                <div id="formrow">
                    <label for="newsletter_<?php echo intval($newsletter['id']); ?>"><?php e($newsletter['title']); ?></label>
                    <input type="checkbox" name="newsletter[<?php echo intval($newsletter['id']); ?>]" id="newsletter_<?php echo intval($newsletter['id']); ?>" id="1" />
                </div>
            <?php endforeach; ?>
        </fieldset>
    <?php endif; */ ?>

    <input type="submit" class="save" name="submit" value="<?php e(__('import')); ?>" />
    <a href="<?php e(url('../')); ?>"><?php e(__('Cancel', 'common')); ?></a>

    </form>
<?php endif; ?>