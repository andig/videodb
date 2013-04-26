#!/usr/bin/perl

##
# Add video files from local disc
#
# @package videoDB
# @author  Andreas Gohr <a.gohr@web.de>
#
# TODO check paths for correctness
##

# path to md4sum
# Get it from  http://www-tet.ee.tu-berlin.de/solyga/linux/
#
# If you don't want md4 sums (time consuming) set it to a
# blank string
$md4sum = '';    #/usr/local/bin/md4sum';

# If you want md4 sums: in which custom field should it be
# stored? In VideoDB it should have 'ed2k' as type
#$md4field = 'custom2';

# If you want a download link to the file: in which custom
# field should it be stored? In VideoDB it should have
# 'url' as type
#$urlfield = 'custom1';

# path to the eject tool
$eject = '/usr/bin/eject';

# path to mplayer (only version 0.90 was tested, 1.0rc1 works too)
$mplayer = '/usr/bin/mplayer';

# cdrom to use (reads it from commandline)
$device = $ARGV[ 0 ];
$device = "/writer" unless (defined($device));

# do the mount ?
$do_mount = 0;

# remove articles
$remove_article = 1;

# if you use the multiuser feature you may want to give an
# owner of the movies to add here - if you don't need it just
# set it to a blank string 
$owner_id = 1;

# allowed suffixes
$suffix_re = 'avi|ogm|ogg|bin|mpe?g|ra?m|mov|asf|wmv';

# Database stuff
$driver   = "mysql";
$database = "VideoDB";
$prefix	  = "";		// enter your DB prefix like videodb. in here
$hostname = "server";
$user     = "www";
$password = "leech";

################################################################################
use DBI;

#Connect to database
$dsn = "DBI:$driver:database=$database;host=$hostname";
$dbh = DBI->connect($dsn, $user, $password);

#quote this only once:
$owner = $dbh->quote($owner);

if (-f $device)
{
   $plain = $device;
   $plain =~ s#.*/([^/]+)$#\1#i;
   add($device,$plain);
}
else
{

   # mount
   ($do_mount) && system("$eject -t $device");
   ($do_mount) && system("mount $device");

   #work
   &readfiles($device);

   #umount
   ($do_mount) && system("umount $device");
   ($do_mount) && system("$eject $device");
}

#disconnect
$dbh->disconnect();

#
###############################################################################

sub readfiles($)
{
   my $path = $_[ 0 ];

   opendir(ROOT, $path);
   my @files = readdir(ROOT);
   closedir(ROOT);

   my ($file, $ffile);
   foreach $file (@files)
   {
      $ffile = "$path/$file";

      next if ($file =~ /^\.|\.\.$/);    #skip upper dirs
      if (-d $ffile)
      {
         readfiles($ffile);
      }

      if ($ffile =~ /\.($suffix_re)$/i)
      {
         &add($ffile, $file);    #add it
         print STDERR "$ffile\n";
      }
   }
}

