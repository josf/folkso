#usage: awk -f procedure-perms.awk procedures.sql > newfile.sql

BEGIN { FS="[ (]"}

tolower($0) ~ /create (function|procedure)/ {
    print "grant execute on " tolower($2) " " $3 " to 'folkso'@'localhost';"
}




