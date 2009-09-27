<?php
$value = $context->getValues();
?>

<h1><?php e(t('Edit list')); ?></h1>

<?php echo $context->getList()->error->view(); ?>

<form action="<?php e(url()); ?>" method="post">
    <fieldset>
    <legend><?php e(t('About the list')); ?></legend>
    <div class="formrow">
      <label for="title">Titel</label>
        <input type="text" name="title" value="<?php if (!empty($value['title'])) e($value['title']); ?>" />
  </div>
<?php
/*
    <div class="formrow">
      <label for="subscribe_option_key">Tilmeldingsmuligheder</label>
        <select name="subscribe_option_key" id="subscribe_option_key">
            <?php
            $newsletter_module = $context->getKernel()->getModule('newsletter');

            foreach ($newsletter_module->getSetting('subscribe_option') AS $key => $option) {
                ?>
                <option value="<?php e($key); ?>" <?php if ($value['subscribe_option_key'] == $key) print("selected=\"selected\""); ?> ><?php e($newsletter_module->getTranslation($option)); ?></option>
                <?php
            }
            ?>
        </select>
  </div>
    */
?>
      <div style="clear: both;">
        <label for="description"><?php e(t('Description')); ?></label><br />
        <textarea name="description" cols="90" rows="10"><?php if (!empty($value['description'])) e($value['description']); ?></textarea>
    </div>
</fieldset>
<fieldset>
    <legend><?php e(t('From address on the email')); ?></legend>
    <div class="formrow">
      <label for="sender_name"><?php e(t('Sender name')); ?></label>
        <input type="text" name="sender_name" value="<?php if (!empty($value['sender_name'])) e($value['sender_name']); ?>" /> (Hvis den er tom, er intranettet afsender)
    </div>
    <div class="formrow">
      <label for="reply_email"><?php e(t('Reply email')); ?></label>
        <input type="text" name="reply_email" value="<?php if (!empty($value['reply_email'])) e($value['reply_email']); ?>" /> (Hvis den er tom, er intranettets e-mail-adresse til svar)
    </div>
</fieldset>
<fieldset>
  <legend><?php e(t('Additional information')); ?></legend>
  <!--
    <div class="formrow">
      <label for="privacy_policy">Privatlivspolitik</label>
        <input type="text" name="privacy_policy" value="<?php if (!empty($value['privacy_policy'])) e($value['privacy_policy']); ?>" />
  </div>
-->


    <div style="clear: both;">
        <p><?php e(t('Either you are using this link')); ?> <strong><?php e('http://' . $context->getKernel()->setting->get('intranet', 'contact.login_url') . '/' .$context->getKernel()->intranet->get('identifier') . '/login'); ?></strong> <?php e(t('or you can write your own')); ?>:</p>
        <label for="optin_link"><?php e(t('Link for the optin page')); ?></label><br />
        <input type="text" name="optin_link" value="<?php if (!empty($value['optin_link'])) e($value['optin_link']); ?>" />
    </div>


    <div style="clear: both;">
        <label for="subscribe_subject"><?php e(t('Subject for the confirmation e-mail')); ?></label><br />
        <input type="text" name="subscribe_subject" value="<?php if (!empty($value['subscribe_subject'])) e($value['subscribe_subject']); ?>" />
    </div>

    <div style="clear: both;">
        <label for="subscribe_message"><?php e(t('Text in the confirmation email')); ?></label><br />
        <textarea name="subscribe_message" cols="90" rows="10"><?php if (!empty($value['subscribe_message'])) e($value['subscribe_message']); ?></textarea>
    </div>
    <!--
    <div style="clear: both;">
        <label for="unsubscribe_message">Frameldingsbesked</label><br />
        <textarea name="unsubscribe_message" cols="90" rows="10"><?php if (!empty($value['unsubscribe_message'])) e($value['unsubscribe_message']); ?></textarea>
    </div>
    -->

    <div>
      <input type="submit" name="submit" value="<?php e(t('Save')); ?>" class="save" />
        <a href="<?php e(url(null)); ?>"><?php e(t('Close')); ?></a>
        <input type="hidden" name="id" value="<?php if (!empty($value['id'])) e($value['id']); ?>" />
    </div>

    </fieldset>
</form>
