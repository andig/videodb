#!/usr/bin/perl
use DBI;

#DB-Connection
$db_server    = "localhost";
$db_user      = "www";
$db_password  = "leech";
$db_database  = "VideoDB";

#output directory
$outdir = '/ftp/moviez/VideoDB/';

#imgcache
$imgcache = 'http://xerxes/videodb/imgcache';

#showurl
$showurl = 'http://xerxes/videodb/show.php';

#lynx
$lynx = '/usr/bin/lynx'; 

###############################################################################
#okay lets go

#delete old stuff
system("rm -rf $outdir/*");

#connect
$dbh = DBI->connect("dbi:mysql:$db_database:$db_server",$db_user,$db_password) || die("Can't connect");

#unseen nontv
$out = "$outdir/unseen";
mkdir($out) unless(-e $out);
$SELECT = "SELECT id, title , subtitle
             FROM videodata
            WHERE seen = 0
              AND istv = 0";
$result = $dbh->selectall_arrayref($SELECT);
$row=0;
while (defined($result->[$row][0])){
  &show($result->[$row][0],$result->[$row][1],$result->[$row][2],$out);
  $row++;
}

#unseen nontv
$out = "$outdir/unseen/tv";
mkdir($out) unless(-e $out);
$SELECT = "SELECT id, title , subtitle
             FROM videodata
            WHERE seen = 0
              AND istv = 1";
$result = $dbh->selectall_arrayref($SELECT);
$row=0;
while (defined($result->[$row][0])){
  &show($result->[$row][0],$result->[$row][1],$result->[$row][2],$out);
  $row++;
}

#seen nontv
$out = "$outdir/seen";
mkdir($out) unless(-e $out);
$SELECT = "SELECT id, title , subtitle
             FROM videodata
            WHERE seen = 1
              AND istv = 0";
$result = $dbh->selectall_arrayref($SELECT);
$row=0;
while (defined($result->[$row][0])){
  &show($result->[$row][0],$result->[$row][1],$result->[$row][2],$out);
  $row++;
}

#unseen tv
$out = "$outdir/seen/tv";
mkdir($out) unless(-e $out);
$SELECT = "SELECT id, title , subtitle
             FROM videodata
            WHERE seen = 1
              AND istv = 1";
$result = $dbh->selectall_arrayref($SELECT);
$row=0;
while (defined($result->[$row][0])){
  &show($result->[$row][0],$result->[$row][1],$result->[$row][2],$out);
  $row++;
}

#all nontv
$out = "$outdir";
mkdir($out) unless(-e $out);
$SELECT = "SELECT id, title , subtitle
             FROM videodata
            WHERE istv = 0";
$result = $dbh->selectall_arrayref($SELECT);
$row=0;
while (defined($result->[$row][0])){
  &show($result->[$row][0],$result->[$row][1],$result->[$row][2],$out);
  $row++;
}

#all tv
$out = "$outdir/tv";
mkdir($out) unless(-e $out);
$SELECT = "SELECT id, title , subtitle
             FROM videodata
            WHERE istv = 1";
$result = $dbh->selectall_arrayref($SELECT);
$row=0;
while (defined($result->[$row][0])){
  &show($result->[$row][0],$result->[$row][1],$result->[$row][2],$out);
  $row++;
}

###############################################################################
sub show($$$$){
  my $id       = $_[0];
  my $title    = $_[1];
  my $subtitle = $_[2];
  my $out      = $_[3];
  print '.';

  my $name;
  if ($subtitle ne ''){
    $name     = "$title - $subtitle.html";
  }else{
    $name = "$title.html";
  }

  my $output = `$lynx --dump --source '$showurl?id=$id'`;
#  print "$lynx --dump '$showurl?id=$id'\n";


  $output =~ m/<!-- content begin -->(.*)<!-- content end -->/is;
  $output = $1;
  $output =~ s/SRC="imgcache/SRC="$imgcache/is;

  $output =~ s/<span CLASS="show_title">(.*?)<\/span>/<H1>$1/is;
  $output =~ s/<span CLASS="show_subtitle">(.*?)<\/span>/$1<\/H1>/is;

  open (FILE,">$out/$name") || die("file open '$out/$name' failed");
  print FILE $output;
  close FILE;
}
