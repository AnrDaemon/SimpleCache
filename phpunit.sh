#!/bin/sh

_dir="$( dirname "$( readlink -e "$0" )" )"

"$_dir/vendor/bin/phpunit" --testdox-html "$_dir/testdox.html" -- "$_dir/tests"
