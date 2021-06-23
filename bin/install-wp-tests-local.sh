#!/usr/bin/env bash

WORKING_DIR=$(pwd)

# Remove old tmp folder
rm -rf "$WORKING_DIR/tmp"

# Ask for some parameters to install the test env.
echo "Choose a database Name for tests [elementor-tests]:"
read -r DB_NAME
echo "What is your database username [admin]?"
read -r DB_USER
echo "What is your database password [admin]?"
read -r DB_PASS
echo "What is your database host (specify port if needed) [127.0.0.1:PORT]?"
read -r DB_HOST
echo "Choose WordPress version for testing [latest]:"
read -r WP_VERSION

DB_NAME=${DB_NAME:-"elementor-tests"}
DB_USER=${DB_USER:-"admin"}
DB_PASS=${DB_PASS:-"admin"}
DB_HOST=${DB_HOST:-"127.0.0.1"}
WP_VERSION=${WP_VERSION:-"latest"}

WP_TESTS_UTILS_DIR=${WP_TESTS_UTILS_DIR-$WORKING_DIR/tmp/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-$WORKING_DIR/tmp/wordpress/}

# Download util
download() {
    if [ `which curl` ]; then
        curl -s "$1" > "$2";
    elif [ `which wget` ]; then
        wget -nv -O "$2" "$1"
    fi
}

## Determine the WP_TEST_TAG.
if [[ $WP_VERSION =~ [0-9]+\.[0-9]+(\.[0-9]+)? ]]; then
	WP_TESTS_TAG="tags/$WP_VERSION"
else
	# http serves a single offer, whereas https serves multiple. we only want one
	download http://api.wordpress.org/core/version-check/1.7/ /tmp/wp-latest.json
	grep '[0-9]+\.[0-9]+(\.[0-9]+)?' /tmp/wp-latest.json
	LATEST_VERSION=$(grep -o '"version":"[^"]*' /tmp/wp-latest.json | sed 's/"version":"//')
	if [[ -z "$LATEST_VERSION" ]]; then
		echo "Latest WordPress version could not be found"
		exit 1
	fi
	WP_TESTS_TAG="tags/$LATEST_VERSION"
fi

set -ex

check_for_svn() {
  if [ ! `which svn` ]; then
    echo 'Please install "svn" and re run this script.'
    ehco 'Mac users: `brew install svn`'
    exit 1
  fi
}

install_wp() {
	if [ -d "$WP_CORE_DIR" ]; then
		return;
	fi

	mkdir -p "$WP_CORE_DIR"

	if [ $WP_VERSION == 'latest' ]; then
		local ARCHIVE_NAME='latest'
	else
		local ARCHIVE_NAME="wordpress-$WP_VERSION"
	fi

	download https://wordpress.org/${ARCHIVE_NAME}.tar.gz  /tmp/wordpress.tar.gz
	tar --strip-components=1 -zxmf /tmp/wordpress.tar.gz -C "$WP_CORE_DIR"

	download https://raw.github.com/markoheijnen/wp-mysqli/master/db.php "$WP_CORE_DIR/wp-content/db.php"
}

install_test_suite() {
	# portable in-place argument for both GNU sed and Mac OSX sed
	if [[ $(uname -s) == 'Darwin' ]]; then
		local ioption='-i .bak'
	else
		local ioption='-i'
	fi

	# set up testing suite if it doesn't yet exist
	if [ ! -d "$WP_TESTS_UTILS_DIR" ]; then
		# set up testing suite
		mkdir -p "$WP_TESTS_UTILS_DIR"/includes
		svn co --quiet https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/ "$WP_TESTS_UTILS_DIR/includes/"
	fi

	cd "$WP_TESTS_UTILS_DIR"

	if [ ! -f wp-tests-config.php ]; then
		download https://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php "$WP_TESTS_UTILS_DIR/wp-tests-config.php"
		sed $ioption "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR':" "$WP_TESTS_UTILS_DIR"/wp-tests-config.php
		sed $ioption "s/youremptytestdbnamehere/$DB_NAME/" "$WP_TESTS_UTILS_DIR"/wp-tests-config.php
		sed $ioption "s/yourusernamehere/$DB_USER/" "$WP_TESTS_UTILS_DIR"/wp-tests-config.php
		sed $ioption "s/yourpasswordhere/$DB_PASS/" "$WP_TESTS_UTILS_DIR"/wp-tests-config.php
		sed $ioption "s|localhost|${DB_HOST}|" "$WP_TESTS_UTILS_DIR"/wp-tests-config.php
	fi
}

install_db() {
	# parse DB_HOST for port or socket references
	local PARTS=(${DB_HOST//\:/ })
	local DB_HOSTNAME=${PARTS[0]};
	local DB_SOCK_OR_PORT=${PARTS[1]};
	local EXTRA=""

	if ! [ -z $DB_HOSTNAME ] ; then
		if [ $(echo $DB_SOCK_OR_PORT | grep -e '^[0-9]\{1,\}$') ]; then
			EXTRA=" --host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp"
		elif ! [ -z $DB_SOCK_OR_PORT ] ; then
			EXTRA=" --socket=$DB_SOCK_OR_PORT"
		elif ! [ -z $DB_HOSTNAME ] ; then
			EXTRA=" --host=$DB_HOSTNAME --protocol=tcp"
		fi
	fi

	# create database
	mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS"$EXTRA
}

check_for_svn
install_wp
install_test_suite
install_db
