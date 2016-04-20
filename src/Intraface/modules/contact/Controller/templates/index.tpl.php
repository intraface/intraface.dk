<h1><?php e(t('Contacts')); ?></h1>

<ul class="options">
    <li><a class="new" href="<?php e(url(null, array('create'))); ?>"><?php e(t('Create contact')); ?></a></li>
    <?php if ($context->getKernel()->getSetting()->get('user', 'contact.search') == 'hide' and count($context->getContacts()) > 0) : ?>
    <li><a href="<?php e(url(null, array('search' => 'view'))); ?>"><?php e(t('show search')); ?></a></li>
    <?php endif; ?>
    <li><a class="pdf" href="<?php e(url(null . '.pdf', array('use_stored' => 'true'))); ?>" target="_blank"><?php e(t('Pdf-labels')); ?></a></li>
    <li><a class="excel" href="<?php e(url(null . '.xls', array('use_stored' => 'true'))); ?>"><?php e(t('Excel')); ?></a></li>
    <li><a href="<?php e(url('sendemail', array('use_stored' => true))); ?>"><?php e(t('Email to contacts in search')); ?></a></li>
    <li><a href="<?php e(url('import/file')); ?>"><?php e(t('Import contacts')); ?></a></li>
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>

</ul>

<?php if ($context->query('flare')) : ?>
    <p class="message"><?php e($context->query('flare')); ?></p>
<?php endif; ?>

<?php if (!$context->getContact()->isFilledIn()) : ?>

    <p><?php e(t('No contacts has been created')); ?>. <a href="<?php e(url(null, array('create'))); ?>"><?php e(t('Create contact')); ?></a>.</p>

<?php else : ?>


<?php if ($context->getKernel()->getSetting()->get('user', 'contact.search') == 'view') : ?>

<form action="<?php e(url()); ?>" method="get" class="search-filter">
    <fieldset>
        <legend><?php e(t('search')); ?></legend>

        <label for="query"><?php e(t('search for')); ?>
            <input name="query" id="query" type="text" value="<?php e($context->getContact()->getDBQuery()->getFilter('search')); ?>" />
        </label>

        <?php if (count($context->getUsedKeywords())) : ?>
        <label for="keyword_id"><?php e(t('show with keywords')); ?>
            <select name="keyword_id" id="keyword_id">
                <option value=""><?php e(t('All')); ?></option>
                <?php foreach ($context->getUsedKeywords() as $k) { ?>
                    <option value="<?php e($k['id']); ?>" <?php if ($k['id'] == $context->getContact()->getDBQuery()->getKeyword(0)) {
                        echo ' selected="selected"';
}; ?>><?php e($k['keyword']); ?></option>
                <?php } ?>
            </select>
        </label>
        <?php endif; ?>
        <span><input type="submit" value="<?php e(t('go')); ?>" /></span>
        <!-- <a href="<?php e(url(null, array('search' => 'hide'))); ?>"><?php e(t('Hide search')); ?></a>  -->
    </fieldset>
</form>

<?php endif; ?>

<?php echo $context->getContact()->getDBQuery()->display('character'); ?>

<form action="<?php e(url()); ?>" method="post">
    <input type="hidden" value="put" name="_method" />
    <?php if (!empty($deleted)) : ?>
        <p class="message">Du har slettet kontakter. <input type="hidden" name="deleted" value="<?php echo base64_encode(serialize($deleted)); ?>" /> <input name="undelete" type="submit" value="Fortryd" /></p>
    <?php endif; ?>

    <table summary="<?php e(t('Contacts')); ?>" class="stripe">
        <caption><?php e(t('Contacts')); ?></caption>
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th><?php e(t('Number')); ?></th>
                <th><?php e(t('Name')); ?></th>
                <th><?php e(t('Phone')); ?></th>
                <th><?php e(t('Email')); ?></th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="5"><?php echo $context->getContact()->getDBQuery()->display('paging'); ?></td>
            </tr>
        </tfoot>
        <tbody>
            <?php foreach ($contacts as $c) { ?>
            <tr class="vcard">

                <td>
                    <input type="checkbox" value="<?php e($c['id']); ?>" name="selected[]" />
                </td>
                <td><?php e($c['number']); ?></td>
                <td><img style="border: none; vertical-align:middle" src="<?php e('http://www.gravatar.com/avatar/'.md5($c['email']).'?s=20&d=&d='.NET_SCHEME . NET_HOST . url('/images/icons/gravatar.png')); ?>" height="20" width="20" /> <a class="fn" href="<?php e(url($c['id'])); ?>"><?php e($c['name']); ?></a></td>
                <td class="tel"><?php e($c['phone']); ?></td>
                <td class="email"><?php e($c['email']); ?></td>
                <td class="options">
                    <a class="edit" href="<?php e(url($c['id'], array('edit'))); ?>"><?php e(t('edit')); ?></a>
                    <?php /*
					<a class="delete" href="index.php?delete=<?php e($c['id']); ?>&amp;use_stored=true"><?php e(t('delete')); ?></a> */ ?>
                </td>
            </tr>
            <?php } // end foreach ?>
        </tbody>
    </table>

    <select name="action">
        <option value=""><?php e(t('Choose')); ?></option>
        <option value="delete"><?php e(t('Delete')); ?></option>
    </select>

    <input type="submit" value="<?php e(t('Execute')); ?>" />

</form>

<?php echo $context->getContact()->getDBQuery()->display('paging'); ?>

<?php endif; ?>