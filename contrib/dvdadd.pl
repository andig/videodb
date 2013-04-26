#!/usr/bin/perl

#program version
my $VERSION="0.1.0";

# Path to your lsdvd binary (http://acidrip.thirtythreeandathird.net/lsdvd.html)
$lsdvd = '/usr/bin/lsdvd';

# DVD drive to use (reads it from commandline)
$device = $ARGV[ 0 ];
$device = "/dev/cdrom" unless (defined($device));

# if you use the multiuser feature you may want to give an
# owner of the movies to add here - if you don't need it just
# set it to a blank string. It has to be a number for videoDB 2.x
$owner = 3;

# if you want to define what mediatype you are going to add (7=dvd-r 1=dvd)
$mediatype = 7;

# Database stuff
$driver   = "mysql";
$database = "VideoDB";
$hostname = "localhost";
$user     = "www";
$password = "leech";

################################################################################
use DBI;
use Data::Dumper;

#Connect to database
$dsn = "DBI:$driver:database=$database;host=$hostname";
$dbh = DBI->connect($dsn, $user, $password);

#quote this only once:
$owner = $dbh->quote($owner);

#prepare language codes
%lc = preparelc();

#work
&readlsdvd();

#disconnect
$dbh->disconnect();

#
###############################################################################
sub readlsdvd($)
{
   my $output = `$lsdvd -t 1 -a -s -v -p $device`;
   if ($output eq '')
   {
      print STDERR "running lsdvd failed - check pathnames\n";
      exit 1;
   }
   eval($output);

   # prepare for inserts
   my $title        = $dbh->quote($lsdvd{ title });
   my $video_width  = $dbh->quote($lsdvd{ track }[ 0 ]{ width });
   my $video_height = $dbh->quote($lsdvd{ track }[ 0 ]{ height });
   my $runtime      = $dbh->quote(sprintf("%d", $lsdvd{ track }[ 0 ]{ 'length' } / 60));

   # get languages... first is default, the next are written to custom1 and custom2 respectively (add custom3 and four if you want 4 languages
   my $language1 = $dbh->quote($lc{ $lsdvd{ track }[ 0 ]{ audio }[ 0 ]{ langcode } });
   my $language2 = $dbh->quote($lc{ $lsdvd{ track }[ 0 ]{ audio }[ 1 ]{ langcode } });
   my $language3 = $dbh->quote($lc{ $lsdvd{ track }[ 0 ]{ audio }[ 2 ]{ langcode } });

   # reads if PAL or NTSC and how many channels the audio has
   my $audio_codec = $dbh->quote($lsdvd{ track }[ 0 ]{ audio }[ 0 ]{ format } . ' ' . $lsdvd{ track }[ 0 ]{ audio }[ 0 ]{ channels } . ' channels');

   # video codec is either PAL or NTSC ... Aspectratio is 16x9 or 4x3
   my $video_codec = $dbh->quote($lsdvd{ track }[ 0 ]{ format });
   my $aspectratio = $dbh->quote($lsdvd{ track }[ 0 ]{ aspect });

   # just for those who want to use pixels instead of Aspectratio
   my $videowidth  = $dbh->quote($lsdvd{ track }[ 0 ]{ width });
   my $videoheight = $dbh->quote($lsdvd{ track }[ 0 ]{ height });

   # Get subtitles prepared... based on GPL code from acidrip-0.12
   my $this_track = 0;
   my $subtitle   = "Subtitles: ";

   foreach my $this_subp (@{ $lsdvd{ track }[ 0 ]->{ 'subp' } })
   {
      my $subp_ix = $this_subp->{ 'ix' };
      my $label   = $subp_ix . " " . $this_subp->{ 'language' };
      $label .= "\: " . $this_subp->{ 'content' } if $this_subp->{ 'content' } ne "Undefined";
      $subtitle = $subtitle . $label . "\, ";
   }

   # Detect aspect ratio and put into video width and height... comment if you prefer pixel count

   if ($aspectratio = "16/9")
   {
      $video_width  = "16";
      $video_height = "9";
   }
   elsif ($aspectratio = "4/3")
   {
      $video_width  = "4";
      $video_height = "3";
   }

   # insert... remove both custom1 and custom2 if you use them for something else or change them to 
   # something different
   #
   # video_width can be replaced with $videowidth and video_height with $videoheight to achieve pixel
   # count like width = 720, height = 576

   my $INSERT = "INSERT INTO videodata
                  SET title = $title,
                     video_width = $video_width,
                     video_height = $video_height,
                     video_codec = $video_codec,
                     mediatype = $mediatype,
                     created = NOW(),
                     runtime = $runtime,
                     language = $language1,
		     custom1 = $language2,
		     custom2 = $language3,
                     audio_codec = $audio_codec,
		     comment = '$subtitle',
                     owner_id = $owner";

   # comment if you are testing the script... this writes to the database
   $dbh->do($INSERT);

   #print $INSERT;
}

