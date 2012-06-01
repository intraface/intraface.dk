[Intraface.dk](http://intraface.dk)
============

[![Build Status](https://secure.travis-ci.org/intraface/intraface.dk.png?branch=master)](http://travis-ci.org/intraface/intraface.dk)

Source code for [intraface.dk](http://intraface.dk). A system for small businesses made with the [konstrukt.dk](http://konstrukt) framework.

Requirements
------------

1. Apache-server running PHP.
2. php-xml module.
3. MySQL-server
4. Allowing .htaccess configuration with auto_prepend_file
5. PEAR setup correctly
6. PHP module openSSL (for https requests to onlinepayment gateways)
7. PHP with magic_quotes_pgc = Off
8. Intraface dependencies - install by creating a PEAR package; see below

Preparing dependencies
----------------------

It is fairly easy to install intraface using the command line. 

First install phing

    pear install --force --alldeps pear_packagefilemanager
    pear channel-discover pear.phing.info
    pear install --alldeps phing/Phing
    pear channel-discover pear.domain51.com
    pear install --force d51/Phing_d51PearPkg2Task
    pear channel-discover pear.saltybeagle.com
    pear install --force --alldeps intrafacepublic/Phing_IlibPearDeployerTask 
    pear install --force --alldeps pear/PHP_CodeSniffer 
    
Notice: If ftpDeployTask is not located in phing/tasks/ext/FtpDeployTask.php get it from [phing](http://phing.info/trac/browser/branches/2.3/classes/phing/tasks/ext/FtpDeployTask.php).

Make sure that your PEAR installation knows the following channels:

    pear channel-discover public.intraface.dk
    pear channel-discover pear.doctrine-project.org
    pear channel-discover htmlpurifier.org
    pear channel-discover pear.michelf.com
    pear channel-discover pearhub.org

Now you are ready to create the PEAR package. The PEAR package will take care of installing all dependencies and put files in the correct web accessible folder. 

To create the package
---------------------

Change directory so you are in the root directory of intraface:

    php generate_package_xml.php make
    pear package src/package.xml

Install the package
-------------------

You need to specify which folder is the web accessible folder:

    pear config-set www_dir /home/intraface/intraface.dk

Now you are ready to install the package:

    sudo pear install --alldeps --force src/Intraface-X.Y.Z.tgz
    sudo rm src/Intraface-X.Y.Z.tgz

Then you need to navigate to your web accessible folder and create a config file:

    cp config.local.example.php config.local.php

Edit the values in the config file, and make sure:

- Create and give access to the webserver to write to log/
- Create and give access to the webserver to write to upload/ 
- Create and give access to the webserver to write to cache/ 

Now you are ready to access intraface through your webbrowser:

- Login with start@intraface.dk, password: startup.
- Go to intranetmaintenance -> Modules, and click 'Registrer Modules'
- Go to Intranet, and edit/create your intranets. Remeber to change login data for the default created intranet.

Create the database
-------------------

In the install folder you will find the database structure. Make sure that you both setup the structure and values.

Updating the package
--------------------

If you create updates for intraface, you just create a new package.

- Check generate_package_xml.php to ensure everything is correct.
- Remember to change the version number and update the version numbers for dependencies.

    php generate_package_xml.php make
    pear package src/package.xml

Install the package locally on your computer

    pear install /path/to/package/Package.tgz

Make sure that everything works correctly, and now you can upgrade, using:

    pear upgrade Intraface-X.Y.Z.tgz

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
