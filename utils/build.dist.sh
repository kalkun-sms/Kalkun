#!/bin/bash

# Usage: ./utils/build.dist.sh <branch|tag...|tree-ish>
#
#  This will build archives for specific versions of PHP
#  holding the correct composer dependencies for that PHP version.
#
#  THIS MUST BE CALLED FROM THE ROOT OF THE GIT REPO AS
#   for example :
#       ./utils/build.dist.sh devel
#       ./utils/build.dist.sh v0.8
#


if [ "$1" == "" ]; then
    echo "Usage: ./utils/build.dist.sh <branch|tag...|tree-ish>"
fi

for ver in 7.1.24 5.6; do
    echo "Building '$1' for PHP $ver..."
    ./utils/build.single.dist.sh $ver "$1"
done