sub preparelc()
{
   my %lc;
   $lc{ 'aa' } = 'afar';
   $lc{ 'ab' } = 'abkhazian';
   $lc{ 'af' } = 'afrikaans';
   $lc{ 'am' } = 'amharic';
   $lc{ 'ar' } = 'arabic';
   $lc{ 'as' } = 'assamese';
   $lc{ 'ay' } = 'aymara';
   $lc{ 'az' } = 'azerbaijani';
   $lc{ 'ba' } = 'bashkir';
   $lc{ 'be' } = 'byelorussian';
   $lc{ 'bg' } = 'bulgarian';
   $lc{ 'bh' } = 'bihari';
   $lc{ 'bi' } = 'bislama';
   $lc{ 'bn' } = 'bengali';
   $lc{ 'bo' } = 'tibetan';
   $lc{ 'br' } = 'breton';
   $lc{ 'ca' } = 'catalan';
   $lc{ 'co' } = 'corsican';
   $lc{ 'cs' } = 'czech';
   $lc{ 'cy' } = 'welsh';
   $lc{ 'da' } = 'danish';
   $lc{ 'de' } = 'german';
   $lc{ 'dz' } = 'bhutani';
   $lc{ 'el' } = 'greek';
   $lc{ 'en' } = 'english';
   $lc{ 'eo' } = 'esperanto';
   $lc{ 'es' } = 'spanish';
   $lc{ 'et' } = 'estonian';
   $lc{ 'eu' } = 'basque';
   $lc{ 'fa' } = 'persian';
   $lc{ 'fi' } = 'finnish';
   $lc{ 'fj' } = 'fiji';
   $lc{ 'fo' } = 'faroese';
   $lc{ 'fr' } = 'french';
   $lc{ 'fy' } = 'frisian';
   $lc{ 'ga' } = 'irish';
   $lc{ 'gd' } = 'gaelic';
   $lc{ 'gl' } = 'galician';
   $lc{ 'gn' } = 'guarani';
   $lc{ 'gu' } = 'gujarati';
   $lc{ 'ha' } = 'hausa';
   $lc{ 'he' } = 'hebrew';
   $lc{ 'hi' } = 'hindi';
   $lc{ 'hr' } = 'croatian';
   $lc{ 'hu' } = 'hungarian';
   $lc{ 'hy' } = 'armenian';
   $lc{ 'ia' } = 'interlingua';
   $lc{ 'id' } = 'indonesian';
   $lc{ 'ie' } = 'interlingue';
   $lc{ 'ik' } = 'inupiak';
   $lc{ 'is' } = 'icelandic';
   $lc{ 'it' } = 'italian';
   $lc{ 'iu' } = 'inuktitut';
   $lc{ 'ja' } = 'japanese';
   $lc{ 'jw' } = 'javanese';
   $lc{ 'ka' } = 'georgian';
   $lc{ 'kk' } = 'kazakh';
   $lc{ 'kl' } = 'greenlandic';
   $lc{ 'km' } = 'cambodian';
   $lc{ 'kn' } = 'kannada';
   $lc{ 'ko' } = 'korean';
   $lc{ 'ks' } = 'kashmiri';
   $lc{ 'ku' } = 'kurdish';
   $lc{ 'ky' } = 'kirghiz';
   $lc{ 'la' } = 'latin';
   $lc{ 'ln' } = 'lingala';
   $lc{ 'lo' } = 'laothian';
   $lc{ 'lt' } = 'lithuanian';
   $lc{ 'lv' } = 'latvian';
   $lc{ 'mg' } = 'malagasy';
   $lc{ 'mi' } = 'maori';
   $lc{ 'mk' } = 'macedonian';
   $lc{ 'ml' } = 'malayalam';
   $lc{ 'mn' } = 'mongolian';
   $lc{ 'mo' } = 'moldavian';
   $lc{ 'mr' } = 'marathi';
   $lc{ 'ms' } = 'malay';
   $lc{ 'mt' } = 'maltese';
   $lc{ 'my' } = 'burmese';
   $lc{ 'na' } = 'nauru';
   $lc{ 'ne' } = 'nepali';
   $lc{ 'nl' } = 'dutch';
   $lc{ 'no' } = 'norwegian';
   $lc{ 'oc' } = 'occitan';
   $lc{ 'om' } = 'oromo';
   $lc{ 'or' } = 'oriya';
   $lc{ 'pa' } = 'punjabi';
   $lc{ 'pl' } = 'polish';
   $lc{ 'ps' } = 'pashto';
   $lc{ 'pt' } = 'portuguese';
   $lc{ 'qu' } = 'quechua';
   $lc{ 'rm' } = 'rhaeto-romance';
   $lc{ 'rn' } = 'kirundi';
   $lc{ 'ro' } = 'romanian';
   $lc{ 'ru' } = 'russian';
   $lc{ 'rw' } = 'kinyarwanda';
   $lc{ 'sa' } = 'sanskrit';
   $lc{ 'sd' } = 'sindhi';
   $lc{ 'sg' } = 'sangho';
   $lc{ 'sh' } = 'serbo-croatian';
   $lc{ 'si' } = 'sinhalese';
   $lc{ 'sk' } = 'slovak';
   $lc{ 'sl' } = 'slovenian';
   $lc{ 'sm' } = 'samoan';
   $lc{ 'sn' } = 'shona';
   $lc{ 'so' } = 'somali';
   $lc{ 'sq' } = 'albanian';
   $lc{ 'sr' } = 'serbian';
   $lc{ 'ss' } = 'siswati';
   $lc{ 'st' } = 'sesotho';
   $lc{ 'su' } = 'sundanese';
   $lc{ 'sv' } = 'swedish';
   $lc{ 'sw' } = 'swahili';
   $lc{ 'ta' } = 'tamil';
   $lc{ 'te' } = 'telugu';
   $lc{ 'tg' } = 'tajik';
   $lc{ 'th' } = 'thai';
   $lc{ 'ti' } = 'tigrinya';
   $lc{ 'tk' } = 'turkmen';
   $lc{ 'tl' } = 'tagalog';
   $lc{ 'tn' } = 'setswana';
   $lc{ 'to' } = 'tonga';
   $lc{ 'tr' } = 'turkish';
   $lc{ 'ts' } = 'tsonga';
   $lc{ 'tt' } = 'tatar';
   $lc{ 'tw' } = 'twi';
   $lc{ 'ug' } = 'uighur';
   $lc{ 'uk' } = 'ukrainian';
   $lc{ 'ur' } = 'urdu';
   $lc{ 'uz' } = 'uzbek';
   $lc{ 'vi' } = 'vietnamese';
   $lc{ 'vo' } = 'volapuk';
   $lc{ 'wo' } = 'wolof';
   $lc{ 'xh' } = 'xhosa';
   $lc{ 'yi' } = 'yiddish';
   $lc{ 'yo' } = 'yoruba';
   $lc{ 'za' } = 'zhuang';
   $lc{ 'zh' } = 'chinese';
   $lc{ 'zu' } = 'zulu';

   return %lc;
}

__END__

=head1 NAME

dvdadd - reads DVD Video Data and writes it to videoDB

=head1 SYNOPSIS

 reads DVD Video Data and writes it to videoDB

=head1 DESCRIPTION

 reads DVD Video Data and writes it to videoDB using Perl and lsdvd. Linux or Unix is required, not tested on MS Windows.

=head1 SEE ALSO

 videoDB http://videodb.sf.net
 lsdvd 0.10 http://acidrip.thirtythreeandathird.net/lsdvd.html
 perl http://perl.org

=head1 AUTHOR

 Elkin Fricke, videoDB DevTeam.
 
=head1 LICENSE

  VideoDB is released under the GNU General Public License (GPL)
  See COPYING for more Info

  VideoDB comes with the Smarty Template Engine
  Smarty is released under the GNU Lesser General Public License (LGPL)
  See COPYING.lib in the smarty directory for more Info

=cut

