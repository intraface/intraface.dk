<?php
require('../../include_first.php');

$cms_module = $kernel->module('cms');
$translation = $kernel->getTranslation('cms');

if (!empty($_POST['page'])) {

    foreach ($_POST['page'] AS $key=>$value) {

        $cmssite = new CMS_Site($kernel, $_POST['id']);
        $cmspage = new CMS_Page($cmssite, $_POST['page'][$key]);
        if ($cmspage->setStatus($_POST['status'][$key])) {
        }

    }


    if (isAjax()) {
        echo 1;
        exit;
    }
    else {
        header('Location: site.php?id='.$cmssite->get('id'));
        exit;
    }

}


if (!empty($_GET['moveup']) AND is_numeric($_GET['moveup'])) {
    $cmspage = CMS_Page::factory($kernel, 'id', $_GET['moveup']);
    $cmspage->getPosition(MDB2::singleton(DB_DSN))->moveUp();
    $cmssite = $cmspage->cmssite;
    $type = $cmspage->get('type');
} elseif (!empty($_GET['movedown']) AND is_numeric($_GET['movedown'])) {
    $cmspage = CMS_Page::factory($kernel, 'id', $_GET['movedown']);
    $cmspage->getPosition(MDB2::singleton(DB_DSN))->moveDown();
    $cmssite = $cmspage->cmssite;
    $type = $cmspage->get('type');
} elseif (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
    $cmspage = CMS_Page::factory($kernel, 'id', $_GET['delete']);
    $cmspage->delete();
    $cmssite = $cmspage->cmssite;
    $type = $cmspage->get('type');
} else {
    if(empty($_GET['type']) || !in_array($_GET['type'], CMS_Page::getTypes())) {
        trigger_error('A valid type of page is needed', E_USER_ERROR);
    } else {
        $type = $_GET['type'];
    }

    if(!empty($_GET['id'])) {
        $cmssite = new CMS_Site($kernel, (int)$_GET['id']);
        $cmspage = new CMS_Page($cmssite);
        $kernel->setting->set('user', 'cms.active.site_id', (int)$_GET['id']);
    }
    else {
        $site_id = $kernel->setting->get('user', 'cms.active.site_id');
        if($site_id != 0) {
            $cmssite = new CMS_Site($kernel, $site_id);
            $cmspage = new CMS_Page($cmssite);
        }
        else {
            header('location: index.php');
            exit;
        }
    }

}

$page_types_plural = CMS_Page::getTypesPlural();

$page = new Intraface_Page($kernel);
$page->includeJavascript('global', 'yui/connection/connection-min.js');
$page->includeJavascript('global', 'checkboxes.js');
$page->includeJavascript('module', 'publish.js');
$page->start(safeToHtml($translation->get($page_types_plural[$type])));

?>

<h1><?php e($translation->get($page_types_plural[$type])); ?> <?php e($translation->get('on', 'common')); ?> <?php echo $cmssite->get('name'); ?></h1>

<?php if (count($cmspage->getTemplate()->getList()) == 0): ?>

    <p class="message-dependent">
        <?php e($translation->get('you have to create a template')); ?>
        <?php if ($kernel->user->hasSubAccess('cms', 'edit_templates')): ?>
            <a href="template_edit.php?site_id=<?php echo $cmssite->get('id'); ?>"><?php e($translation->get('create template')); ?></a>.
        <?php else: ?>
            <strong><?php e($translation->get('you cannot create templates')); ?></strong>
        <?php endif; ?>
    </p>

<?php else: ?>

<ul class="options">
    <?php foreach($cmspage->getTypes() AS $page_type): ?>
        <li>
            <?php if($page_type == $type): ?>
                <strong><?php e(t($page_types_plural[$page_type])); ?></strong>
            <?php else: ?>
                <a  href="pages.php?type=<?php e($page_type); ?>&amp;id=<?php echo $cmssite->get('id'); ?>"><?php e(t($page_types_plural[$page_type])); ?></a>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>

