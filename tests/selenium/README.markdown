Running the selenium tests
==

Install the selenium add-on for Firefox. Generate the test suite:

    php generate_test_suite.php

Install Selemium Server
--

You only need this, if you are not running the test suite from Firefox.

1. Download selenium server from http://www.openqa.org/selenium-rc/
2. Make sure that java and a browser is installed as described in the documentation for selenium
   Note: On Linux firefox-bin is in /usr/lib/firefox-*/firefox-bin so you might need to "ln -s /usr/lib/firexfox-*/firefox-bin /usr/local/firefox-bin". Remember to write the correct version number instead of *.
3. Pack out the files. The server is in selenium-server-*/ and start is as described in the documentation.
4. Ready!