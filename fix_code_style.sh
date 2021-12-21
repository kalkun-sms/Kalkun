#!/bin/bash

# Usage:
#   ./fix_code_style.sh git-co
#
#         Run & commit each php-cs-fix/phpcbf config individually
#
#   ./fix_code_style.sh git-diff
#
#         Run the fix and save diff to file. This won't commit anything.
#         The branch has to be clean before proceeding and changes will be restored.
#
#   ./fix_code_style.sh strict
#
#         Only show the changes that should be done to have strict_comparison
#         operator everywhere.
#
#   ./fix_code_style.sh
#
#         Apply the fixes to the project (changes won't be commited)
#

if [[ "$1" == "git-co" ]]; then
  DO_GIT_COMMIT=1
  DO_GIT_DIFF=0
elif [[ "$1" == "git-diff" ]]; then
  if [[ $(git diff --stat) != '' ]]; then
    echo "git is dirty. Stopping."
    exit
  fi
  DO_GIT_COMMIT=0
  DO_GIT_DIFF=1
elif [[ "$1" == "strict" ]]; then
  STRICT_COMPARISON=1
  DO_GIT_COMMIT=0
  DO_GIT_DIFF=0
else
  DO_GIT_COMMIT=0
  DO_GIT_DIFF=0
fi

CS_FIXER_CONF_DIR=.php-cs-fixer-configs
CS_RULESSET_DIR=.phpcs-rules
TMPDIR=$(mktemp -d)

if [[ -v GITHUB_ACTIONS ]]; then
    DIFF_OUTPUT_DIR="."
else
    DIFF_OUTPUT_DIR="$TMPDIR"
fi

############### Check for strict STRICT_COMPARISON operator #########

if [[ "$STRICT_COMPARISON" == "1" ]]; then
    vendor/bin/php-cs-fixer fix -v --show-progress=dots --allow-risky=yes --dry-run --diff --config "$CS_FIXER_CONF_DIR/php-cs-fixer-5-strict_comparison.php" > "$DIFF_OUTPUT_DIR/code_style_check-strict_comparison.diff"
    EXIT_CODE=$?
    if [ $EXIT_CODE -eq 8 ] || [ $EXIT_CODE -eq 4 ]; then
        # 4 - Some files have invalid syntax (only in dry-run mode).
        # 8 - Some files need fixing (only in dry-run mode).
        if [[ -v GITHUB_ACTIONS ]]; then
            head -n200 "$DIFF_OUTPUT_DIR/code_style_check-strict_comparison.diff"
        fi
        echo
        echo "_____________________"
        echo " CODE NEEDS FIXING ! "
        echo "‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾"
        echo "Some code doesn't use strict_comparison operators."
        echo "Please double-check and apply the required changes."
        echo
        echo "Full diff file is '$DIFF_OUTPUT_DIR/code_style_check-strict_comparison.diff'"
        if [[ -v GITHUB_ACTIONS ]]; then
            echo "The first 200 lines of the diff are shown above."
            echo "You can find the full diff in the artifacts."
        fi
    else
        echo
        echo "_______________________________________________________"
        echo " Code uses strict comparison operators where expected. "
        echo "‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾"
        rm "$DIFF_OUTPUT_DIR/code_style_check-strict_comparison.diff"
    fi
    echo "php-cs-fixer exited with exit-code: $EXIT_CODE"
    exit $EXIT_CODE
fi


############### Beautify HTML/JS/CSS of *.php in views #########
# This requires js-beautify/html-beautify
# On debian, package is 'node-js-beautify'
# This in voluntarily placed before PHP check

if command -v html-beautify >/dev/null ; then
    echo "Beautifying HTML/JS/CSS style..."
    while IFS= read -r -d '' file; do
        html-beautify \
            --replace \
            --indent-with-tabs \
            --templating=php \
            --end-with-newline=true \
            --brace-style=collapse \
            --newline-between-rules=false \
            --space-around-combinator=true \
            --space-around-selector-separator=true \
            --indent-scripts=normal \
            "$file"
    done <  <(find application/views/js_init application/views/main -name "*.php" -print0)

    PLUGIN_VIEWS=$(find application/plugins -type d -name views)
    while IFS= read -r -d '' file; do
        html-beautify \
            --replace \
            --indent-with-tabs \
            --templating=php \
            --end-with-newline=true \
            --brace-style=collapse \
            --newline-between-rules=false \
            --space-around-combinator=true \
            --space-around-selector-separator=true \
            --indent-scripts=normal \
            "$file"
    done <  <(find $PLUGIN_VIEWS -name "*.php" -print0)
