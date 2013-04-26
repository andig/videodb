#!/usr/bin/perl

# Set title
$TITLE="South Park";

# Set genres (see list below for ids)
@GENRES=(4,3);

#  1  Action
#  2  Adventure
#  3  Animation
#  4  Comedy
#  5  Crime
#  6  Documentary
#  7  Drama
#  8  Family
#  9  Fantasy
# 10  Film-Noir
# 11  Horror
# 12  Musical
# 13  Mystery
# 14  Romance
# 15  Sci-Fi
# 16  Short
# 17  Thriller
# 18  War
# 19  Western

$db_server    = "localhost";
$db_user      = "www";
$db_password  = "leech";
$db_database  = "VideoDB";

######################################################################
use DBI;
$dbh = DBI->connect("dbi:mysql:$db_database:$db_server",$db_user,$db_password) || die("Can't connect");

$SELECT = "SELECT id from videodata
            WHERE title LIKE '$TITLE'";
$idr = $dbh->selectall_arrayref($SELECT);

$row=0;
while (defined($idr->[$row][0])){
  my $id = $idr->[$row][0];

  #clear existing genres:
  $dbh->do("DELETE FROM videogenre WHERE id = $id");

  #insert new genres
  foreach my $gid (@GENRES){  
    $dbh->do("INSERT INTO videogenre SET id = $id, gid = $gid");
  }
  $row++;
}
