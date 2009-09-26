<h1><span>Intraface.dk</span></h1>

<form id="form-login" method="post" action="<?php e(url(null)); ?>">

    <fieldset>
        <legend>Opret mig i systemet med følgende oplysninger</legend>
        <p>Du kan prøve systemet ved at skrive din e-mail og adgangskode nedenunder. Derefter logger vi dig direkte ind i systemet.</p>
    <?php
        if (!empty($msg)) {
            echo '<p>'.$msg.'</p>';
        }
    ?>


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
