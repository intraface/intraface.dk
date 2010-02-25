<h1><?php e(t('Edit element')); ?></h1>

<ul>
	<li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
</ul>

<?php
echo $element->error->view(array($context, 't'));
?>

<form method="post" action="<?php e(url(null, array('type' => $element->get('type')))); ?>"  enctype="multipart/form-data">
    <input name="type" type="hidden" value="<?php e($element->get('type')); ?>" />

<?php
// disse elementtyper skal svare til en elementtype i en eller anden fil.

switch ($value['type']) {
    case 'htmltext':
        include 'element/htmltext.tpl.php';
        break;
    case 'wikitext':
        include 'element/wikitext.tpl.php';
        break;
    case 'picture':
        include 'element/picture.tpl.php';
        break;
    case 'pagelist':
        include 'element/pagelist.tpl.php';
        break;
    case 'filelist':
        include 'element/filelist.tpl.php';
        break;
    case 'flickr':
        include 'element/flickr.tpl.php';
        break;
    case 'delicious':
        // hvis der er flere b�r vi ogs� underst�tte dem.
        include 'element/delicious.tpl.php';
        break;
    case 'video':
        include 'element/video.tpl.php';
        break;
    case 'map':
        include 'element/map.tpl.php';
        break;
    case 'gallery':
        include 'element/gallery.tpl.php';
        break;
    case 'randompicture':
        include 'element/randompicture.tpl.php';
        break;
    case 'twitter':
        include 'element/twitter.tpl.php';
        break;
    default:
        trigger_error(t('not a valid type'), E_USER_ERROR);
    break;

}
?>

    <fieldset>
        <legend><?php e(t('element settings')); ?></legend>

        <div class="formrow">
            <label for="elm-properties"><?php e(t('element properties')); ?></label>
            <select name="elm_properties">
                <?php foreach ($element->properties AS $key => $property): ?>
                <option value="<?php e($key); ?>"<?php if (!empty($value['elm_properties']) AND $value['elm_properties'] == $key) echo ' selected="selected"'; ?>><?php e(t($property)); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="formrow">
            <label for="elm-adjust"><?php e(t('element adjustment')); ?></label>
            <select name="elm_adjust">
                <?php foreach ($element->alignment AS $key => $alignment): ?>
                <option value="<?php e($key); ?>"<?php if (!empty($value['elm_adjust']) AND $value['elm_adjust'] == $key) echo ' selected="selected"'; ?>><?php e(t($alignment, 'cms')); ?></option>
                <?php endforeach; ?>
            </select>
        </div>


        <div class="formrow">
            <label for="elm-width"><?php e(t('element width')); ?></label>
            <input name="elm_width" id="elm-width" type="text" value="<?php if (!empty($value['elm_width'])) e($value['elm_width']); ?>" size="3" maxlength="10" /> <?php e(t('use either %, em or px')); ?>
        </div>


        <div class="radiorow">
            <p>
                <input name="elm_box" id="elm-box" value="box" type="checkbox"<?php if (!empty($value['elm_box']) AND $value['elm_box'] == 'box') echo ' checked="checked"'; ?> /> <label for="elm-box"><?php e(t('show element in a box')); ?></label>
            </p>
        </div>


    </fieldset>

    <fieldset>
        <legend><?php e(t('publish settings','cms')); ?></legend>

        <div class="formrow">
            <label for="dateFieldPublish"><?php e(t('publish date','cms')); ?></label>
            <input name="date_publish" id="dateFieldPublish" type="text" value="<?php if (!empty($value['date_publish'])) e($value['date_publish']); ?>" size="30" maxlength="225" /> <span id="dateFieldMsg1"><?php e(t('empty is today')); ?></span>
        </div>

        <div class="formrow">
            <label for="dateFieldExpire"><?php e(t('expire date','cms')); ?></label>
            <input name="date_expire" id="dateFieldExpire" type="text" value="<?php if (!empty($value['date_expire']))  e($value['date_expire']); ?>" size="30" maxlength="225" /> <span id="dateFieldMsg2"><?php e(t('empty never expires')); ?></span>
        </div>
    </fieldset>

    <div class="">
        <input type="submit" value="<?php e(t('save')); ?>" />
        <input type="submit" name="close" value="<?php e(t('save and close')); ?>" />
        <a href="<?php e(url('../')); ?>"><?php e(t('Cancel')); ?></a>
    </div>

</form>