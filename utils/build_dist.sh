#!/bin/bash

# Usage: ./utils/build_dist.sh [branch|tag|tree-ish]
#
#  This will build archives for specific versions of PHP
#  holding the correct composer dependencies for that PHP version.
#
#  If not argument is given, it uses the current branch, or
#  $GITHUB_REF_NAME if available.
#
#  THIS MUST BE CALLED FROM THE ROOT OF THE GIT REPO LIKE THIS.
#   for example :
#       ./utils/build_dist.sh
#       ./utils/build_dist.sh devel
#       ./utils/build_dist.sh v0.8
#

if [ ! -e .git ]; then
    echo "This must be run from the root of the git project."
    exit 1;
fi

if [ "$1" == "" ]; then
    if [ "$GITHUB_REF_NAME" == "" ]; then
        TREEISH=$(git rev-parse --abbrev-ref HEAD)
    else
        TREEISH=$(git rev-parse --abbrev-ref "$GITHUB_REF_NAME")
    fi
else
    TREEISH="$1"
fi

for ver in 7.1.24 5.6; do
    ./utils/build_single_dist.sh $ver "$TREEISH"
done