fi
if [ $DO_GIT_COMMIT -eq 1 ]; then
    git add application &&
    git commit -m "[AUTO: html-beautify] Beautify HTML/JS/CSS in views"
fi


# Configure phpcs (add CodeIgniter standard to phpcs)
vendor/bin/phpcs --config-set installed_paths vendor/ise/php-codingstandards-codeigniter/CodeIgniter

############ Various change to harmonize code  #########

# Remove old useless CI1/CI2 end of document comments
grep -lr "/\* Location" application/ | while IFS= read -r file; do
    sed -i "/\* Location/d" "$file"
done
grep -lr "/\* End of file" application/ | while IFS= read -r file; do
    sed -i "/\* End of file/d" "$file"
done
if [ $DO_GIT_COMMIT -eq 1 ]; then
    git add application &&
    git commit -m "[AUTO] Remove trailing CI1-CI2 comments

These are useless in CI3 and coding style doesn't mention them anymore
(as opposed to CI2 user guide in which they were mentionned)."
fi

# Add missing index.html files in each directory
# https://codeigniter.com/userguide3/general/security.html?highlight=index%20html#hide-your-files
# CodeIgniter will have an index.html file in all of its directories in an attempt
# to hide some of this data, but have it in mind that this is not enough to prevent a serious attacker.
#find application/ -type d -exec cp -a vendor/codeigniter/framework/application/index.html '{}' \;
find application/ -type d '!' -exec test -e "{}/index.html" ';' -exec cp -a vendor/codeigniter/framework/application/index.html '{}' \; &&
find media/ -type d '!' -exec test -e "{}/index.html" ';' -exec cp -a vendor/codeigniter/framework/application/index.html '{}' \; &&
if [ $DO_GIT_COMMIT -eq 1 ]; then
    git add "application/**index.html" &&
    git add "media/**index.html" &&
    git commit -m "[AUTO] Add missing protective index.html"
fi

# Replace old formatted php header to keep '<?php' opening tag alone on first line
OLD_CI_HEADER=$(find application/ -name "*.php" -exec head -n1 '{}' \; | grep BASEPATH | grep "^<?php" | sort -u)
if [[ "$OLD_CI_HEADER" != "" ]] ; then
    while IFS= read -r -d '' file; do
        while IFS= read -r line; do
            sed -i "s,$line,<?php\ndefined('BASEPATH') OR exit('No direct script access allowed');," "$file"
        done <<< "$OLD_CI_HEADER"
    done <   <(find application -name "*.php" -print0)
    if [ $DO_GIT_COMMIT -eq 1 ]; then
        git add "application/" &&
        git commit -m "[AUTO] Replace old formatted php header to keep opening tag alone on first line

This also makes the header in line with the CI3 standards."
    fi
fi
unset OLD_CI_HEADER

############# PHP CS Fixer (with a little bit of PHP_CodeSniffer) #############

if [ $DO_GIT_DIFF -eq 0 ]; then

    # Correct spaces, end of line, tabs, indentation...
    vendor/bin/php-cs-fixer fix -v --show-progress=dots --config "$CS_FIXER_CONF_DIR/php-cs-fixer-0-spaces.php" &&
    if [ $DO_GIT_COMMIT -eq 1 ]; then
        git add application &&
        git commit -m "[AUTO: PHP-CS-Fixer] spaces...

