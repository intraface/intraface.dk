<?php
require_once 'Ilib/Testing/Selenium/Selenese/TestSuiteGenerator.php';

$writer = new Ilib_Testing_Selenium_Selenese_TestSuiteGenerator;
$writer->addReplacement('##path_test_root##', dirname(__FILE__));
$writer->addReplacement('@@dirctory_separator@@', DIRECTORY_SEPARATOR);
if ($writer->write()) {
    echo "Success: added selenese files to testSuite.html\n";
} else {
    echo "Failure: could not add selenese files to file testSuite.html\n";
}
