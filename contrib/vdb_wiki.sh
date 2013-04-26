#!/bin/sh

#
# Extract documentation from wiki to include in release package
#
# @package Release
# @author  Andreas Gohr		<a.gohr@web.de>
# @author  Andreas Goetz	<cpuidle@gmx.de>
# @link	   http://www.splitbrain.org/dokuwiki/vdb:videodb
# @version $Id: vdb_wiki.sh,v 1.1 2004/08/11 10:09:13 andig2 Exp $
#

# remove existing doku
rm -f *.html

# get doku from dokuwiki
wget --level 2 -r -np -nc -nd -E -k -A 'vdb*' -R '*\?*' http://www.splitbrain.org/dokuwiki/vdb:videodb

# delete files
for x in `ls *@*`
do
  rm $x
done

# fix filenames
for x in `ls *%3A*`
do
  mv $x `echo $x|tr -s %3A _`
done

#cp ../Copy\ of\ vdb/* .

# fix html
for x in `ls *.html`
do
  # move to tempfile
  cp $x $x.tmp

  # add new header
  cat >$x <<EOF
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
  <title>VideoDB - Documentation</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
</head>
<body>
  <a href="vdb_videodb.html">Table of Contents</a>
<!-- begin content --> 
EOF

  # add fixed content
  cat $x.tmp | \
  perl -e '$foo=join("",<>);
           $foo=~s/vdb(:|%3A)/vdb_/gs;
           $foo=~m/<!-- wikipage start -->(.*)<!-- foocache/s;
           print $1' \
  >> $x


  # add new footer
  cat >>$x <<EOF
<!-- end content -->    
</body>
</html>
EOF

  # remove tempfile
  rm -f $x.tmp
done