encoding
indentation_type
line_ending
no_trailing_whitespace
no_whitespace_in_blank_line
single_blank_line_at_eof
no_trailing_whitespace_in_comment
array_indentation
no_whitespace_before_comma_in_array
whitespace_after_comma_in_array
trim_array_spaces
no_spaces_around_offset"
    fi

     # linebreak_after_opening_tag
    vendor/bin/php-cs-fixer fix -v --show-progress=dots --config "$CS_FIXER_CONF_DIR/php-cs-fixer-9-linebreak_after_opening_tag.php" &&
     # no_closing_tag
    vendor/bin/php-cs-fixer fix -v --show-progress=dots --config "$CS_FIXER_CONF_DIR/php-cs-fixer-8-no_closing_tag.php" &&
    if [ $DO_GIT_COMMIT -eq 1 ]; then
        git add application &&
        git commit -m "[AUTO: PHP-CS-Fixer] no_closing_tag, linebreak_after_opening_tag"
    fi

    # single_quote
    vendor/bin/php-cs-fixer fix -v --show-progress=dots --config "$CS_FIXER_CONF_DIR/php-cs-fixer-1-single_quote.php" &&
    if [ $DO_GIT_COMMIT -eq 1 ]; then
        git add application &&
        git commit -m "[AUTO: PHP-CS-Fixer] single_quote"
    fi

    # method_argument_space
    vendor/bin/php-cs-fixer fix -v --show-progress=dots --config "$CS_FIXER_CONF_DIR/php-cs-fixer-6-method_argument_space.php" &&
    if [ $DO_GIT_COMMIT -eq 1 ]; then
        git add application &&
        git commit -m "[AUTO: PHP-CS-Fixer] method_argument_space

'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline']"
    fi

    # explicit_string_variable
    vendor/bin/php-cs-fixer fix -v --show-progress=dots --config "$CS_FIXER_CONF_DIR/php-cs-fixer-7-explicit_string_variable.php" &&
    if [ $DO_GIT_COMMIT -eq 1 ]; then
        git add application &&
        git commit -m "[AUTO: PHP-CS-Fixer] explicit_string_variable"
    fi

    # operator spacing (except some config files)
    vendor/bin/php-cs-fixer fix -v --show-progress=dots --config "$CS_FIXER_CONF_DIR/php-cs-fixer-11-operator.php" &&
    if [ $DO_GIT_COMMIT -eq 1 ]; then
        git add application &&
        git commit -m "[AUTO: PHP-CS-Fixer] operator & parenthesis spacing

'not_operator_with_space' => true,
'no_spaces_inside_parenthesis' => true,
    We use 'no_spaces_inside_parenthesis' with not_operator_with_space
    to be sure notation remains correct for CI3 style guidelines

'object_operator_without_whitespace' => true,
'operator_linebreak' => [ 'only_booleans' => true ],
'standardize_not_equals' => true,
'ternary_operator_spaces' => true,
'unary_operator_spaces' => true,
'binary_operator_spaces' => true,"
    fi

    # constant_case  TRUE, FALSE
    vendor/bin/php-cs-fixer fix -v --show-progress=dots --config "$CS_FIXER_CONF_DIR/php-cs-fixer-3-constant_case.php" &&
    if [ $DO_GIT_COMMIT -eq 1 ]; then
        git add application &&
        git commit -m "[AUTO: PHP-CS-Fixer] constant_case

'constant_case' => [ 'case' => 'upper'], //TRUE, FALSE..."
    fi

    # single_line_comment_style
    vendor/bin/php-cs-fixer fix -v --show-progress=dots --config "$CS_FIXER_CONF_DIR/php-cs-fixer-10-single_line_comment_style.php" &&
    if [ $DO_GIT_COMMIT -eq 1 ]; then
        git add application &&
        git commit -m "[AUTO: PHP-CS-Fixer] single_line_comment_style"
    fi

    # no_alternative_syntax (EXCEPT views)
    vendor/bin/php-cs-fixer fix -v --show-progress=dots --config "$CS_FIXER_CONF_DIR/php-cs-fixer-12-no_alternative_syntax.php" &&
    # braces & control_structure_continuation_position & no_alternative_syntax
    vendor/bin/php-cs-fixer fix -v --show-progress=dots --config "$CS_FIXER_CONF_DIR/php-cs-fixer-4-braces.php" &&
    # Run phpcs immediately with sniffs=Generic.Classes.OpeningBraceSameLine to fix
    # php-cs-fixer not inline with what we want
    vendor/bin/phpcbf -p --standard="$CS_RULESSET_DIR/ruleset.xml" --sniffs=Generic.Classes.OpeningBraceSameLine
    if [ $DO_GIT_COMMIT -eq 1 ]; then
        git add application &&
        git commit -m "[AUTO: PHP-CS-Fixer] braces, no_alt_syntax and related...

