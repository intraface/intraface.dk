<?php
/**
 * keywords.php
 *
 * @author Lars Olesen <lars@legestue.net>
 */

require('../../include_first.php');

$kernel->useShared('keyword');
$translation = $kernel->getTranslation('keyword');

if (!empty($_REQUEST['product_id']) AND is_numeric($_REQUEST['product_id'])) {
    $object_name = 'Product';
    $module = $kernel->module('product');
    $id = (int)$_REQUEST['product_id'];
    $id_name = 'product_id';
    $redirect = 'product/product';
    $object = new $object_name($kernel, $id);

}
elseif (!empty($_REQUEST['contact_id']) AND is_numeric($_REQUEST['contact_id'])) {
    $object_name = 'Contact';
    $module = $kernel->module('contact');
    $id = (int)$_REQUEST['contact_id'];
    $id_name = 'contact_id';
    $redirect = 'contact/contact';
    $object = new $object_name($kernel, $id);

}
elseif (!empty($_REQUEST['page_id']) AND is_numeric($_REQUEST['page_id'])) {
    $object_name = 'CMS_Page';
    $module = $kernel->module('cms');
    $id = (int)$_REQUEST['page_id'];
    $id_name = 'page_id';
    $redirect = 'cms/page';
    $object = CMS_Page::factory($kernel, 'id', $id);

}
elseif (!empty($_REQUEST['template_id']) AND is_numeric($_REQUEST['template_id'])) {
    $object_name = 'CMS_template';
    $module = $kernel->module('cms');
    $id = (int)$_REQUEST['template_id'];
    $id_name = 'template_id';
    $redirect = 'cms/template';
    $object = CMS_Template::factory($kernel, 'id', $id);

}
elseif (!empty($_REQUEST['filemanager_id']) AND is_numeric($_REQUEST['filemanager_id'])) {
    $object_name = 'FileManager';
    $module = $kernel->module('filemanager');
    $id = (int)$_REQUEST['filemanager_id'];
    $id_name = 'filemanager_id';
    $redirect = 'filemanager/file';
    $object = new $object_name($kernel, $id);
}
else {
    trigger_error('Der er ikke angivet noget objekt i /shared/keyword/connect.php', E_USER_ERROR);
}

if (!empty($_POST)) {

    $keyword = $object->getKeywordAppender(); // starter keyword objektet

    if (!$keyword->deleteConnectedKeywords()) {
        $keyword->error->set('Kunne ikke slette keywords.');
    }

    // strengen med keywords
    if (!empty($_POST['keywords'])) {
        $appender = new Intraface_Keyword_StringAppender(new Keyword($object), $keyword);
        $appender->addKeywordsByString($_POST['keywords']);
    }

    // listen med keywords
    if (!empty($_POST['keyword']) AND is_array($_POST['keyword']) AND count($_POST['keyword']) > 0) {
        for($i=0, $max = count($_POST['keyword']); $i < $max; $i++) {
            $keyword->addKeyword(new Keyword($object, $_POST['keyword'][$i]));
        }
    }

    if (!empty($_POST['close'])) {
        header('Location: '.PATH_WWW.'/modules/'.$redirect.'.php?id='.$id.'&from=keywords#keywords');
        exit;
    }
      if (!$keyword->error->isError()) {
        //header('Location: connect.php?'.$id_name.'='.$object->get('id'));
        //exit;
    }
}

require_once 'Ilib/Redirect.php';
$options = array('extra_db_condition' => 'intranet_id = '.intval($kernel->intranet->get('id')));
$redirect = Ilib_Redirect::receive($kernel->getSessionId(), MDB2::singleton(DB_DSN), $options);
$redirect->setDestination(PATH_WWW . 'shared/keyword/edit.php', PATH_WWW . 'shared/keyword/connect.php?'.$id_name.'='.$object->get('id'));


if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
    $keyword = new Keyword($object, $_GET['delete']);
    $keyword->delete();
}



$keyword = $object->getKeywordAppender(); // starter objektet
$keywords = $keyword->getAllKeywords(); // henter alle keywords
$keyword_string = $keyword->getConnectedKeywordsAsString();

// finder dem der er valgt
$checked = array();
foreach($keyword->getConnectedKeywords() AS $key) {
    $checked[] = $key['id'];
}

$page = new Page($kernel);
$page->start(safeToHtml($translation->get('add keywords to') . ' ' . $object->get('name')));

?>
<h1><?php echo safeToHtml($translation->get('add keywords to') . ' ' . $object->get('name')); ?></h1>

<?php echo $keyword->error->view(); ?>

<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">
    <?php if (is_array($keywords) AND count($keywords) > 0): ?>
    <fieldset>
        <legend><?php echo safeToHtml($translation->get('choose keywords')); ?></legend>
        <input type="hidden" name="<?php echo $id_name; ?>" value="<?php echo $object->get('id'); ?>" />
        <?php
            $i = 0;
            foreach ($keywords AS $k) {
                print '<input type="checkbox" name="keyword[]" id="k'.$k['id'].'" value="'.$k['id'].'"';
                if (in_array($k['id'], $checked)) {
                    print ' checked="checked" ';
                }
                print ' />';
                print ' <label for="k'.$k["id"].'"><a href="edit.php?'. $id_name.'='.$object->get('id').'&amp;id='.$k['id'].'">' . safeToHtmL($k['keyword']) . ' (#'.$k["id"].')</a></label> - <a href="'.basename($_SERVER['PHP_SELF']).'?'. $id_name.'='.$object->get('id').'&amp;delete='.$k["id"].'" class="confirm">' .safeToHtml($translation->get('delete', 'common')). '</a><br />'. "\n";
        }
        ?>
    </fieldset>
        <div style="clear: both; margin-top: 1em; width:100%;">
            <input type="submit" value="<?php echo $translation->get('choose'); ?>" name="submit" class="save" /> <input type="submit" value="<?php echo safeToHtml($translation->get('choose and close')); ?>" name="close" class="save" />
        </div>

    <?php endif; ?>
    <fieldset>
        <legend><?php echo safeToHtml($translation->get('create keyword')); ?></legend>
        <p><?php echo safeToHtml($translation->get('separate keywords by comma')); ?></samp></p>
        <input type="hidden" name="<?php echo $id_name; ?>" value="<?php echo $object->get('id'); ?>" />
        <label for="keyword"><?php echo safeToHtml($translation->get('keywords')); ?></label>
        <input type="text" name="keywords" id="keyword" value="<?php // echo $keyword_string; ?>" />
        <input type="submit" value="<?php echo safeToHtml($translation->get('save', 'common')); ?>" name="submit" name="close" />
    </fieldset>
</form>



<?php
$page->end();
?>
