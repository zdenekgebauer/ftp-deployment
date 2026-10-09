#!/bin/sh
# Builds deployment.phar with Box from the working tree; a clean copy is needed because Box fails on dev dependencies.
set -eu

root=$(cd "$(dirname "$0")" && pwd)
out=${1:-"$root/deployment.phar"}
tmp=$(mktemp -d)
trap 'rm -rf "$tmp"' EXIT

cd "$root"
for f in box.json composer.json composer.lock deployment src; do
	[ -e "$f" ] && cp -a "$f" "$tmp/"
done

cd "$tmp"
composer install --no-dev --no-progress --no-interaction --quiet
box compile --no-interaction
cp deployment.phar "$out"
php "$out" | grep -q "^FTP deployment v"
echo "Built $out"
