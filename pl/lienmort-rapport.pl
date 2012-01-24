#!/usr/local/bin/perl
use strict;
use warnings;
use utf8;
### Time-stamp: <2012-01-06 15:38:57 joseph>

use DBI;

#setup DB
my $dbh = DBI->connect('DBI:mysql:folksonomie',
                       'folkso-rw',
                       'MathildedelaMole') || 
  die "Echec de connexion mysql: $DBI::errstr";


my $sth = $dbh->prepare("select uri_raw, title from resource where linkstatus = 'NO'");
$sth->execute();

my $head = <<END;

<html><head><title>Liens morts</title></head>
<body>
<h1>Liens morts détectés dans la base folksonomies</h1>
<p>NB: il s'agit seulement des ressources déjà taggés.</p>
<table>

END


open FH, ">:utf8", "deadlinks.html";

print FH $head;
while (my $res = $sth->fetchrow_hashref) {
    print FH "<tr><td>\n";
    print FH $$res{'uri_raw'} . "</td><td> " . $$res{'title'} . "</td></tr>\n";
}


my $foot = <<END;

</table>
</body>
</html>

END

print FH $foot;


