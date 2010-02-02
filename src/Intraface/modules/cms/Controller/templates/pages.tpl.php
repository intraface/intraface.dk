<h1><?php e(t($page_types_plural[$type])); ?> <?php e(t('on')); ?> "<?php e($cmssite->get('name')); ?>"</h1>

<?php if (count($cmspage->getTemplate()->getList()) == 0): ?>

    <p class="message-dependent">
        <?php e(t('you have to create a template')); ?>
        <?php if ($kernel->user->hasSubAccess('cms', 'edit_templates')): ?>
            <a href="<?php e(url('../template/create')); ?>"><?php e(t('create template')); ?></a>.
        <?php else: ?>
            <strong><?php e(t('you cannot create templates')); ?></strong>
        <?php endif; ?>
    </p>

<?php else: ?>

<ul class="options">
    <?php foreach ($cmspage->getTypes() AS $page_type): ?>
        <li>
            <?php if ($page_type == $type): ?>
                <strong><?php e(t($page_types_plural[$page_type])); ?></strong>
            <?php else: ?>
                <a  href="<?php e(url(null, array('type' => $page_type))); ?>"><?php e(t($page_types_plural[$page_type])); ?></a>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>

<ul class="options">
    <li><a class="new" href="<?php e(url('create', array('type' => $type)));?>"><?php e(t('create '.$type)); ?></a></li>
    <li><a  href="<?php e(url('../')); ?>"><?php e(t('go to site overview')); ?></a></li>
</ul>


<form id="form-site" action="<?php e(url()); ?>" method="post">
<input type="hidden" id="site" name="id" value="<?php e($cmssite->get('id')); ?>" />
<input type="hidden" id="type" name="type" value="<?php e($type); ?>" />

<?php if ($type == 'page'): ?>
    <?php
    $cmspage = new CMS_Page($cmssite);
    $cmspage->getDBQuery()->setFilter('type', 'page');
    $cmspage->getDBQuery()->setFilter('level', 'alllevels');
    $pages = $cmspage->getList('page', 'alllevels');

    if (!is_array($pages) OR count($pages) == 0): ?>
        <p><?php e(t('no pages found')); ?></p>
    <?php else: ?>
        <table>
            <caption><?php e(t('pages')); ?></caption>
            <thead>
                <tr>
                    <th><?php e(t('navigation name')); ?></th>
                    <th><?php e(t('unique page address')); ?></th>
                    <th><?php e(t('published')); ?></th>
                    <th><?php e(t('show')); ?></th>
                    <th colspan="4"></th>
                </tr>
            </thead>
            <?php foreach ($pages AS $p):?>
                <tr>
                    <td><a href="<?php e(url($p['id'])); ?>"><?php e(str_repeat("- ", $p['level']) . $p['navigation_name']); ?></a></td>
                    <td><?php e($p['identifier']); ?></td>
                    <td>
                        <input type="hidden" name="page[<?php e($p['id']); ?>]" value="<?php e($p['id']); ?>" />
                        <input class="input-publish" id="<?php e($p['id']); ?>" type="checkbox" name="status[<?php e($p['id']); ?>]" value="published" <?php if ($p['status'] == 'published') echo ' checked="checked"'; ?> />
                    </td>
                    <td>
                        <?php if ($p['status'] == 'published'): // hack siden kan kun vises, hvis den er udgivet. Der b�r laves et eller andet, s� det er muligt anyways - fx en hemmelig kode p� siden ?>
                            <a href="<?php e($p['url']); ?>" target="_blank"><?php e(t('show page')); ?></a>
                        <?php endif; ?>
                    </td>
                    <td class="options">
                        <a class="moveup" href="<?php e(url(null, array('moveup' => $p['id'], 'type' => $type))); ?>"><?php e(t('up')); ?></a>
                        <a class="moveup" href="<?php e(url(null, array('movedown' => $p['id'], 'type' => $type))); ?>"><?php e(t('down')); ?></a>
                        <a class="edit" href="<?php e(url($p['id']. '/edit')); ?>"><?php e(t('edit settings')); ?></a>
                        <a class="delete" href="<?php e(url(null, array('delete' => $p['id'], 'type' => $type))); ?>"><?php e(t('delete')); ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
