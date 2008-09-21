#!/bin/bash
cd /home/intraface/backup/
mkdir mysql
suffix=$(date +%y%m%d)
mysqldump --opt -uintraface -ppassword@g -h mysql.intraface.dk intraface > mysql/intraface.$suffix.sql
tar -zcvf archives/mysql_backup.$suffix.tar mysql/*
# date | mutt lars@intraface.dk -a /home/intraface/backup/archives/mysql_backup.$suffix.tar -s "MySQL Backup Intraface"
rm -r mysql/