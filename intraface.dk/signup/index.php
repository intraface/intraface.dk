<?php
require '../common.php';
require 'Intraface/Auth.php';

$title = 'Intraface.dk -> Login';

if($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!Validate::email($_POST['email'])) {
        $error[] = 'E-mail ugyldig';
    }
    if (!Validate::string($_POST['password'], VALIDATE_ALPHA . VALIDATE_NUM)) {
        $error[] = 'Password ugyldigt';
    }
    /*
    if (isset($_POST['phrase']) && isset($_SESSION['phrase']) &&
        strlen($_POST['phrase']) > 0 && strlen($_SESSION['phrase']) > 0
        && $_POST['phrase'] == $_SESSION['phrase']) {

        unset($_SESSION['phrase']);
    }
    else {
        $error[] = 'Tekst er forkert';
    }
    unlink(PATH_CAPTCHA . md5(session_id()) . '.png');
    */


    if (!empty($error) AND count($error) > 0) {
        $msg = 'Vi kunne ikke oprette dig';
    }
    else {
        $db = MDB2::singleton(DB_DSN);
        $res = $db->query("SELECT id FROM user WHERE email = ".$db->quote($_POST['email'], 'text'));
        if (PEAR::isError($res)) {
            trigger_error($res->getMessage(), E_USER_ERROR);
        }
        if ($res->numRows() == 0) {
            $res = $db->query("INSERT INTO user SET email = ".$db->quote($_POST['email'], 'text').", password=".$db->quote(md5($_POST['password']), 'text'));
            $user_id = $db->lastInsertID('user');
        }
        else {
            $error[] = 'Du er allerede oprettet';
        }

        if (!empty($error) AND count($error) > 0) {
            $msg = 'Du er allerede oprettet. <a href="'.PATH_WWW.'main/login.php">Login</a>.';
        }
        else {
            $intranet_id = 22; // betatest intranet for forskellige brugere

            // intranet access
            $db->query("INSERT INTO permission SET intranet_id = ".$db->quote($intranet_id, 'integer').", user_id = ".$db->quote($user_id, 'integer'));

            // module access
            $modules = array('onlinepayment', 'cms', 'filemanager', 'contact', 'debtor','quotation', 'invoice', 'order','accounting', 'product', 'stock', 'webshop');

            foreach ($modules AS $module) {
                $res = & $db->query("SELECT id FROM module WHERE name = ".$db->quote($module, 'text')." LIMIT 1");
                if ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                    $db->query("INSERT INTO permission SET
                        intranet_id = ".$db->quote($intranet_id, 'integer').",
                        user_id = ".$db->quote($user_id, 'integer').",
                        module_id = ".$db->quote($row['id'], 'integer'));
                }


            }

            $sub_access = array('edit_templates', 'setting', 'vat_report', 'endyear');

            foreach ($sub_access AS $module) {
                $res = & $db->query("SELECT id, module_id FROM module_sub_access WHERE name = ".$db->quote($module, 'text')." LIMIT 1");
                if ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {

                    $res = $db->query("INSERT INTO permission SET intranet_id = ".$db->quote($intranet_id, 'integer').", module_sub_access_id = ".$db->quote($row['id'], 'integer').", module_id = ".$db->quote($row['module_id'], 'integer').", user_id = ".$db->quote($user_id, 'integer'));
                    if (PEAR::isError($res)) {
                        trigger_error('Kunne ikke oprette nogle af rettighederne', E_USER_ERROR);
                    }
                }
            }

            $auth = new Auth(session_id());
            if (!$auth->login($_POST['email'], $_POST['password'])) {
                trigger_error('could not login', E_USER_ERROR);
                return false;
            }
            $auth->isLoggedIn();
            header('Location: '.PATH_WWW.'main/');
            exit;

        }


    }
}

        /*
        // Set CAPTCHA options (font must exist!)
        $options = array(
            'font_size' => 20,
            'font_path' => './',
            'font_file' => 'BOOKOSB.TTF'
        );

        // Generate a new Text_CAPTCHA object, Image driver
        $c = Text_CAPTCHA::factory('Image');
        $retval = $c->init(150, 50, null, $options);
        if (PEAR::isError($retval)) {
            trigger_error($retval->getMessage(), E_USER_ERROR);

        }

        // Get CAPTCHA secret passphrase
        $_SESSION['phrase'] = $c->getPhrase();

        // Get CAPTCHA image (as PNG)
        $png = $c->getCAPTCHAAsPNG();
        if (PEAR::isError($png)) {
            trigger_error($png->getMessage(), E_USER_ERROR);

        }



        if (!is_dir(PATH_CAPTCHA)) {
            mkdir(PATH_CAPTCHA);
            chmod(PATH_CAPTCHA, 644);
        }
        file_put_contents(PATH_CAPTCHA . md5(session_id()) . '.png', $png);
        */

include(PATH_INCLUDE_IHTML . 'outside/top.php');

?>

<h1><span>Intraface.dk</span></h1>

<form id="form-login" method="post" action="<?php echo basename($_SERVER['PHP_SELF']); ?>">

    <fieldset>
        <legend>Opret mig i systemet med følgende oplysninger</legend>
        <p>Du kan prøve systemet ved at skrive din e-mail og adgangskode nedenunder. Derefter logger vi dig direkte ind i systemet.</p>
    <?php
        if (!empty($msg)) {
            echo '<p>'.$msg.'</p>';
        }
    ?>


        <div class="align-left">
            <label for="email" id="email_label">E-mail:</label>
            <input tabindex="1" type="text" name="email" id="email" value="" />
        </div>
        <div>
            <label for="password" id="password_label">Adgangskode:</label>
            <input tabindex="2" type="password" name="password" id="password" value="" />
        </div>
    <!--
        <div class="align-left" style="clear: both;">
            <?php echo '<img src="file.php?' . time() . '" />'; ?>
        </div>
        <div>
            <label for="phrase">Skriv tekst fra billede</label>
            <input tabindex="3" type="text" name="phrase" id="phrase" />
        </div>
-->
        <div style="clear: both;">
            <input tabindex="4" type="submit" value="Lad mig prøve" id="submit" />
        </div>

    </fieldset>

</form>

<?php
include(PATH_INCLUDE_IHTML . 'outside/bottom.php');
?>