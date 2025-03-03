#!/bin/bash

set -e

TESTSDIR="tests/"
ENABLE_MONKEY_PATCHING=false

mkdir -p "${TESTSDIR}"
php vendor/kenjis/ci-phpunit-test/install.php -t "${TESTSDIR}" --from-composer
# Workaround a bug in phpunit < 7 where the absence of "${TESTSDIR}"_ci_phpunit_test/ makes it fail
# with strpos(): Empty needle in vendor/phpunit/php-file-iterator/src/Iterator.php
if ! vendor/bin/phpunit --atleast-version 7; then
  mkdir -vp "${TESTSDIR}"_ci_phpunit_test
  # Below the alternative is to remove the culprit line from phpunit.xml
  #sed -i -e "/<exclude>.\/_ci_phpunit_test\/<\/exclude>/ d" "${TESTSDIR}"phpunit.xml
fi

# Workaround bug in installer of ci-phpunit-test which doesn't set the paths correctly
sed -i "s#'../../vendor/codeigniter/framework/system'#'../vendor/codeigniter/framework/system'#" "${TESTSDIR}"/Bootstrap.php
sed -i "s#'../../application'#'../application'#" "${TESTSDIR}"/Bootstrap.php
sed -i "/define('FCPATH'/ s#'/../..'#'/..'#" "${TESTSDIR}"/Bootstrap.php

for dir in controllers models views libraries helpers hooks views/errors; do
  sed -i "s#>../$dir<#>../application/$dir<#" "${TESTSDIR}/phpunit.xml"
done

# Uncomment the monkey patcher function. This will search the line matching "Enabling Monkey Patching"
# then search the next "/*", delete that line, search the next "*/" and delete the line, write, and quit.
if [ "$ENABLE_MONKEY_PATCHING" = "true" ]; then
  ed -s "${TESTSDIR}"Bootstrap.php <<EOF
/Enabling Monkey Patching/
/^\/\*$/
n
d
/^\*\/$/
n
d
w
q
EOF
fi

rm "${TESTSDIR}"controllers/Welcome_test.php

# exlude the full application/view dir from coverage, otherwise coverage would fail.
# See: https://github.com/kenjis/ci-phpunit-test/issues/412
sed -i -e 's|<directory suffix=".php">../application/views/errors</directory>|<directory suffix=".php">../application/views</directory>|' "${TESTSDIR}"phpunit.xml

# the void return type of setUp() methods in phpunit (required since phpunit8) isn't supported
# with phpunit <= 6. For these, we remove the ": void" part of the tests
if [ "$(composer show phpunit/phpunit | grep "^versions : " | rev | cut -d " " -f 1 | rev | cut -d . -f 1)" -le 6 ]; then
  for func in setUp tearDown setUpBeforeClass tearDownAfterClass; do
    sed -i "/ function $func()/ s/:\s*void$//" "${TESTSDIR}"*/*_test.php
  done
fi

# Check that CI3 patch is applied. Patch fixes issue with escape char of DB queries when switching DB engine, in the tests.
if [ -d vendor/codeigniter/framework ] &&  ! grep -q "// Reinitialize static variable when switching to another database driver." vendor/codeigniter/framework/system/database/DB_driver.php; then
  PATCH_PATH="patches/Codeigniter_Framework/v3.1.13/20-reinit_static_variable_when_switching_db_in_tests.patch"
  echo "ERROR: So that the test suite can switch between DB without issues"
  echo "you must apply patch located at $PATCH_PATH"
  exit 1;
fi
