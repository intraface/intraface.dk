<?php
/**
 * Account
 *
 * @package Intraface_Accounting
 *
 * @author  Lars Olesen
 * @since   1.0
 * @version 1.0
 */
$year_module = $kernel->useModule('accounting');

$year = new Year($kernel);

if (count($year->getList()) == 0):

    $_advice[] = array(
        'msg' => 'no accounting year has been created',
        'link' => $year_module->getPath() . 'year_edit.php',
        'module' => $year_module->getName()
    );

endif;

?>