#!/bin/sh
BACKUP_DIR="./backup/$(date +%F--%H-%M-%S)"
mkdir $BACKUP_DIR
drush sql:dump |gzip  > $BACKUP_DIR/db.sql

tar -czf $BACKUP_DIR/dump.tar.gz --exclude=./web/sites/default $(cat ./scripts/files.txt)
