<h1>Signup</h1>

<form id="form-login" method="post" action="<?php e(url(null)); ?>">

    <fieldset>
        <legend><?php e(t('Please create a user for me')); ?></legend>
        <p><?php echo $context->msg; ?></p>

        <div class="align-left">
            <label for="email" id="email_label"><?php e(t('Email')); ?></label>
            <input tabindex="1" type="text" name="email" id="email" value="" />
        </div>
        <div>
            <label for="password" id="password_label"><?php e(t('Password')); ?></label>
            <input tabindex="2" type="password" name="password" id="password" value="" />
        </div>
        <div class="align-left">
            <label for="name" id="name_label"><?php e(t('Intranet name')); ?></label>
            <input tabindex="3" type="text" name="name" id="name" value="" />
        </div>
        <div>
            <label for="identifier" id="identifier_label"><?php e(t('Intranet identifier')); ?></label>
            <input tabindex="4" type="text" name="identifier" id="identifier" value="" />
        </div>
        <div style="clear: both;">
            <input tabindex="5" type="submit" value="<?php e(t('Let me try')); ?>" id="submit" />
        </div>

    </fieldset>

</form>
