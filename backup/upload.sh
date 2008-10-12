#!/bin/bash

BACKUP_DIR=/home/intrafac/upload/
TARGET=intraface_upload@olive.dreamhost.com:/home/intraface_upload/solar/upload

rsync -e ssh -rltv --exclude="tempdir/"  $BACKUP_DIR $TARGET/dayly.0