<ul class="options">
    <li><a class="new" href="page_edit.php?type=<?php e($type); ?>&amp;site_id=<?php echo $cmssite->get('id'); ?>"><?php e($translation->get('create '.$type)); ?></a></li>
    <li><a  href="site.php?id=<?php echo $cmssite->get('id'); ?>"><?php e($translation->get('go to site overview')); ?></a></li>
</ul>


<form id="form-site" action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">
<input type="hidden" id="site" name="id" value="<?php echo $cmssite->get('id'); ?>" />
<input type="hidden" id="type" name="type" value="<?php echo $type; ?>" />

<?php if($type == 'page'): ?>
    <?php
    $cmspage = new CMS_Page($cmssite);
    $cmspage->getDBQuery()->setFilter('type', 'page');
    $cmspage->getDBQuery()->setFilter('level', 'alllevels');
    $pages = $cmspage->getList('page', 'alllevels');

    if (!is_array($pages) OR count($pages) == 0):
        echo '<p>'.safeToHtml($translation->get('no pages found')).'</p>';
    else: ?>
        <table>
            <caption><?php e($translation->get('pages')); ?></caption>
            <thead>
                <tr>
                    <th><?php e($translation->get('navigation name')); ?></th>
                    <th><?php e($translation->get('unique page address')); ?></th>
                    <th><?php e($translation->get('published', 'common')); ?></th>
                    <th><?php e($translation->get('show', 'common')); ?></th>
                    <th colspan="4"></th>
                </tr>
            </thead>
            <?php foreach ($pages AS $p):?>
                <tr>
                    <td><a href="page.php?id=<?php echo $p['id']; ?>"><?php e(str_repeat("- ", $p['level']) . $p['navigation_name']); ?></a></td>
                    <td><?php e($p['identifier']); ?></td>
                    <td>
                        <input type="hidden" name="page[<?php echo $p['id']; ?>]" value="<?php echo $p['id']; ?>" />
                        <input class="input-publish" id="<?php echo $p['id']; ?>" type="checkbox" name="status[<?php echo $p['id']; ?>]" value="published" <?php if ($p['status'] == 'published') echo ' checked="checked"'; ?> />
                    </td>
                    <td>
                        <?php if ($p['status'] == 'published'): // hack siden kan kun vises, hvis den er udgivet. Der bør laves et eller andet, så det er muligt anyways - fx en hemmelig kode på siden ?>
                            <a href="<?php echo $p['url']; ?>" target="_blank"><?php e($translation->get('show page')); ?></a>
                        <?php endif; ?>
                    </td>
                    <td class="options">
                        <a class="moveup" href="<?php e(basename($_SERVER['PHP_SELF'])); ?>?id=<?php e($cmssite->get("id")); ?>&amp;moveup=<?php e($p['id']); ?>&amp;type=<?php e($type); ?>"><?php e($translation->get('up', 'common')); ?></a>
                        <a class="moveup" href="<?php e(basename($_SERVER['PHP_SELF'])); ?>?id=<?php e($cmssite->get("id")); ?>&amp;movedown=<?php e($p['id']); ?>&amp;type=<?php e($type); ?>"><?php e($translation->get('down', 'common')); ?></a>
                        <a class="edit" href="page_edit.php?id=<?php e($p['id']); ?>"><?php e($translation->get('edit settings', 'common')); ?></a>
                        <a class="delete" href="<?php echo basename($_SERVER['PHP_SELF']); ?>?delete=<?php e($p['id']); ?>&amp;type=<?php e($type); ?>"><?php e($translation->get('delete', 'common')); ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
