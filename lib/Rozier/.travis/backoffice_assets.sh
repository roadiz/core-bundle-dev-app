#!/bin/sh -x
cd src || exit 1;
yarn install --pure-lockfile
yarn run install
yarn run build
