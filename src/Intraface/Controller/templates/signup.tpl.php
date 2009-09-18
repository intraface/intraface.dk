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
            <label for="email" id="email_label">E-mail</label>
            <input tabindex="1" type="text" name="email" id="email" value="" />
        </div>
        <div>
            <label for="password" id="password_label">Adgangskode</label>
            <input tabindex="2" type="password" name="password" id="password" value="" />
        </div>
        <div class="align-left">
            <label for="name" id="name_label">Intranet name</label>
            <input tabindex="3" type="text" name="name" id="name" value="" />
        </div>
        <div>
            <label for="identifier" id="identifier_label">Intranet identifier</label>
            <input tabindex="4" type="text" name="identifier" id="identifier" value="" />
        </div>
        <div style="clear: both;">
            <input tabindex="5" type="submit" value="Lad mig prøve" id="submit" />
        </div>

    </fieldset>

</form>
