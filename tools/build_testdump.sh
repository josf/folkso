#!/bin/bash
DUMPDIR=/home/joseph/sect/fab
TOOLDIR=/home/joseph/sect/fab/tools/

mysqldump -u root --password=hellyes --no-create-db --no-create-info testostonomie > $TOOLDIR/testdump-raw.sql

cat $TOOLDIR/testdump_init.sql  $TOOLDIR/testdump-raw.sql  > $DUMPDIR/testdump_test.sql
