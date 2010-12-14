About the test suite
====================

Intraface both have selenium tests and unit tests written with PHPUnit.

Running the unit tests:

    apt-get install php5-dev
    apt-get install php5
    pecl install xdebug
    pear channel-discover pear.phpunit.de
    pear install --alldeps --force phpunit/PHPUnit

Running the selenium tests:

Install the Selenium IDE for Firefox. To generate the test suite files, you should change directory to the tests/selenium directory.

    php generate_test_suite.php
    
Now open the test suite from Firefox.
