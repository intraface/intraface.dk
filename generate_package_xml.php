<?php
/**
 * package.xml generation script
 *
 * @package Intraface
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */

die('NOT IMPLEMENTED YET - ONLY USED TO DISPLAY DEPENDENT PACKAGES');

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);
$pfm = new PEAR_PackageFileManager2();
$pfm->setOptions(
    array(
        'baseinstalldir'    => 'Intraface',
        'filelistgenerator' => 'file',
        'packagedirectory'  => dirname(__FILE__),
        'packagefile'       => 'package.xml',
        'ignore'            => array(
			'generate_package_xml.php',
			'package.xml',
			'*.tgz'
			),
		'exceptions'        => array(),
        'simpleoutput'      => true,
	)
);

$pfm->setPackage('Intraface');
$pfm->setSummary('');
$pfm->setDescription('');
$pfm->setUri('http://localhost/');
$pfm->setLicense('LGPL License', 'http://www.gnu.org/licenses/lgpl.html');
$pfm->addMaintainer('lead', 'lars', 'Lars Olesen', 'lars@legestue.net');

$pfm->setPackageType('php');

$pfm->setAPIVersion('1.6.1');
$pfm->setReleaseVersion('1.6.1');
$pfm->setAPIStability('beta');
$pfm->setReleaseStability('stable');
$pfm->setNotes('Needs to be filled in');
$pfm->addRelease();

$pfm->addGlobalReplacement('package-info', '@package-version@', 'version');

$pfm->clearDeps();
$pfm->setPhpDep('5.2.0');
$pfm->setPearinstallerDep('1.5.0');
$pfm->addPackageDepWithChannel('required', 'MDB2', 'pear.php.net', '2.4.0');
$pfm->addPackageDepWithChannel('required', 'Translation2', 'pear.php.net', '2.0.0');
$pfm->addPackageDepWithChannel('required', 'Log', 'pear.php.net', '1.9.10');

$pfm->generateContents();

if (isset($_GET['make']) || (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make')) {
	echo 'write package file';
    $pfm->writePackageFile();
} else {
	echo 'debug package file';
    $pfm->debugPackageFile();
}
?>