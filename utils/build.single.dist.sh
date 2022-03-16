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

TARGET_PHP_VERSION="$1"

if [ -e dist/build ]; then
    rm -r dist/build
fi

mkdir -p dist/build

git archive --format=tar "$2" | tar -x -C dist/build

rm dist/build/composer.lock

# Add config.platform.php & config.platform."ext-mbstring" to composer.json
jq \
    --arg TARGET_PHP_VERSION "$TARGET_PHP_VERSION" \
    '.config.platform.php = $TARGET_PHP_VERSION | .config.platform."ext-mbstring" = $TARGET_PHP_VERSION' \
    composer.json > dist/build/composer.json

# If we targe PHP5, we have to remove require-dev
if [[ "$TARGET_PHP_VERSION" =~ ^5\..* ]]; then
    tmp=$(mktemp)
    cp dist/build/composer.json $tmp
    jq 'del(."require-dev")' $tmp > dist/build/composer.json
    rm $tmp
fi

# Update package with correct composer dependencies
cd dist/build
composer update --no-dev

# Set output dirname/filename
cd ..
if [[ "$2" =~ ^v.* ]] || [[ "$2" =~ ^[0-9].* ]]; then
    # For a tagged version
    FILENAME="kalkun_$2_PHP${1}"
else
    # For any other branch
    FILENAME="kalkun_$2_$(date +%Y-%m-%d-%H%M)_PHP${1}"
fi

mv build "$FILENAME"

# Create ZIP & tar.xz archives
zip -qr "${FILENAME}.zip" "$FILENAME"
tar -cJf "${FILENAME}.tar.xz" "$FILENAME"

# Cleanup
rm -r "$FILENAME"
