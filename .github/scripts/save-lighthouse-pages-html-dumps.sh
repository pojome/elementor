#!/bin/bash

TEMPLATES_FILE_NAMES=`ls ${GITHUB_WORKSPACE}/tests/lighthouse/templates/*.json`

mkdir -p .lighthouseci/dumps
for TEMPLATE_FILE_NAME in $TEMPLATES_FILE_NAMES
do
   TEMPLATE_NAME=$(basename "$TEMPLATE_FILE_NAME" .json)
   wget --directory-prefix=.lighthouseci/dumps --mirror --convert-links --adjust-extension --page-requisites --no-parent http://localhost:8889/${TEMPLATE_NAME}/
done
