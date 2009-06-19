<?php

/**
 * Always keep your application up-to-date with the most recent and stable
 * version
 * of PEAR::Log package.
 *
 * PHP versions 4 and 5
 *
 * @category PEAR
 * @package  PEAR_PackageUpdate
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version  CVS: $Id: withoutFrontend.php,v 1.8 2007/11/25 16:37:36 farell Exp
 * $
 * @link     http://pear.php.net/package/PEAR_PackageUpdate
 * @since    File available since Release 0.5.0
 */

require_once 'Ilib/ClassLoader.php';

/**
 * This class allow to use PEAR_PackageUpdate as backend without any frontend.
 * No end-user action needed.
 *
 * @ignore
 */
class PEAR_PackageUpdate_Null extends PEAR_PackageUpdate
{
    /**
     * Cli driver class constructor
     *
     * @param string $packageName The package to update.
     * @param string $channel     The channel the package resides on.
     * @param string $user_file   (optional) file to read PEAR user-defined
     *                             options from
     * @param string $system_file (optional) file to read PEAR system-wide
     *                             defaults from
     * @param string $pref_file   (optional) file to read PPU user-defined
     *                             options from
     *
     * @access public
     * @return void 
     * @since  0.5.0
     */
    function PEAR_PackageUpdate_Null($packageName, $channel,
        $user_file = '', $system_file = '', $pref_file = '')
    {
        parent::PEAR_PackageUpdate($packageName, $channel,
            $user_file, $system_file, $pref_file);
    }
 
    /**
     * Null driver always redirects to current script
     * to force the user to restart the application.
     *
     * @access public
     * @return void 
     * @since  0.5.0
     */
    function forceRestart()
    {
        // removes warning message given by pear installer
        ob_end_clean();
        // Reload current page.
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Check for updates of PEAR::Log package though pear.php.net channel
$ppu = PEAR_PackageUpdate::factory('Null', 'Numbers_Roman', 'pear');
if ($ppu !== false) {
    // Check for new stable version
    $ppu->setMinimumState(PEAR_PACKAGEUPDATE_STATE_STABLE);
    $ppu->setMinimumReleaseType(PEAR_PACKAGEUPDATE_TYPE_BUG);
    if ($ppu->checkUpdate()) {
        ob_start();
        if ($ppu->update()) {
            // If the update succeeded, the application should be restarted.
            $ppu->forceRestart();
        }
        ob_end_clean();
    }
}

// Check for errors.
if ($ppu->hasErrors()) {
    $error = $ppu->popError();
    echo "<b>Error occured when trying to update: PEAR::Log package</b> <br />\n";
    echo "<b>Message:</b> " . $error['message'] ."<br />\n";
    if (isset($error['context'])) {
        echo "<hr /><i>Context:</i><br />\n";
        echo "<b>File:</b> " . $error['context']['file'] ."<br />\n";
        echo "<b>Line:</b> " . $error['context']['line'] ."<br />\n";
        echo "<b>Function:</b> " . $error['context']['function'] ."<br />\n";
        echo "<b>Class:</b> " . $error['context']['class'] ."<br />\n";
    }
    exit();
}
 
// your application code goes here ...

print 'Hello World';

?>