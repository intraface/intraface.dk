#!/bin/sh

DIR=/home/intrafac/backup/mysql
DB_USER=intraface
DB_PASS=password
DB_HOST=localhost
DB_DATABASE=intrafac_intraface

# delete the oldest backup
if [ -e $DIR/daily.7.sql.gz ] 
then
    rm daily.7.sql.gz
fi

# move backups
for i in 6 5 4 3 2 1
do
    if [ -e $DIR/daily.$i.sql.gz ]
    then 
        let n=$i+1
        mv $DIR/daily.$i.sql.gz daily.$n.sql.gz
    fi
done

mysqldump --opt -u$DB_USER -p$DB_PASS -h$DB_HOST $DB_DATABASE > $DIR/daily.0.sql
gzip $DIR/daily.0.sql

echo "finished"
