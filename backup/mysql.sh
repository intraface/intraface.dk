#!/bin/sh

## Configuration

DIR=/home/intrafac/backup/mysql
DB_USER=intrafac
DB_PASS=password
DB_HOST=localhost
DB_DATABASE=intrafac_intraface

## Create backup

# dump database
mysqldump --opt -u$DB_USER -p$DB_PASS -h$DB_HOST $DB_DATABASE > $DIR/daily.sql
# gzip dump
gzip $DIR/daily.sql

## Backup rotation

# every sunday we store the oldest backup a little longer
if [[ $(date +%u) = 7 && -e $DIR/daily.7.sql.gz ]]
then
    # delete the oldest backup
    if [ -e $DIR/weekly.4.sql.gz ] 
    then
        rm $DIR/weekly.4.sql.gz
    fi
 
    # move backups
    for i in 3 2 1 0
    do
        if [ -e $DIR/weekly.$i.sql.gz ]
        then 
            let n=$i+1
            mv $DIR/weekly.$i.sql.gz $DIR/weekly.$n.sql.gz
        fi
    done
    
    mv $DIR/daily.7.sql.gz $DIR/weekly.0.sql.gz      
fi

# delete the oldest backup
if [ -e $DIR/daily.7.sql.gz ] 
then
    rm $DIR/daily.7.sql.gz
fi

# move backups
for i in 6 5 4 3 2 1 0
do
    if [ -e $DIR/daily.$i.sql.gz ]
    then 
        let n=$i+1
        mv $DIR/daily.$i.sql.gz $DIR/daily.$n.sql.gz
    fi
done

# we put the newest backup in the rotation.
mv $DIR/daily.sql.gz $DIR/daily.0.sql.gz

echo "finished"
