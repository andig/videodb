#!/usr/bin/perl

use DBI;

$db_server    = "localhost";
$db_user      = "www";
$db_password  = "leech";
$db_database  = "VideoDB";

$dbh = DBI->connect("dbi:mysql:$db_database:$db_server",$db_user,$db_password) || die("Can't connect");


$SELECT = "SELECT filename, filesize, diskid
             FROM videodata 
         ORDER BY filename";

$result = $dbh->selectall_arrayref($SELECT);

print "DiskID\tSize\t\tFilename\n";
print "-"x74;
print "\n";

$row=0;
while (defined($result->[$row][0])){
  printf("%s\t",$result->[$row][2]);
  printf("%3.2f MB\t",($result->[$row][1]/(1024*1024)));
  printf("%s\n",$result->[$row][0]);

  $row++;
}
