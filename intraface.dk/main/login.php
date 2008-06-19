<?php
require '../common.php';

$title = 'Intraface.dk -> Login';

if (isset($_POST['email']) AND isset($_POST['password'])) {
    session_start();

	$adapter = new Intraface_Auth_User(MDB2::singleton(DB_DSN), session_id(), $_POST['email'], $_POST['password']);

    $auth = new Intraface_Auth(session_id());
    $auth->attachObserver(new Intraface_Log);
    
    $user = $auth->authenticate($adapter);

	if (is_object($user)) {
	    header('Location: '.PATH_WWW.'main/index.php');
        exit;
    } else {
		$msg = 'wrong credentials';
    }
} elseif (!empty($_GET['msg'])) {
	$msg = $_GET['msg'];
}
include(PATH_INCLUDE_IHTML . 'outside/top.php');

?>

<h1><span>Intraface.dk</span></h1>

<form id="form-login" method="post" action="<?php echo basename($_SERVER['PHP_SELF']); ?>">

    <fieldset>
    <?php
        if (!empty($msg)) {
            echo '<p>'.htmlspecialchars(strip_tags($msg)).'</p>';
        }
        /*
        else {
            $actual = SystemDisturbance::getActual();
            if(count($actual) > 0 && $actual['important'] == 1) {
                echo '<p id="system_message">'.htmlspecialchars($actual['description']).'</p>';
            }
        }
        */

        ?>


        <div class="align-left">
            <label for="email" id="email_label">E-mail:</label>
            <input tabindex="1" type="text" name="email" id="email" value="<?php if (!empty($_COOKIE['username'])) echo htmlentities(strip_tags($_COOKIE['username'])); ?>" />
        </div>
        <div>
            <label for="password" id="password_label">Adgangskode:</label>
            <input tabindex="2" type="password" name="password" id="password" value="<?php if (!empty($_COOKIE['password'])) echo htmlentities(strip_tags($_COOKIE['password'])); ?>" />
        </div>

        <div>
            <input tabindex="3" type="submit" value="Login" id="submit" /> <a tabindex="4" href="forgotten_password.php">Glemt password?</a>
        </div>

    </fieldset>

    <p style="text-align: center;">
        <a href="http://blog.intraface.dk/">Website</a> |
        <a href="http://blog.intraface.dk/blog/">Nyheder</a> |
        <a href="http://blog.intraface.dk/kontakt/">Kontakt</a> |
        <a href="<?php echo PATH_WWW; ?>signup/">Prøv systemet</a>
    </p>
    <!--<p>Intraface.dk er et system målrettet til mindre virksomheder. Forskellige moduler klarer både webshop, fakturaer, regnskab og nyhedsbreve.</p>-->

</form>

<?php
include(PATH_INCLUDE_IHTML . 'outside/bottom.php');
?>