<?php elseif ($type == 'article'): ?>
    <?php
    $cmsarticles = new CMS_Page($cmssite);
    $cmsarticles->getDBQuery()->setFilter('type', 'article');
    $articles = $cmsarticles->getList();
    if (!is_array($articles) OR count($articles) == 0): ?>
        <p><?php e(t('no articles found')); ?></p>
    <?php else: ?>
        <table>
            <caption><?php e(t('articles')); ?></caption>
            <thead>
                <tr>
                    <th><?php e(t('title')); ?></th>
                    <th><?php e(t('unique page address')); ?></th>
                    <th><?php e(t('published')); ?></th>
                    <th><?php e(t('show')); ?></th>
                    <th colspan="2"></th>
                </tr>
            </thead>
            <?php foreach ($articles AS $p):?>
                <tr>
                    <td><a href="<?php e(url($p['id'])); ?>"><?php e($p['title']); ?></a></td>
                    <td><?php e($p['identifier']); ?></td>
                    <td>
                        <input type="hidden" name="page[<?php e($p['id']); ?>]" value="<?php e($p['id']); ?>" />
                        <input class="input-publish" id="<?php e($p['id']); ?>" type="checkbox" name="status[<?php e($p['id']); ?>]" value="published" <?php if ($p['status'] == 'published') echo ' checked="checked"'; ?> />
                    </td>
                    <td>
                    <?php if ($p['status'] == 'published'): // hack siden kan kun vises, hvis den er udgivet. Der b�r laves et eller andet, s� det er muligt anyways - fx en hemmelig kode p� siden ?>
                        <a href="<?php e($p['url']); ?>" target="_blank"><?php e(t('show page')); ?></a>
                    <?php endif; ?>
                    </td>
                    <td class="options"><a class="edit" href="<?php e(url($p['id'].'/edit')); ?>"><?php e(t('edit settings')); ?></a>
                    <a class="delete" href="<?php e(url(null, array('delete' => $p['id'], 'type' => $type))); ?>"><?php e(t('delete')); ?></a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
<?php elseif ($type == 'news'): ?>
    <?php
    $cmsnews = new CMS_Page($cmssite);
    $cmsnews->getDBQuery()->setFilter('type', 'news');
    $news = $cmsnews->getList();
    if (!is_array($news) OR count($news) == 0): ?>
        <p><?php e(t('no news found')); ?></p>
    <?php else: ?>
        <table>
            <caption><?php e(t('news')); ?></caption>
            <thead>
                <tr>
                    <th><?php e(t('date')); ?></th>
                    <th><?php e(t('title')); ?></th>
                    <th><?php e(t('unique page address')); ?></th>
                    <th><?php e(t('published')); ?></th>
                    <th><?php e(t('show')); ?></th>
                    <th colspan="2"></th>
                </tr>
            </thead>
            <?php foreach ($news AS $p):?>
                <tr>
                    <td><?php e($p['date_publish_dk']); ?></td>
                    <td><a href="<?php e(url($p['id'])); ?>"><?php e($p['title']); ?></a></td>
                    <td><?php e($p['identifier']); ?></td>
                    <td>
                        <input type="hidden" name="page[<?php e($p['id']); ?>]" value="<?php e($p['id']); ?>" />
                        <input class="input-publish" id="<?php e($p['id']); ?>" type="checkbox" name="status[<?php e($p['id']); ?>]" value="published" <?php if ($p['status'] == 'published') echo ' checked="checked"'; ?> />
                    </td>
                    <td>
                        <?php if ($p['status'] == 'published'): // hack siden kan kun vises, hvis den er udgivet. Der b�r laves et eller andet, s� det er muligt anyways - fx en hemmelig kode p� siden ?>
                            <a href="<?php e($p['url']); ?>" target="_blank"><?php e(t('show page')); ?></a>
                        <?php endif; ?>
                    </td>

                    <td class="options"><a class="edit" href="<?php e(url($p['id'] . '/edit')); ?>"><?php e(t('edit settings')); ?></a>
                        <a class="delete" href="<?php e(url(null, array('delete' => $p['id']))); ?>"><?php e(t('delete')); ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
<?php endif; ?>

<p><input type="submit" value="<?php e(t('save')); ?>" id="submit-publish" /></p>
<?php endif; ?>

</form>