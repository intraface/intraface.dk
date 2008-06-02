<?php
require '../../include_first.php';

$translation = $kernel->getTranslation('controlpanel');

// hvordan løser vi lige det med temaerne. Det er lavet ret dumt i øjeblikket.

// indstillinger. Hvor skal disse standardindstillinger beskrives henne?
/*
$textsizes = array(
    'small' => 'Small',
    'medium' => 'Medium',
    'large' => 'Large',
    'xlarge' => 'Xtra Large'
);
*/

$error = new Intraface_Error();

/*
if ($kernel->user->hasModuleAccess('cms')) {
    $cms_module = $kernel->useModule('cms');
    //$editors = $cms_module->getSetting('htmleditors');
}
*/

$labels_standard = array(
    0 => '3x7',
    1 => '2x8'
);

if(!empty($_POST)) {
    /*
    if (!$kernel->setting->set('user', 'rows_pr_page', $_POST['rows_pr_page'])) {
        $error[] = 'rows_pr_page';
    }
    if (!$kernel->setting->set('user', 'theme', $_POST['theme'])) {
        $error[] = 'theme';
    }
    if (!$kernel->setting->set('user', 'ptextsize', $_POST['ptextsize'])) {
        $error[] = 'theme';
    }
    */


    if (isset($_POST['label']) AND !isset($labels_standard[$_POST['label']])) {
        $error->set('error in label - not allowed');
    }

    if (!$kernel->setting->set('user', 'label', $_POST['label'])) {
        $error->set('error in label');
    }

    if (!empty($_POST['language']) AND !array_key_exists($_POST['language'], $translation->getLangs())) {
        $error->set('error in language - not allowed');
    }

    if (!$kernel->setting->set('user', 'language', $_POST['language'])) {
        $error->set('error in language');
    }

    /*
    if ($kernel->user->hasModuleAccess('cms')) {
        $validator = new Intraface_Validator($error);
        $validator->isString($_POST['htmleditor'], 'error in htmleditor not a string', '');

        if (!array_key_exists($_POST['htmleditor'], $editors)) {
            $error->set('error in htmleditor not allowed');
        }
        if (!$kernel->setting->set('user', 'htmleditor', $_POST['htmleditor'])) {
            $error->set('error in htmleditor not saved');
        }
    }
    */

    if (!$error->isError()) {
        header('Location: user.php');
        exit;
    }
    $value = $_POST;

} else {
    /*
    $value['rows_pr_page'] = $kernel->setting->get('user', 'rows_pr_page');
    $value['theme'] = $kernel->setting->get('user', 'theme');
    $value['ptextsize'] = $kernel->setting->get('user', 'ptextsize');
    */
    $value['label'] = $kernel->setting->get('user', 'label');
    $value['language'] = $kernel->setting->get('user', 'language');
    //$value['htmleditor'] = $kernel->setting->get('user', 'htmleditor');
}

$page = new Intraface_Page($kernel);
$page->start(t('user preferences'));
?>

<h1><?php e(t('user preferences')); ?></h1>

<ul class="options">
    <li><a href="index.php"><?php e(t('close', 'common')); ?></a></li>
    <li><a href="user.php"><?php e(t('user')); ?></a></li>
</ul>

<form action="<?php e(basename($_SERVER['PHP_SELF'])); ?>" method="post">

    <?php echo $error->view($translation); ?>

    <?php
    /*
    <fieldset id="ptheme" class="radiobuttons">
        <legend>Tema</legend>
      <?php  foreach(themes() AS $key=>$v): ?>
           <label for="<?php echo $key; ?>" <?php if ($value['theme'] == $key) echo ' class="selected"'; ?>>
                <input type="radio" id="<?php echo $key; ?>" name="theme" value="<?php echo $key; ?>" <?php if ($value['theme'] == $key) echo ' checked="checked"'; ?> />
                <?php echo $v['name']; ?>. <span><?php echo $v['description']; ?></span>
            </label>
         <?php endforeach; ?>
    </fieldset>

    <fieldset id="ptextsize" class="radiobuttons">
        <legend>Tekststørrelse</legend>
            <?php foreach ($textsizes AS $key => $v): ?>
                <label for="<?php echo $key; ?>" class="<?php echo $key; if ($value['ptextsize'] == $key) echo ' selected'; ?>">
                    <input type="radio" name="ptextsize" id="<?php echo $key; ?>" value="<?php echo $key; ?>" <?php if ($value['ptextsize'] == $key) echo ' checked="checked"'; ?> /> <?php echo $v; ?>
                </label>
            <?php endforeach; ?>
    </fieldset>

    <fieldset>
        <legend>Visning</legend>
        <div>
            <label for="rows_pr_page">Rækker pr. side</label>
            <input type="text" name="rows_pr_page" id="rows_pr_page" value="<?php print $value["rows_pr_page"]; ?>" />
        </div>
    </fieldset>
    */
    ?>
    <fieldset>
        <div class="formrow">
        <label for="language"><?php e(t('language')); ?></label>
        <select name="language" id="language">
            <?php foreach ($translation->getLangs() AS $key => $lang): ?>
                <option value="<?php e($key); ?>"<?php if (!empty($value['language']) AND $value['language'] == $key) echo ' selected="selected"'; ?>><?php e($lang); ?></option>
            <?php endforeach; ?>
        </select>
        </div>

    </fieldset>

    <?php /* if ($kernel->user->hasModuleAccess('cms')): ?>
    <fieldset>
        <legend><?php e(t('which editor do you want to use')); ?></legend>
        <div class="formrow">
        <label><?php e(t('editor')); ?></label>
            <select name="htmleditor">
            <?php
                foreach($editors AS $k=>$v) {
                    echo '<option value="'.$k.'"';
                    if (!empty($value['htmleditor']) AND $k == $value['htmleditor']) echo ' selected="selected"';
                    echo '>' . safeToForm($translation->get($v)) . '</option>';
                }
            ?>
            </select>

        </div>
    </fieldset>
    <?php endif; */ ?>


    <fieldset id="labelsize" class="radiobuttons">
        <legend><?php e(t('labels')); ?></legend>
        <p><?php e(t('choose which labels you use - when printing from acrobat reader remember to set page scaling to none')); ?></p>
            <?php foreach ($labels_standard AS $key => $v): ?>
                <label for="<?php e($key); ?>" class="<?php e($key); if ($value['label'] == $key) echo ' selected'; ?>">
                    <input type="radio" name="label" id="<?php e($key); ?>" value="<?php echo $key; ?>" <?php if ($value['label'] == $key) echo ' checked="checked"'; ?> /> <?php echo $v; ?>
                </label>
            <?php endforeach; ?>
    </fieldset>


    <div>
        <input type="submit" name="submit" value="<?php e(t('save', 'common')); ?>" />
            eller
        <a href="/controlpanel/"><?php e(t('regret', 'common')); ?></a>
    </div>

</form>

<?php
$page->end();
?>
