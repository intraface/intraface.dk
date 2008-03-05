<?php
require_once 'Ilib/Testing/Selenium/Selenese/TestSuiteGenerator.php';

$writer = new Ilib_Testing_Selenium_Selenese_TestSuiteGenerator;
if ($writer->write()) {
    echo "Success, wrote content to file testSuite.html\n";
} else {
    echo "Failire, did not wrote content to file testSuite.html\n";
}
