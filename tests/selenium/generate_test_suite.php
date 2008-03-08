<?php
require_once 'Ilib/Testing/Selenium/Selenese/TestSuiteGenerator.php';

$writer = new Ilib_Testing_Selenium_Selenese_TestSuiteGenerator;
$writer->addReplacement('##path_test_root##', dirname(__FILE__));
$writer->addReplacement('@@dirctory_separator@@', DIRECTORY_SEPARATOR);
if ($writer->write()) {
    echo "Success, wrote content to file testSuite.html\n";
} else {
    echo "Failire, did not wrote content to file testSuite.html\n";
}
