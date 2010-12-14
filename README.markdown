Intraface.dk
============

Source code for intraface.dk. A system for small businesses made with the konstrukt framework.

Requirements
------------

1. Apache-server running PHP.
2. php-xml module.
3. MySQL-server
4. Allowing .htaccess configuration with auto_prepend_file
5. PEAR setup correctly
6. PHP module openSSL (for https requests to onlinepayment gateways)
7. PHP with magic_quotes_pgc = Off

Installation
------------

It is fairly easy to install intraface using the command line. You should follow the following steps:

First install phing

    pear install --force --alldeps pear_packagefilemanager
    pear channel-discover pear.phing.info
    pear install --alldeps phing/Phing
    pear channel-discover pear.domain51.com
    pear install --force d51/Phing_d51PearPkg2Task
    pear channel-discover pear.saltybeagle.com
    pear install --force --alldeps intrafacepublic/Phing_IlibPearDeployerTask 
    pear install --force --alldeps pear/PHP_CodeSniffer 
    
If ftpDeployTask is not located in phing/tasks/ext/FtpDeployTask.php get it from http://phing.info/trac/browser/branches/2.3/classes/phing/tasks/ext/FtpDeployTask.php
    

Then install all dependencies by creating a package and installing it

    pear channel-discover public.intraface.dk
    pear channel-discover pear.doctrine-project.org
    pear channel-discover htmlpurifier.org
    pear channel-discover pear.michelf.com
    pear channel-discover pearhub.org

Change directory so you are in the root directory of intraface:

    php generate_package_xml.php make
    pear package src/package.xml
    sudo pear install --alldeps --force src/Intraface-X.Y.Z.tgz
    sudo rm src/Intraface-X.Y.Z.tgz

Create the database
-------------------

In the install folder you will find the database structure. Make sure that you both setup the structure and values.

Misc. information about the installation
----------------------------------------

1. Copy all files to your server.
2. Make sure that all files in src/intraface.dk is put into the web accessible folder
3. Set up a database, create a user with all data and structure access.
4. Import strucuture and values to database from intraface.dk/install/
5. Create and give access to the webserver to write to log/
6. Create and give access to the webserver to write to upload/ 
7. Create and give access to the webserver to write to cache/ 
8. Create a config.local.php on the basis of config.local.default.php
9. Access intraface through your webbroser.
10. Login with start@intraface.dk, password: startup.
11. Go to intranetmaintenance -> Modules, and click 'Registrer Modules'
12. Go to Intranet, and edit/create your intranets. Remeber to change login data for the default created intranet.

Checklist for updating intraface
================================

1. Run all unit tests
2. Run all selenium tests
3. Write a twitter status with upgrade
4. Run backup/upload.sh 
5. Run backup/mysql.sh. 
6. Download backup/mysql/daily.0.sql.gz to your local machine
7. Update the generate_package_xml.php and generate package.xml (see below)
8. Create the PEAR package: PEAR package package.xml
9. Upload the package to server
10. Update the database structure from the install/database-updates.sql
11. Upgrade the intraface package: PEAR upgrade /home/intraface/Intraface-1.x.x.tgz  
12. ...
13. Ensure cron job is correct and they do not generate error.
14. Test general use of the system
15. Test that pdfs are working. Ensure that payment bank information is correct
16. Test that send invoice as e-mail sender works
17. Write twitter status
18. Goodnight!

How to update an package
------------------------

1. Go through the generate_package_xml.php to ensure everything is correct.
2. Remember to change the version number and update the version numbers for dependencies.
3. run "php generate_package_xml.php" to test the configuration
4. run "php generate_package_xml.php make" to create the package.xml file
5. run "pear package src/package.xml" to create the package
6. install the package locally on your computer "pear install /path/to/package/Package.tgz"
7. Check the files are installed correct
8. Upload the package as a new release on the channel.
