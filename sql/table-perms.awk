#usage: awk -f table-perms.awk user=folkso all-tables.sql > newfile.sql
tolower($0) ~ /create table/ {
    if (user == "tester_dude") {
        print "grant select,insert,update,delete,drop on " $3 " to '" user "'@'localhost';"
    }
    else {
        print "grant select,insert,update,delete on " $3 " to '" user "'@'localhost';"
    }

}