PHP-CS-Fixer:
'braces' => [ 'position_after_control_structures' => 'next'],
'control_structure_continuation_position' => [ 'position' => 'next_line'],
'no_alternative_syntax' => true,

PHP Code Sniffer:
Restore opening braces on same line as Class definition"
    fi

fi


# Rerun php-cs-fixer with all fixes + the fix on classes opening braces
vendor/bin/php-cs-fixer fix -v --show-progress=dots &&
vendor/bin/phpcbf -p --standard="$CS_RULESSET_DIR/ruleset.xml" --sniffs=Generic.Classes.OpeningBraceSameLine
if [ $DO_GIT_COMMIT -eq 1 ]; then
    git add application &&
    git commit -m "[AUTO: PHP-CS-Fixer] EMPTY?"
fi


# Process also the scripts directory
vendor/bin/php-cs-fixer fix -v --show-progress=dots scripts &&
vendor/bin/phpcbf -p --standard="$CS_RULESSET_DIR/ruleset.xml" --sniffs=Generic.Classes.OpeningBraceSameLine scripts
if [ $DO_GIT_COMMIT -eq 1 ]; then
    git add scripts &&
    git commit -m "[AUTO: PHP-CS-Fixer] scripts directory"
fi


############# PHP_CodeSniffer #############
# First we check the errors
vendor/bin/phpcs -p -s --standard="$CS_RULESSET_DIR/ruleset.xml" 2>&1 | tee "$TMPDIR/phpcs.log" &&
# Then we fix them
vendor/bin/phpcbf -p --standard="$CS_RULESSET_DIR/ruleset.xml" 2>&1 | tee "$TMPDIR/phpcbf.log"
if [ $DO_GIT_COMMIT -eq 1 ]; then
    git add application &&
    git commit -m "[AUTO: CodeSniffer] Fixes to fit to to CI3 coding style"
fi

if [[ $DO_GIT_DIFF -eq 1 ]]; then
    git diff --exit-code > "$DIFF_OUTPUT_DIR/code_style_check.diff"
    DIFF_EXIT_CODE=$?
    git checkout .
    if [[ $DIFF_EXIT_CODE -ne 0 ]]; then
        if [[ -v GITHUB_ACTIONS ]]; then
            head -n200 "$DIFF_OUTPUT_DIR/code_style_check.diff"
        fi
        echo
        echo "_____________________"
        echo " CODE NEEDS FIXING ! "
        echo "‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾"
        echo "The code doesn't respect the coding guidelines."
        echo "Please double-check and apply the required changes."
        echo
        echo "Full diff file is '$DIFF_OUTPUT_DIR/code_style_check.diff'"
        if [[ -v GITHUB_ACTIONS ]]; then
            echo "The first 200 lines of the diff are shown above."
            echo "You can find the full diff in the artifacts."
        fi
        EXIT_STATUS=1
    else
        echo
        echo "___________________________"
        echo " Code Respects Guidelines. "
        echo "‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾"
        rm "$DIFF_OUTPUT_DIR/code_style_check.diff"
    fi
fi

# Check the views for any errors
# Disabled for now
#vendor/bin/phpcs -p -s --standard="$CS_RULESSET_DIR/ruleset-views.xml" 2>&1 | tee "$TMPDIR/phpcs-views.log"
#grep "| ERROR" phpcs-views.log | cut -d '|' -f 3- | sort | uniq -c
#grep "| WARNING" phpcs-views.log | cut -d '|' -f 3- | sort | uniq -c
#

if [ -v EXIT_STATUS ]; then
    exit $EXIT_STATUS
fi
