To run tests:


Install phpunit: http://phpunit.de

If you get the error:
Fatal error:  Call to a member function getDSN() on a non-object 
in /usr/share/php5/PEAR/MDB2.php on line 489

Set $this->backupGlobals = false;
in Phpunit/Framework/Testcase.php 

If you get error
 Warning:  fopen(/usr/share/php5/PEAR/Document/fonts/php_Helvetica.afm): failed to open stream: Access denied 
 in /usr/share/php5/PEAR/Document/Cpdf.php on line 1465

Set write access for the server to PEAR/Document/fonts
