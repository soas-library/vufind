#!/bin/bash

#####################################################
# Build java command
#####################################################
if [ "$JAVA_HOME" ]
then
  JAVA="$JAVA_HOME/bin/java"
else
  JAVA="java"
fi

if [ -z "$VUFIND_HOME" ]
then
  VUFIND_HOME=`dirname $0`
fi

if [ -z "$SOLR_HOME" ]
then
  SOLR_HOME="$VUFIND_HOME/solr/vufind"
fi

export VUFIND_HOME=/usr/local/vufind
# SCB
export LC_COLLATE="sb_SB.UTF-8"
# SCB

set -e
set -x

cd "`dirname $0`/import"
CLASSPATH="browse-indexing.jar:${SOLR_HOME}/jars/*:${SOLR_HOME}/../vendor/contrib/analysis-extras/lib/*:${SOLR_HOME}/../vendor/server/solr-webapp/webapp/WEB-INF/lib/*"

# make index work with replicated index
# current index is stored in the last line of index.properties
function locate_index
{
    local targetVar=$1
    local indexDir=$2
    # default value
    local subDir="index"

    if [ -e $indexDir/index.properties ]
    then
        # read it into an array
        readarray farr < $indexDir/index.properties
        # get the last line
        indexline="${farr[${#farr[@]}-1]}"
        # parse the lastline to just get the filename
        subDir=`echo $indexline | sed s/index=//`
    fi

    eval $targetVar="$indexDir/$subDir"
}

locate_index "bib_index" "${SOLR_HOME}/biblio"
locate_index "auth_index" "${SOLR_HOME}/authority"
index_dir="${SOLR_HOME}/alphabetical_browse"

mkdir -p "$index_dir"

function build_browse
{
    browse=$1
    field=$2
    skip_authority=$3

    extra_jvm_opts=$4

# SCB
    #if [ "$skip_authority" = "1" ]; then
    #    $JAVA ${extra_jvm_opts} -Dfile.encoding="UTF-8" -Dfield.preferred=heading -Dfield.insteadof=use_for -cp $CLASSPATH PrintBrowseHeadings "$bib_index" "$field" "${browse}.tmp"
    #else
    #    $JAVA ${extra_jvm_opts} -Dfile.encoding="UTF-8" -Dfield.preferred=heading -Dfield.insteadof=use_for -cp $CLASSPATH PrintBrowseHeadings "$bib_index" "$field" "$auth_index" "${browse}.tmp"
    #fi

#        $JAVA ${extra_jvm_opts} -Dfile.encoding="UTF-8" -Dfield.preferred=heading -Dfield.insteadof=use_for -cp $CLASSPATH PrintBrowseHeadings "$bib_index" "$field" "${browse}.tmp"

        $JAVA ${extra_jvm_opts} -Dfield.preferred=heading -Dfield.insteadof=use_for -cp $CLASSPATH PrintBrowseHeadings "$bib_index" "$field" "${browse}.tmp"
# SCB

    sort -T /var/tmp -u -t$'\1' -k1 "${browse}.tmp" -o "sorted-${browse}.tmp"
    $JAVA -Dfile.encoding="UTF-8" -cp $CLASSPATH CreateBrowseSQLite "sorted-${browse}.tmp" "${browse}_browse.db"

    rm -f *.tmp

    mv "${browse}_browse.db" "$index_dir/${browse}_browse.db-updated"
    touch "$index_dir/${browse}_browse.db-ready"
}
# SCB
#build_browse "hierarchy" "hierarchy_browse"
#build_browse "title" "title_fullStr" 1 "-Dbibleech=StoredFieldLeech -Dsortfield=title_sort -Dvaluefield=title_fullStr"
#build_browse "topic" "topic_browse"
#build_browse "author" "author_browse"
#build_browse "lcc" "callnumber-raw" 1 "-Dbrowse.normalizer=org.vufind.util.LCCallNormalizer"
#build_browse "dewey" "dewey-raw" 1 "-Dbrowse.normalizer=org.vufind.util.DeweyCallNormalizer"
build_browse "callnumber" "callnumber" 1 "-Dbibleech=StoredFieldLeech -Dsortfield=callnumber -Dvaluefield=callnumber"
#build_browse "callnumber" "callnumber-sort" 1 "-Dbibleech=StoredFieldLeech -Dsortfield=callnumber-sort -Dvaluefield=callnumber"
#build_browse "hierarchy_top_title" "hierarchy_top_title_browse"
# SCB