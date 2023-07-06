#!/bin/bash

#
# Upload build dependencies to the "kalkun-build-deps" PPA
#
# Usage: upload_to_build_deps_ppa.sh -d <suite> <.dsc URL/file>
#
#  For example:
#    ./utils/upload_build_deps_to_ppa.sh -d focal http://archive.ubuntu.com/ubuntu/pool/main/d/debhelper/debhelper_13.6ubuntu1~bpo20.04.1.dsc
#

set -e

print_usage() {
  echo
  echo "Usage: ./utils/upload_build_deps_to_ppa.sh -d <suite> <source package name or .dsc URL/file>"
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
    dep_distrib="$2"
    shift # past argument
    ;;
  *.dsc)
    dep_dsc="$1"
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

if [ -z "$dep_distrib" ]; then
  print_usage
  echo "destination suite not specified. Exiting."
  exit 1
fi

if [ -z "$dep_dsc" ]; then
  print_usage
  echo "*.dsc file not specified. Exiting."
  exit 1
fi

if [ -z "$KEY_ID" ]; then
  exit 1
  echo "KEY_ID environment variable not set or empty."
fi

package="$(echo "$dep_dsc" | rev | cut -d / -f 1 | rev | cut -f 1 -d _)"
version="$(echo "$dep_dsc" | rev | cut -d / -f 1 | rev | cut -f 2 -d _)"

echo -e "${MAGENTA}${UNDERLINE}${BOLD}Starting script $(basename -- "${BASH_SOURCE[0]}")${RESET}"
echo -e "  distribution: ${GREEN}$dep_distrib${RESET}"
echo -e "  package: ${GREEN}$package${RESET}"
echo -e "  version: ${GREEN}$version${RESET}"
echo -e "  URL: ${GREEN}$dep_dsc${RESET}"

if [[ $(ubuntu-distro-info --supported) =~ $dep_distrib ]]; then
  workdir="$(mktemp -d "/tmp/backportpackage_workdir_$package.XXX")" || exit 1
  trap "rm -rf -- '$workdir'" EXIT

  set -x
  backportpackage \
    --workdir="$workdir" \
    --yes \
    --release-pocket \
    --suffix "~ppa1" \
    --destination "$dep_distrib" \
    --key "$KEY_ID" \
    "$dep_dsc"
  set +x

  if [ -z "$DPUT_CF" ]; then
    echo -e "${RED}DPUT_CF environment variable is not set."
    echo -e "Set it with the location of a file derived from $SCRIPT_DIR/launchpad/dput.cf.in${RESET}"
    exit 1
  elif [ ! -f "$DPUT_CF" ]; then
    echo -e "${RED}File '$DPUT_CF not found${RESET}' referenced by DPUT_CF environment variable not found."
    exit 1
  fi

  set -x
  dput -c utils/launchpad/dput.cf "kalkun-build-deps" "$workdir"/*_source.changes
  set +x

  rm -rf "${workdir}"
  trap - EXIT
  echo -e "Upload of ${BOLD}$package${RESET}: ${GREEN}successful${RESET}"

fi
