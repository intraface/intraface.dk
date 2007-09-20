<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'NewsletterTest.php';
require_once 'NewsletterObserverTest.php';
require_once 'NewsletterSubscriberTest.php';

class Newsletter_AllTests
{

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Intraface_Newsletter');

        $suite->addTestSuite('NewsletterTest');
        $suite->addTestSuite('NewsletterSubscriberTest');
        $suite->addTestSuite('NewsletterObserverTest');
        return $suite;
    }
}
?>