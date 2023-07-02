#!/bin/bash

#
# Upload Kalkun dependencies to the PPA
#
# Usage: upload_kalkun_deps_to_ppa.sh -r <package_repo_url> -d "<suite1 suite2 ...>" -h <dput hostname>
#
#

set -e

print_usage() {
  echo '
 Upload Kalkun dependencies to the PPA

 Usage: upload_kalkun_deps_to_ppa.sh -r <package_repo_url> -d "<suite1 suite2 ...>" -h <dput hostname>

  For example:
    ./utils/upload_kalkun_deps_to_ppa.sh -r https://salsa.debian.org/php-team/pear/php-datto-json-rpc -d jammy -h kalkun-snapshots

    ./utils/upload_kalkun_deps_to_ppa.sh -r https://salsa.debian.org/php-team/pear/php-datto-json-rpc -d "$(ubuntu-distro-info --supported)" -h kalkun-snapshots

    ./utils/upload_kalkun_deps_to_ppa.sh -r kalkun -d "$(ubuntu-distro-info --supported)" -h kalkun-snapshots
    ./utils/upload_kalkun_deps_to_ppa.sh -r kalkun -p /path/to/local/kalkun/repo -d "$(ubuntu-distro-info --supported)" -h kalkun-snapshots
'
  echo "Usage: TODO"
}

BLACK='\033[0;30m'
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
MAGENTA='\033[0;35m'
CYAN='\033[0;36m'
WHITE='\033[0;37m'
BOLD='\033[1m'
UNDERLINE='\033[4m'
RESET='\033[0m'

export DEBEMAIL="packager@kalkun.invalid"
export DEBFULLNAME="Kalkun github workflow packager"

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )

while [[ $# -gt 0 ]]
do
key="$1"

case $key in
  -d)
    distribs=($2)
    shift # past argument
    ;;
  -r)
    repo="$2"
    shift
    ;;
  -h)
    DPUT_UPLOAD_SERVER="$2"
    shift
    ;;
  -p)
    KALKUN_REPO_PATH="$(realpath "$2")"
    shift
    ;;
  *)
    # unknown option
    print_usage
    echo "unknown option $key"
    exit 1
    ;;
esac
shift # past argument or value
done

