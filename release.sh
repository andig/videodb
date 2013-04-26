#!/bin/sh

# Script for preparing a release (Windows)
#
# @author Andreas Goetz <cpuidle@gmx.de>
# $Id: release_xp.sh,v 1.8 2013/04/26 15:09:49 andig2 Exp $

TARGET="videodb"
USER=andig2
BUILD="/cygdrive/c/temp"
FTP=$USER@frs.sourceforge.net:/home/frs/project/$TARGET

export CVS_RSH=ssh
export CVSROOT=:ext:$USER@$TARGET.cvs.sourceforge.net:/cvsroot/videodb

RELEASE=$1;
if [ "$RELEASE" = "" ]
then
  echo no release given
  read foo
  exit
fi

echo "Releasing $TARGET $RELEASE ..."
echo $RELEASE > doc/VERSION
cat core/constants.php | perl -e "\$r=\"$RELEASE\"; \$r=~tr#_#.#; foreach \$d (<>) { \$d =~ s#('VERSION',\s*')(.+?)'#\$1\$r'#; print \$d; }" > $BUILD/constants.php
cat $BUILD/constants.php > core/constants.php
cvs -q commit -f -m "release $RELEASE" doc/VERSION

cvs -q commit -f -m "release $RELEASE" core/constants.php
cvs -q tag "v_$RELEASE"

echo "ready to build. hit enter to start"
read foo

cd $BUILD
rm -rf $TARGET/*

cvs -q export -r HEAD $TARGET

#rm -f $TARGET/release.sh
#rm -f $TARGET/makeAPIdoc.sh
rm -f $TARGET/*.sh
rm -rf $TARGET/test
find $TARGET -type f -exec chmod 644 {} \;
find $TARGET -type f -name '*.pl' chmod 755 {} \;
find $TARGET -type d chmod 755 {} \;
chmod -R o+w $TARGET/cache
chmod -R g+w $TARGET/cache
find $TARGET -name '.cvsignore' -exec rm -f {} \;
tar -czvf $TARGET-$RELEASE.tgz $TARGET

echo "build complete hit enter to upload"
read foo

#cat <<. > ftp.txt
#cd uploads 
#put $TARGET-$RELEASE.tgz 
#bye
#.

#sftp -e "cd uploads ; put $TARGET-$RELEASE.tgz ; bye" $FTP
#sftp -i drive/c/data/Personal/public.key -b ftp.txt $FTP

scp -i /cygdrive/c/data/Personal/public.key $TARGET-$RELEASE.tgz $FTP

echo
echo "everything done. visit sourceforge and make the release now"
