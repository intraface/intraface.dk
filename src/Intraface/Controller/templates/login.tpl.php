<h1><span>Intraface.dk</span></h1>

<form id="form-login" method="post" action="<?php e(url()); ?>">

    <fieldset>
    <?php
    if ($context->query('flare')) : ?>
            <p><?php e(t($context->query('flare'))); ?></p>
        <?php                                                                                                             endif; ?>

        <div class="align-left">
            <label for="email" id="email_label"><?php e(t('Email')); ?></label>
            <input type="text" name="email" id="email" value="<?php if (!empty($_COOKIE['username'])) {
                e($_COOKIE['username']);
} ?>" />
        </div>
        <div>
            <label for="password" id="password_label"><?php e(t('Password')); ?></label>
            <input type="password" name="password" id="password" value="<?php if (!empty($_COOKIE['password'])) {
                e($_COOKIE['password']);
} ?>" />
        </div>

        <div>
            <input type="submit" value="<?php e(t('Login')); ?>" id="submit" />
            <a href="<?php e(url('../retrievepassword')); ?>"><?php e(t('Forgotten password')); ?></a>
        </div>

    </fieldset>

</form>