if [ ${#distribs[@]} -eq 0 ]; then
  print_usage
  echo "destination suite(s) not set. Exiting."
  exit 1
fi

if [ -z "$repo" ]; then
  print_usage
  echo "repository URL or fullpath not set. Exiting."
  exit 1
fi

if [ -z "$DPUT_UPLOAD_SERVER" ]; then
  print_usage
  echo "dput's hostname not set. Exiting."
  exit 1
fi

if [ -z "$KEY_ID" ]; then
  echo "KEY_ID environment variable not set or empty."
  exit 1
fi

vendor="$(dpkg-vendor --query vendor)"
vendor="${vendor,,}"
package="$(basename "$repo")"

workdir="$(mktemp -d "/tmp/workdir_${package}.XXX")" || exit 1
trap "rm -fr -- '$workdir'" EXIT

echo
echo -e "${MAGENTA}${UNDERLINE}${BOLD}Starting script $(basename -- "${BASH_SOURCE[0]}")${RESET}"
echo -e "  package: ${GREEN}$package${RESET}"
echo -e "  source repository: ${GREEN}$repo${RESET}"
echo -e "  target distributions: ${GREEN}${distribs[*]}${RESET}"
echo

set -x

if [ "$repo" = "kalkun" ]; then

  if [ "a$CI" != "atrue" ]; then
    # Case when this is run locally
    if [ -z "$KALKUN_REPO_PATH" ]; then
      echo -e "${RED}path to local kalkun repo not set. Set it with -k <path>${RESET}"
      exit 1
    fi
    git clone --depth 1 "file://$KALKUN_REPO_PATH" "$workdir/repo"
    cd "$workdir/repo"

    TAG_AT_HEAD="$(git tag --points-at HEAD)"
    if [ "$TAG_AT_HEAD" = "" ]; then
      REF_NAME="$(git branch --show-current)"
    else
      REF_NAME="$TAG_AT_HEAD"
    fi
  else
    REF_NAME="$GITHUB_REF_NAME" # The short ref name of the branch or tag that triggered the workflow run. This value matches the branch or tag name shown on GitHub. For example, feature-branch-1.
    if [ "$GITHUB_REF_TYPE" = "tag" ]; then
      TAG_AT_HEAD="$REF_NAME"
    fi
  fi

  git config user.name "$DEBFULLNAME"
  git config user.email "$DEBEMAIL"

  git checkout -b bpp_general

  LAST_U_COMMIT_HASH="$(git rev-parse --short HEAD)"
  LAST_U_COMMIT_DATE="$(git log -1 --date=format:%Y%m%d%H%M --format=%cd)"
  LAST_U_COMMIT_DATE_H="$(git log -1 --date=format:%Y-%m-%d\ %H:%M --format=%cd)"
  UVERSION="$(grep kalkun_version application/config/kalkun_settings.php | cut -d "'" -f 4)"

  # Download only the debian folder from debian salsa servers to be able to build kalkun
  # This adds only the debian folder to the branch to be able to build the packages
  git remote add debian https://salsa.debian.org/bastif/kalkun.git
  git fetch debian

  git checkout -b debian_branch debian/debian/latest
  LAST_D_COMMIT_HASH="$(git rev-parse --short HEAD)"
  LAST_D_COMMIT_DATE="$(git log -1 --date=format:%Y%m%d%H%M --format=%cd)"
  LAST_D_COMMIT_DATE_H="$(git log -1 --date=format:%Y-%m-%d\ %H:%M --format=%cd)"

  git checkout bpp_general
  git checkout debian_branch -- debian
  git commit -m "add debian/ folder"

  # Create a branch containing debian folder before we change anything else.
  git branch -f bpp_general_orig
  ORIGINAL_BRANCH=bpp_general_orig

  if [ "$TAG_AT_HEAD" != "" ]; then
    TAG_VERSION="$(git tag --points-at "$TAG_AT_HEAD" | sed "s/^v//")"
    UVERSIONMANGLED="${TAG_VERSION//-/\~}"
    dch -v "${UVERSIONMANGLED}-1~${LAST_D_COMMIT_DATE}.${LAST_D_COMMIT_HASH}" --force-bad-version ""
    dch "Snapshot based on:"
    dch "  upstream tag v$TAG_VERSION."
    dch "  debianization from package source repository at commit $LAST_D_COMMIT_HASH, dated $LAST_D_COMMIT_DATE_H."
    gbp export-orig --no-pristine-tar --upstream-tree=TAG --upstream-tag="$REF_NAME" --compression=xz
  else \
    UVERSIONMANGLED=$(echo "${UVERSION}" | sed -e "s/-/~/g" -e "s/~dev/~~dev/")
    gbp dch \
      --new-version="${UVERSIONMANGLED}" \
      --snapshot \
      --snapshot-number "${LAST_U_COMMIT_DATE}" \
      --ignore-branch \
      --no-git-author \
      --since=HEAD \
      debian
    # Append debian version to version number (this is because we use --snapshot)
    dch --local "-1~${LAST_D_COMMIT_DATE}.${LAST_D_COMMIT_HASH}" ""
    dch "Snapshot based on:"
    dch "  upstream repository at commit $LAST_U_COMMIT_HASH, dated $LAST_U_COMMIT_DATE_H."
    dch "  debianization from package source repository at commit $LAST_D_COMMIT_HASH, dated $LAST_D_COMMIT_DATE_H."
    gbp export-orig --no-pristine-tar --upstream-tree=BRANCH --upstream-branch="$REF_NAME" --compression=xz ; \
  fi

  git add debian/changelog
  git commit -m "update changelog"
  git checkout -f

else

  gbp clone "$repo" "$workdir/repo"

  cd "$workdir/repo"

  git config user.name "$DEBFULLNAME"
  git config user.email "$DEBEMAIL"

  ORIGINAL_BRANCH=$(git branch --show-current)
  DCH_DISTRIB=$(git show "$ORIGINAL_BRANCH":debian/changelog | dpkg-parsechangelog -l - -S Distribution)
  git checkout -b bpp_general
  if [ "${DCH_DISTRIB,,}" = "unreleased" ]; then
    LAST_COMMIT_HASH="$(git rev-parse --short HEAD)"
    LAST_COMMIT_DATE="$(git log -1 --date=format:%Y%m%d%H%M --format=%cd)"
    LAST_COMMIT_DATE_H="$(git log -1 --date=format:%Y-%m-%d\ %H:%M --format=%cd)"
    dch --local "~~${LAST_COMMIT_DATE}.${LAST_COMMIT_HASH}." ""
    dch "Snapshot based on package source repository at commit $LAST_COMMIT_HASH, dated $LAST_COMMIT_DATE_H."
    git add debian/changelog
    git commit -m "update changelog"
  fi

  git checkout -f
  gbp export-orig

fi

if [ "a$CI" == "atrue" ]; then
  # Build binary package
  export DEB_BUILD_PROFILES="nocheck"
  export DEB_BUILD_OPTIONS="nocheck"

  # Install build dependencies
  mk-build-deps --install --remove --root-cmd sudo --tool='apt-get -o Debug::pkgProblemResolver=yes --no-install-recommends --yes' debian/control
  rm -f "$(dpkg-parsechangelog -S Source)"-build-deps_*

  # Build binary packages
  dpkg-buildpackage -d --sign-key="$KEY_ID"

  # Install binary packages in case they are required for the next steps.
  dcmd --deb sudo apt-get install -y ../"$(dpkg-parsechangelog -S Source)"_"$(dpkg-parsechangelog -S Version)"_amd64.changes

  # Copy build products
  mkdir -p ~/build_products
  dcmd cp ../"$(dpkg-parsechangelog -S Source)"_"$(dpkg-parsechangelog -S Version)"_amd64.changes ~/build_products
else
  # Build only source package
  dpkg-buildpackage -d -S --sign-key="$KEY_ID"
fi

set +x

for suite in "${distribs[@]}"; do

  set +x
  echo
  echo -e "${MAGENTA}${UNDERLINE}Processing ${BOLD}$package${RESET}${MAGENTA}${UNDERLINE} for ${BOLD}$suite${RESET}..."
  echo
  set -x

  if [ "$suite" = "focal" ]; then
    if git rev-parse --quiet --verify "origin/$vendor/$suite" > /dev/null; then
      git checkout -b "bpp_$suite" "origin/$vendor/$suite"
      DCH_DISTRIB="$(git show "bpp_$suite":debian/changelog | dpkg-parsechangelog -l - -S Distribution)"
      gbp export-orig
    else
      git checkout -b "bpp_$suite" bpp_general
      DCH_DISTRIB="$(git show "$ORIGINAL_BRANCH":debian/changelog | dpkg-parsechangelog -l - -S Distribution)"
    fi

    sed -i 's/dh-sequence-phpcomposer/pkg-php-tools/' debian/control
    sed -i 's/\(pkg-php-tools\).*/\1,/' debian/control
    if grep -q pkg-php-tools debian/control; then
      sed -i 's/dh $@/dh $@ --with phpcomposer/' debian/rules
    fi

    if [ "$(dpkg-parsechangelog -S Source)" = "kalkun" ]; then
      sed -i 's/\(php-random-compat\).*/\1,/' debian/control
    fi

    git add debian/control debian/rules
    if ! git diff --cached --quiet --exit-code; then
      git commit -m "Fix Build-Depends in d/control & d/rules to work on $suite"
    fi

    if [ "${DCH_DISTRIB,,}" != "unreleased" ] && [ "$(dpkg-parsechangelog -S Source)" != "kalkun" ] ; then
      dch --local "~" ""
    else
      dch --local "~~${suite}" ""
    fi

    dch "Fix Build-Depends in d/control & d/rules to work on $suite"
    git add debian/changelog
    git commit -m "update changelog"

    git checkout -f
    dpkg-buildpackage -d -S --sign-key="$KEY_ID"
  else
    # Be sure we are on the bpp_general branch (needed for subsequent calls to dpkg-parsechangelog)
    git checkout -f bpp_general
  fi

  workdir_bpp="$workdir/bpp_$suite"

  backportpackage \
    --workdir="$workdir_bpp" \
    --yes \
    --release-pocket \
    --suffix "~ppa1" \
    --destination "$suite" \
    --key "$KEY_ID" \
    ../"$(dpkg-parsechangelog -S Source)"_"$(dpkg-parsechangelog -S Version)".dsc

  if [ -z "$DPUT_CF" ]; then
    echo -e "${RED}DPUT_CF environment variable is not set."
    echo -e "Set it with the location of a file derived from $SCRIPT_DIR/launchpad/dput.cf.in${RESET}"
    exit 1
  elif [ ! -f "$DPUT_CF" ]; then
    echo -e "${RED}File '$DPUT_CF not found${RESET}' referenced by DPUT_CF environment variable not found."
    exit 1
  fi

  dput -c "$DPUT_CF" "${DPUT_UPLOAD_SERVER}" "$workdir_bpp"/*_source.changes

done
set +x

rm -fr "${workdir}"
trap - EXIT