<?php elseif($type == 'article'): ?>
    <?php
    $cmsarticles = new CMS_Page($cmssite);
    $cmsarticles->getDBQuery()->setFilter('type', 'article');
    $articles = $cmsarticles->getList();
    if (!is_array($articles) OR count($articles) == 0):
        echo '<p>'.safeToHtml($translation->get('no articles found')).'</p>';
    else: ?>
        <table>
            <caption><?php e($translation->get('articles')); ?></caption>
            <thead>
                <tr>
                    <th><?php e($translation->get('title')); ?></th>
                    <th><?php e($translation->get('unique page address')); ?></th>
                    <th><?php e($translation->get('published', 'common')); ?></th>
                    <th><?php e($translation->get('show', 'common')); ?></th>
                    <th colspan="2"></th>
                </tr>
            </thead>
            <?php foreach ($articles AS $p):?>
                <tr>
                    <td><a href="page.php?id=<?php echo $p['id']; ?>"><?php e($p['title']); ?></a></td>
                    <td><?php e($p['identifier']); ?></td>
                    <td>
                        <input type="hidden" name="page[<?php echo $p['id']; ?>]" value="<?php echo $p['id']; ?>" />
                        <input class="input-publish" id="<?php echo $p['id']; ?>" type="checkbox" name="status[<?php echo $p['id']; ?>]" value="published" <?php if ($p['status'] == 'published') echo ' checked="checked"'; ?> />
                    </td>
                    <td>
                    <?php if ($p['status'] == 'published'): // hack siden kan kun vises, hvis den er udgivet. Der bør laves et eller andet, så det er muligt anyways - fx en hemmelig kode på siden ?>
                        <a href="<?php echo $p['url']; ?>" target="_blank"><?php e($translation->get('show page', 'common')); ?></a>
                    <?php endif; ?>
                    </td>
                    <td class="options"><a class="edit" href="page_edit.php?id=<?php echo $p['id']; ?>"><?php e($translation->get('edit settings', 'common')); ?></a>
                    <a class="delete" href="<?php echo basename($_SERVER['PHP_SELF']); ?>?delete=<?php echo $p['id']; ?>"><?php e($translation->get('delete', 'common')); ?></a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
<?php elseif($type == 'news'): ?>
    <?php
    $cmsnews = new CMS_Page($cmssite);
    $cmsnews->getDBQuery()->setFilter('type', 'news');
    $news = $cmsnews->getList();
    if (!is_array($news) OR count($news) == 0):
        echo '<p>'.safeToHtml($translation->get('no news found')).'</p>';
    else: ?>
        <table>
            <caption><?php e($translation->get('news')); ?></caption>
            <thead>
                <tr>
                    <th><?php e($translation->get('date', 'common')); ?></th>
                    <th><?php e($translation->get('title')); ?></th>
                    <th><?php e($translation->get('unique page address')); ?></th>
                    <th><?php e($translation->get('published', 'common')); ?></th>
                    <th><?php e($translation->get('show', 'common')); ?></th>
                    <th colspan="2"></th>
                </tr>
            </thead>
            <?php foreach ($news AS $p):?>
                <tr>
                    <td><?php e($p['date_publish_dk']); ?></td>
                    <td><a href="page.php?id=<?php echo $p['id']; ?>"><?php e($p['title']); ?></a></td>
                    <td><?php e($p['identifier']); ?></td>
                    <td>
                        <input type="hidden" name="page[<?php echo $p['id']; ?>]" value="<?php echo $p['id']; ?>" />
                        <input class="input-publish" id="<?php echo $p['id']; ?>" type="checkbox" name="status[<?php echo $p['id']; ?>]" value="published" <?php if ($p['status'] == 'published') echo ' checked="checked"'; ?> />
                    </td>
                    <td>
                        <?php if ($p['status'] == 'published'): // hack siden kan kun vises, hvis den er udgivet. Der bør laves et eller andet, så det er muligt anyways - fx en hemmelig kode på siden ?>
                            <a href="<?php echo $p['url']; ?>" target="_blank"><?php e($translation->get('show page')); ?></a>
                        <?php endif; ?>
                    </td>

                    <td class="options"><a class="edit" href="page_edit.php?id=<?php echo $p['id']; ?>"><?php e($translation->get('edit settings', 'common')); ?></a>
                        <a class="delete" href="<?php echo basename($_SERVER['PHP_SELF']); ?>?delete=<?php echo $p['id']; ?>"><?php e($translation->get('delete', 'common')); ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
<?php endif; ?>

<p><input type="submit" value="<?php e($translation->get('save', 'common')); ?>" id="submit-publish" /></p>
<?php endif; ?>

</form>


<?php
$page->end();
?>