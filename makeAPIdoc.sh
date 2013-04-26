#!/bin/bash

TITLE="VideoDB"
PATH_PROJECT=$PWD
PATH_PHPDOC=~/tools/phpDocumentor-1.2.2/phpdoc
PATH_DOCS=$PWD/doc/API
IGNORE=smarty
OUTPUTFORMAT=HTML
CONVERTER=frames
TEMPLATE=default
PRIVATE=off

FILES=`find . -name '*.php' |grep -v smarty |xargs|sed -e 's/ /,/g'`

# make documentation
$PATH_PHPDOC -f $FILES  \
-t $PATH_DOCS -ti "$TITLE" \
-i "$IGNORE" -o $OUTPUTFORMAT:$CONVERTER:$TEMPLATE -pp $PRIVATE




# vim: set expandtab :
