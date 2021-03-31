#!/bin/bash

#
# init.sh
#
# Windows users with Git installed should also have git-bash, so this should
# work cross-platform.
#
# @author Thomas Galinis <tgalinis2020@fau.edu>
#

# Install dependencies listed in package.json.
npm install

# Create a folder in document root directory to hold third-party dependencies.
mkdir -p public_html/vendor


# If a dependency isn't in the vendor directory, copy it from node_modules.

if [ ! -d ./public_html/vendor/bootstrap ]
then
    cp -r ./node_modules/bootstrap ./public_html/vendor
fi

if [ ! -d ./public/vendor/@fortawesome/fontawesome-free ]
then
    cp -r ./node_modules/@fortawesome/fontawesome-free ./public_html/vendor
fi
