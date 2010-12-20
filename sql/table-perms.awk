#usage: awk -f table-perms.awk all-tables.sql > newfile.sql

tolower($0) ~ /create table/ {

print "grant select,insert,update,delete on " $3 " to 'folkso'@'localhost';"

}