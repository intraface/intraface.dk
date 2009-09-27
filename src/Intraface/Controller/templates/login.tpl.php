<h1><span>Intraface.dk</span></h1>

<form id="form-login" method="post" action="<?php e($context->url()); ?>">

    <fieldset>
    <?php
        if (!empty($msg)) { ?>
            <p><?php e($msg); ?></p>
        <?php }
        /*
        else {
            $actual = SystemDisturbance::getActual();
            if (count($actual) > 0 && $actual['important'] == 1) {
                echo '<p id="system_message">'.htmlspecialchars($actual['description']).'</p>';
            }
        }
        */

        ?>


        <div class="align-left">
            <label for="email" id="email_label"><?php e(t('Email')); ?></label>
            <input tabindex="1" type="text" name="email" id="email" value="<?php if (!empty($_COOKIE['username'])) e($_COOKIE['username']); ?>" />
        </div>
        <div>
            <label for="password" id="password_label"><?php e(t('Password')); ?></label>
            <input tabindex="2" type="password" name="password" id="password" value="<?php if (!empty($_COOKIE['password'])) e($_COOKIE['password']); ?>" />
        </div>

        <div>
            <input tabindex="3" type="submit" value="<?php e(t('Login')); ?>" id="submit" />
            <a tabindex="4" href="<?php e(url('../retrievepassword')); ?>"><?php e(t('Forgotten password')); ?></a>
            <a tabindex="4" href="<?php e(url('../signup')); ?>"><?php e(t('Register')); ?></a>
        </div>

    </fieldset>

</form>