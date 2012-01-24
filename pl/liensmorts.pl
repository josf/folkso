#!/usr/local/bin/perl
use strict;
use warnings;
### Time-stamp: <2012-01-06 16:45:41 joseph>

use DBI;
use LWP::UserAgent;

#setup HTTP

my $ua = LWP::UserAgent->new;
$ua->agent("BringOutYourDead");


#setup DB
my $dbh = DBI->connect('DBI:mysql:folksonomie',
                       'folkso-rw',
                       'MathildedelaMole') || 
  die "Echec de connexion mysql: $DBI::errstr";


my $sth = $dbh->prepare('select distinct r.uri_raw, r.id  from resource r'
                       . ' join tagevent te on r.id = te.resource_id '
                       . ' where linkStatusTime < now() - interval 1 day'
                       . ' limit 5000');
$sth->execute();

my $sthUpdate = 
  $dbh->prepare("update resource set linkstatus = ?, linkStatusTime = now() where id = ?");

while (my $res = $sth->fetchrow_hashref) {
    my $request = HTTP::Request->new(GET => $$res{'uri_raw'});
    my $response = $ua->request($request);
    print "Going to check: " . $$res{'uri_raw'} . " ";
    if ($response->is_success) {
        print "OK\n";
        $sthUpdate->execute('OK', $$res{'id'});
    }
    else {
        print "very BAD\n";
        $sthUpdate->execute('NO', $$res{'id'});
    }
}
