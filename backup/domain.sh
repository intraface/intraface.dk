#!/bin/bash
suffix=$(date +%y%m%d)
cd /home/intraface/.snapshot/nightly.0/
tar -zcvf /home/intraface/backup/archives/intraface.$suffix.tar intraface*/
# tar -zcvf /home/intraface/backup/archives/upload.$suffix.tar upload/
tar --exclude='*/tempdir*' --exclude='*/instance*' -zcvf /home/intraface/backup/archives/upload.$suffix.tar.gz upload/
tar -zcvf /home/intraface/backup/archives/kundelogin.$suffix.tar kundelogin*/
tar -zcvf /home/intraface/backup/archives/certificates.$suffix.tar certificates*/