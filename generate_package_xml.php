<?php
/**
 * package.xml generation script
 *
 * @package Intraface
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */

$version = '1.6.0';
$notes = '
* Initial release as a PEAR package
';

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);
$pfm = new PEAR_PackageFileManager2();
$pfm->setOptions(
    array(
        'baseinstalldir'    => '/',
        'filelistgenerator' => 'file',
        'packagedirectory'  => dirname(__FILE__),
        'packagefile'       => 'package.xml',
        'ignore'            => array(
			'generate_package_xml.php',
			'package.xml',
			'*.tgz',
            'upload/2/1',
            'tests/'
			),
		'exceptions'        => array(),
        'simpleoutput'      => true,
	)
);

$pfm->setPackage('Intraface');
$pfm->setSummary('Intraface');
$pfm->setDescription('Intraface');
$pfm->setUri('http://localhost/');
$pfm->setLicense('LGPL License', 'http://www.gnu.org/licenses/lgpl.html');
$pfm->addMaintainer('lead', 'lsolesen', 'Lars Olesen', 'lars@legestue.net');
$pfm->addMaintainer('lead', 'sj', 'Sune Jensen', 'sj@sunet.dk');

$pfm->setPackageType('php');

$pfm->setAPIVersion($version);
$pfm->setReleaseVersion($version);
$pfm->setAPIStability('beta');
$pfm->setReleaseStability('stable');
$pfm->setNotes($notes);
$pfm->addRelease();

$pfm->addGlobalReplacement('package-info', '@package-version@', 'version');

$pfm->clearDeps();
$pfm->setPhpDep('5.2.0');
$pfm->setPearinstallerDep('1.5.0');

// Kernel
$pfm->addPackageDepWithChannel('required', 'MDB2', 'pear.php.net', '2.4.0');
$pfm->addPackageDepWithChannel('required', 'Translation2', 'pear.php.net', '2.0.0');
$pfm->addPackageDepWithChannel('required', 'Log', 'pear.php.net', '1.9.10');
$pfm->addPackageDepWithUri('required', 'ErrorHandler', 'http://svn.intraface.dk/intrafacepublic/3Party/ErrorHandler/ErrorHandler-0.2.1');
$pfm->addPackageDepWithUri('required', 'MDB2_Debug_ExplainQueries', 'http://svn.intraface.dk/intrafacepublic/3Party/MDB2/MDB2_Debug_ExplainQueries-0.1.0');

// email
$pfm->addPackageDepWithUri('required', 'phpmailer', 'http://svn.intraface.dk/intrafacepublic/3Party/phpmailer/phpmailer-1.73.0');

// cms
$pfm->addPackageDepWithChannel('required', 'HTMLPurifier', 'htmlpurifier.org', '1.6.0');
$pfm->addPackageDepWithUri('required', 'Markdown', 'http://svn.intraface.dk/intrafacepublic/3Party/Markdown/PHPMarkdown-1.0.1');
$pfm->addPackageDepWithUri('required', 'SmartyPants', 'http://svn.intraface.dk/intrafacepublic/3Party/SmartyPants/PHPSmartyPants-1.5.1');
$pfm->addPackageDepWithUri('required', 'phpFlickr', 'http://svn.intraface.dk/intrafacepublic/3Party/phpFlickr/phpFlickr-1.6.1');

// debtor
$pfm->addPackageDepWithUri('required', 'CPdf', 'http://svn.intraface.dk/intrafacepublic/3Party/Cpdf/Cpdf-0.0.9');
$pfm->addPackageDepWithUri('required', 'quickpay', 'http://svn.intraface.dk/intrafacepublic/3Party/Quickpay/Quickpay-1.17.1');


$pfm->generateContents();

if (isset($_GET['make']) || (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make')) {
    if ($pfm->writePackageFile()) {
        exit('package file written');
    }
} else {
    $pfm->debugPackageFile();
}
?>