sub add($$)
{
   my $ffile = $_[ 0 ];    #full path
   my $file  = $_[ 1 ];    #file only

   # get filestatistics
   my ($dev, $ino, $mode, $nlink, $uid, $gid, $rdev, $size, $atime, $mtime, $ctime, $blksize, $blocks) = stat($ffile);

   # get md4
   my $md4;
   if ($md4sum ne '')
   {
      $md4 = `$md4sum "$ffile"`;
      $md4 =~ s/\s.*$//;
   }

   # get videoinfos
   my ($audio_codec, $video_codec, $video_width, $video_height, $runtime);
   print qq($mplayer -identify  -ao null -vo null -frames 0 "$ffile" 2>/dev/null);
   my @out = `$mplayer -identify  -ao null -vo null -frames 0 "$ffile" 2>/dev/null` if ($mplayer);
   foreach my $line (@out)
   {
      next unless ($line =~ m/^ID_/);
      chomp($line);
      if ($line =~ m/^ID_VIDEO_FORMAT=(.*)/)
      {
         $video_codec = $1;
         $video_codec = 'MPEG1' if ($video_codec eq '0x10000001');
         $video_codec = 'MPEG2' if ($video_codec eq '0x10000002');
         $video_codec = 'MPEG4' if ($video_codec eq 'MPG4');
         #FIXME id's of other mpegs??
         $video_codec = 'DivX3' if ($video_codec eq 'DIV3');
         $video_codec = 'DivX3' if ($video_codec eq 'div3');
         $video_codec = 'DivX4' if ($video_codec eq 'DIV4');
         $video_codec = 'DivX4' if ($video_codec eq 'DIVX');
         $video_codec = 'DivX4' if ($video_codec eq 'divx');
         $video_codec = 'DivX5' if ($video_codec eq 'DX50');
         $video_codec = 'XviD' if ($video_codec eq 'XVID');
         #FIXME aliases of other codecs?
      }
      elsif ($line =~ m/^ID_VIDEO_WIDTH=(.*)/)
      {
         $video_width = $1;
      }
      elsif ($line =~ m/^ID_VIDEO_HEIGHT=(.*)/)
      {
         $video_height = $1;
      }
      elsif ($line =~ m/^ID_AUDIO_CODEC=(.*)/)
      {
         $audio_codec = $1;
         $audio_codec = 'MP3' if ($audio_codec eq 'mad');
         $audio_codec = 'MP3' if ($audio_codec eq 'mp3');
         $audio_codec = 'AC3' if ($audio_codec eq 'a52');
         $audio_codec = 'Vorbis' if ($audio_codec eq 'ffvorbis');
         $audio_codec = 'PCM' if ($audio_codec eq 'pcm');
         $audio_codec = 'WMA2' if ($audio_codec eq 'ffwmav2');
         $audio_codec = 'WMA1' if ($audio_codec eq 'ffwmav1');	# just a guess, needs confirmation
      }
      elsif ($line =~ m/^ID_LENGTH=(.*)/)
      {
         $runtime = $1;
         $runtime = sprintf("%d", $runtime / 60);
      }
   }

   # get titles
   my ($lang, $title, $subtitle, $istv) = &guessnames($file);

   # prepare for inserts
   $file         = $dbh->quote($file);
   $size         = $dbh->quote($size);
   $audio_codec  = $dbh->quote($audio_codec);
   $video_codec  = $dbh->quote($video_codec);
   $video_width  = $dbh->quote($video_width);
   $video_height = $dbh->quote($video_height);
   $runtime      = $dbh->quote($runtime);
   $lang         = $dbh->quote($lang);
   $title        = $dbh->quote($title);
   $subtitle     = $dbh->quote($subtitle);
   $md4          = $dbh->quote($md4);
   $ffile        = $dbh->quote($ffile);

   # insert
   $INSERT = "INSERT INTO ".$prefix."videodata
               SET filename = $file,
                   filesize = $size,
                   audio_codec = $audio_codec,
                   video_codec = $video_codec,
                   video_width = $video_width,
                   video_height = $video_height,
                   language = $lang,
                   title = $title,
                   subtitle = $subtitle,
                   runtime = $runtime,
                   mediatype = 4,
                   istv = $istv,
                   filedate = FROM_UNIXTIME($mtime),
                   created = NOW(),
                   owner_id = $owner_id";
   if ($md4sum ne '')
   {
      $INSERT .= ", $md4field = $md4";
   }

   if ($urlfield ne '')
   {
      $INSERT .= ", $urlfield = $ffile";
   }
   $dbh->do($INSERT);

   #print "$file \n$title - $subtitle\n\n";
}

sub guessnames($)
{
   my $episode = "";
   my $istv    = 0;

   my $file = $_[ 0 ];    #file only

   # try to get language
   # backdrafts: es matches german word, de probably some french stuff
   my $lang = "";
   $lang = "german"  if ($file =~ m/\b(german|deutsch|ger|de)\b/i);
   $lang = "english" if ($file =~ m/\b(english|eng|en)\b/i);
   $lang = "french"  if ($file =~ m/\b(french|français|fra|fr)\b/i);
   $lang = "spanish" if ($file =~ m/\b(spanish|español|es)\b/i);

   # remove add. info
   $file =~ s/\(.*\)//;

   #remove common trash and suffixes
   $file =~ s/(\[[^\]]\]|bin|cd\d|dvd\d|divx|xvid|[ms]?vcd|dvdscr|dvdrip|shareconnector|eselfilme)//gi;
   $file =~ s/\.($suffix_re)$//gi;

   # get episode Number
   if ($file =~ s/(s\d+e\d+)/-/i)
   {
      $episode = $1;
   }
   elsif ($file =~ s/(\d+x\d+)/-/i)
   {
      $episode = $1;
   }

   # change dots to underscores
   $file =~ s/\./_/g;

   # change underscores to spaces
   $file =~ s/_/ /g;

   # split title and subtitle and cleanup
   my @parts = split ("-", $file, 2);
   my $title    = $parts[ 0 ];
   my $subtitle = $parts[ 1 ];
   $title    =~ s/^[\s-]*//g;
   $title    =~ s/[\s-]*$//g;
   $subtitle =~ s/^[\s-]*//g;
   $subtitle =~ s/[\s-]*$//g;

   if ($episode)
   {
      $subtitle = "[$episode] $subtitle";
      $istv     = 1;
   }

   if ($remove_article)
   {
      unless ($title =~ s/^(for the)\b(.*)$/$2, $1/i)
      {
         unless ($title =~ s/^(for a)\b(.*)$/$2, $1/i)
         {
            unless ($title =~ s/^(for)\b(.*)$/$2, $1/i)
            {
               unless ($title =~ s/^(the)\b(.*)$/$2, $1/i)
               {
                  unless ($title =~ s/^(a)\b(.*)$/$2, $1/i)
                  {
                     ($title =~ s/^(der|die|das)\b(.*)$/$2, $1/i);
                  }
               }
            }
         }
      }
   }
   chomp ($title);

   return ($lang, $title, $subtitle, $istv);
}
