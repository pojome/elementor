#!/bin/bash
set -eo pipefail

if [[ -z "$SVN_USERNAME" ]]; then
	echo "Set the SVN_USERNAME secret"
	exit 1
fi

if [[ -z "$SVN_PASSWORD" ]]; then
	echo "Set the SVN_PASSWORD secret"
	exit 1
fi

if [[ -z "$PLUGIN_VERSION" ]]; then
	echo "Set the PLUGIN_VERSION env var"
	exit 1
fi

echo "Publish version: ${PLUGIN_VERSION}"

ELEMENTOR_PATH="$GITHUB_WORKSPACE/elementor"
SVN_PATH="$GITHUB_WORKSPACE/svn"

cd $ELEMENTOR_PATH
mkdir -p $SVN_PATH

echo "Change readme.txt file in trunk"
cd $SVN_PATH
svn co https://plugins.svn.wordpress.org/elementor/trunk

echo "Preparing files"
cd $SVN_PATH/trunk
cp $ELEMENTOR_PATH/readme.txt .
svn status

echo "Commit readme.txt file to trunk"
svn ci readme.txt -m "Upload v${PLUGIN_VERSION}" --no-auth-cache --non-interactive  --username "$SVN_USERNAME" --password "$SVN_PASSWORD"

echo "Change readme.txt file in ${PLUGIN_VERSION}"
cd $SVN_PATH
svn co https://plugins.svn.wordpress.org/elementor/tags/${PLUGIN_VERSION}

echo "Preparing files"
cd $SVN_PATH/${PLUGIN_VERSION}
cp $ELEMENTOR_PATH/readme.txt .
svn status

echo "Commit readme.txt file to ${PLUGIN_VERSION}"
svn ci readme.txt -m "Upload v${PLUGIN_VERSION}" --no-auth-cache --non-interactive  --username "$SVN_USERNAME" --password "$SVN_PASSWORD"

