#!/bin/bash

# Usage:
#
#   This file should be called from "build.dist.sh"
#
# ./build.single.dist.sh <PHP_VER> <branch|tag...|tree-ish>


# We add this element to composer.json to fix the version of PHP for which
# to deploy. 7.1.24 is the PHP version on sourceforge where is the demo.
#
# See: https://github.com/composer/composer/issues/10625
#
# Add this to composer.json
#    "config": {
#        "platform": {
#            "php": "7.1.24",
#            "ext-mbstring": "7.1.24"
#        }
#    },

if [ ! -e .git ]; then
    echo "This must be run from the root of the git project."
    exit 1;
fi

if ! command -v jq > /dev/null; then
    echo "jq command is required. Exiting."
    exit 1;
fi

if [ "$2" == "" ]; then
    if [ "$GITHUB_REF_NAME" == "" ]; then
        TREEISH=$(git rev-parse --abbrev-ref HEAD)
    else
        TREEISH=$(git rev-parse --abbrev-ref "$GITHUB_REF_NAME")
    fi
else
    TREEISH="$2"
fi

TARGET_PHP_VERSION="$1"
PROJECT="Kalkun"
GIT_BRANCH=$(git rev-parse --abbrev-ref "$TREEISH")
GIT_VER=$(git describe --tags "$TREEISH")

echo "Building '$GIT_BRANCH' for PHP $TARGET_PHP_VERSION..."

if [ -e dist/build ]; then
    rm -r dist/build
fi

mkdir -p dist/build

git archive --format=tar "$TREEISH" | tar -x -C dist/build

if [ -e dist/build/composer.lock ]; then
    rm -f dist/build/composer.lock
fi

# Add config.platform.php & config.platform."ext-mbstring" to composer.json
jq \
    --arg TARGET_PHP_VERSION "$TARGET_PHP_VERSION" \
    '.config.platform.php = $TARGET_PHP_VERSION | .config.platform."ext-mbstring" = $TARGET_PHP_VERSION' \
    composer.json > dist/build/composer.json

# If we targe PHP5, we have to remove require-dev
if [[ "$TARGET_PHP_VERSION" =~ ^5\..* ]]; then
    tmp=$(mktemp)
    cp dist/build/composer.json "$tmp"
    jq 'del(."require-dev")' "$tmp" > dist/build/composer.json
    rm "$tmp"
fi

# Update package with correct composer dependencies
cd dist/build || exit
composer update --no-dev
cd ..

# Set output dirname/filename
if [[ "$TREEISH" =~ ^v.* ]] || [[ "$TREEISH" =~ ^[0-9].* ]]; then
    # For a tagged version
    FILENAME="${PROJECT}_${GIT_VER}_forPHP${1}"
else
    # For any other branch
    FILENAME="${PROJECT}_${GIT_BRANCH}_${GIT_VER}_forPHP${1}"
fi

echo "$FILENAME" > "build/BUILD.info"

mv build "$FILENAME"

# Create new ZIP & tar.xz archives
zip -qr - "$FILENAME" >  "${FILENAME}.zip"
tar -cJf "${FILENAME}.tar.xz" "$FILENAME"

# Cleanup
rm -r "$FILENAME"
