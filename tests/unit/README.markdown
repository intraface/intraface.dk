Unit tests
==========

Unit tests for intraface.dk.

Report bugs
-----------

Please help out running the test suite. Report bugs at: http://github.com/intraface/intraface.dk/issues

Requirements
------------

* Test database
* PHPUnit

Run tests
---------

First install PHPUnit:

    pear channel-discover pear.phpunit.de
    pear install phpunit/phpunit

Add a phpunit.xml file to the test suite. Copy phpunit.example.xml and edit the settings in the file.

    cp phpunit.example.xml phpunit.xml

Run the test suite from within the unit test directory as follows

    phpunit --process-isolation .
    
Troubleshooting
---------------

If you get the error:

    Fatal error:  Call to a member function getDSN() on a non-object 
    in /usr/share/php5/PEAR/MDB2.php on line 489

In the test file set; 

    protected $backupGlobals = false;

If you get error

    Warning:  fopen(/usr/share/php5/PEAR/Document/fonts/php_Helvetica.afm): failed to open stream: Access denied 
    in /usr/share/php5/PEAR/Document/Cpdf.php on line 1465

Set write access for the server to PEAR/Document/fonts

The error
    
    Segmentation Fault

Should be corrected in Xdebug 2.1.0-CVS
