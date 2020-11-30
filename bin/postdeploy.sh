#!/usr/bin/env bash
##
# Build.
#
# shellcheck disable=SC2015,SC2094

set -e

echo "Post deploy script"
cd /var/www/newtest-v2/docroot &&
/usr/local/bin/drush cr
echo "Cache cleared."
