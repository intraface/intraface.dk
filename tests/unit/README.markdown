Unit tests
==

Report bugs
--

Please help out running the test suite. Report bugs at: http://github.com/intraface/intraface.dk

Run tests
--

Create a config.test.php file. Check out the phpunit.xml.example and see if you want to setup your own local example.

Install phpunit from http://phpunit.de

    phpunit .
    
Troubleshooting
--

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
