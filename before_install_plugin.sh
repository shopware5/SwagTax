#!/usr/bin/env bash

PLUGIN_DIR=$1

ENV=$2

php $PLUGIN_DIR/../../../bin/console sw:plugin:install --activate "SwagCustomProducts" --env="$ENV"
php $PLUGIN_DIR/../../../bin/console sw:plugin:install --activate "SwagBundle" --env="$ENV"
