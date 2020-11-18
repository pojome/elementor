#!/bin/bash -e

# For running this script in local env, you need to install:
# zip archive (for mac users "brew install zip")
# mysql (for mac users https://flaviocopes.com/mysql-how-to-install/)

if [ $# -lt 3 ]; then
	echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version]"
	exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}

# Set default WP_VERSION (if WP_VERSION string is null, that is, has zero length)
if [ -z $WP_VERSION ] ; then
    WP_VERSION=latest
fi

# Set WP params for settings
#locale=(he_IL en_AU en_CA en_NZ en_ZA en_GB en_US)
WP_LOCALE="en_GB"
WP_USER="test"
WP_USER_PASS="test"
WP_USER_EMAIL="user@example.org"
WP_SITE_NAME="test"
WP_THEMES="hello-elementor"
WP_PLUGINS="elementor"

# Save the current working directory in an environment variable.
INITIAL_WORKING_DIRECTORY=$(pwd)
echo "$INITIAL_WORKING_DIRECTORY"

# Set paths to directories where you will want to install: wp-cli | wp | wp test suit
WP_TESTS_DIR=${WP_TESTS_DIR-/tmp/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-/tmp/wordpress/}

# Set paths to current plugins and them tests directories
CURRENT_PLUGIN="${WP_CORE_DIR}wp-content/plugins/elementor"
CURRENT_PLUGIN_TEST_DIR="${CURRENT_PLUGIN}/tests"

download() {
    if [ `which curl` ]; then
        curl -s "$1" > "$2";
    elif [ `which wget` ]; then
        wget -nv -O "$2" "$1"
    fi
}

install_wp_cli() {
 	if [ ! -d "$WP_CORE_DIR" ]; then
 		mkdir -p "$WP_CORE_DIR"
	fi

	cd "$WP_CORE_DIR"
	# Download and install wp-cli
	curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
	# Set the permissions to make it executable
	chmod +x wp-cli.phar
	sudo mv wp-cli.phar /usr/local/bin/wp
  wp --info
}

download_wp_core(){
	# Download the WordPress core files [--skip-content - Download WP without the default themes and plugins.]
	wp core download --locale="$WP_LOCALE" --version="$WP_VERSION" --skip-plugins
}

config(){
	# Create the wp-config file
	wp config create --dbname="$DB_NAME" --dbuser="$DB_USER" --dbpass="$DB_PASS"
}

install(){
	# Create database
	wp db create

	# parse DB_HOST for port or socket references
	local PARTS=(${DB_HOST//\:/ })
	local DB_HOSTNAME=${PARTS[0]};
	local DB_SOCK_OR_PORT=${PARTS[1]};
	local EXTRA=""

	if ! [ -z "$DB_HOSTNAME" ] ; then
		if [ $(echo "$DB_SOCK_OR_PORT" | grep -e '^[0-9]\{1,\}$') ]; then
			EXTRA="--host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp"
		elif ! [ -z $DB_SOCK_OR_PORT ] ; then
			EXTRA="--socket=$DB_SOCK_OR_PORT"
		elif ! [ -z $DB_HOSTNAME ] ; then
			EXTRA="--host=$DB_HOSTNAME --protocol=tcp"
		fi
	fi

	# Optimizes the database
	wp db optimize --dbuser="$DB_USER" --dbpass="$DB_PASS" "$EXTRA"

	# install WordPress
	wp core install --url="http://testelementor" --title="$WP_SITE_NAME" --admin_user="$WP_USER" --admin_password="$WP_USER_PASS" --admin_email="$WP_USER_EMAIL"

	download https://raw.github.com/markoheijnen/wp-mysqli/master/db.php $WP_CORE_DIR/wp-content/db.php
}

get_installed_wp_version(){
	if [[ $WP_VERSION =~ [0-9]+\.[0-9]+(\.[0-9]+)? ]]; then
		WP_TESTS_TAG="tags/$WP_VERSION"
	else
		# get the latest version of wordpress from core installed
		LATEST_VERSION=$(wp core version)
		if [[ -z "$LATEST_VERSION" ]]; then
			echo "Latest WordPress version could not be found"
			exit 1
		fi
		WP_TESTS_TAG="tags/$LATEST_VERSION"
	fi
}

install_test_suite() {
	get_installed_wp_version
	# portable in-place argument for both GNU sed and Mac OSX sed
	if [[ $(uname -s) == 'Darwin' ]]; then
		local ioption='-i .bak'
	else
		local ioption='-i'
	fi

	# set up testing suite if it doesn't yet exist
	if [ ! -d "$WP_TESTS_DIR" ]; then
		# set up testing suite
		mkdir -p $WP_TESTS_DIR
		svn co --quiet https://develop.svn.wordpress.org/"${WP_TESTS_TAG}"/tests/phpunit/includes/ "${WP_TESTS_DIR}/includes"
	fi

	cd "${WP_TESTS_DIR}"

	if [ ! -f wp-tests-config.php ]; then
		download https://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php "${WP_TESTS_DIR}/wp-tests-config.php"
		sed $ioption "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR':" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s/youremptytestdbnamehere/$DB_NAME/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s/yourusernamehere/$DB_USER/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s/yourpasswordhere/$DB_PASS/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s|localhost|${DB_HOST}|" "$WP_TESTS_DIR"/wp-tests-config.php
	fi
}

create_plugins_folder(){
	if [ ! -d "${WP_CORE_DIR}wp-content/plugins" ]; then
		cd "${WP_CORE_DIR}wp-content/"
		mkdir plugins
	fi
}

install_plugins(){
	create_plugins_folder
	# Create symlink to current path
	ln -s "${INITIAL_WORKING_DIRECTORY}" "${CURRENT_PLUGIN}"
	# After running install_test_suite need to return to directory $WP_CORE_DIR where wp-cli installed
	cd "${WP_CORE_DIR}"
	# Install plugins
	wp plugin activate "$WP_PLUGINS"
}

install_themes(){
	# Install the theme
	wp theme install "$WP_THEMES" --activate
}

delete_db(){
	# Deletes the existing database.
	wp db drop --yes
}

delete_the_installed_directories(){
  # Delete the $WP_CORE_DIR directory and all its contents, including any subdirectories and files
  if [ -d "$WP_CORE_DIR" ]; then
    rm -rf "$WP_CORE_DIR"
    if [ ! -d "$WP_CORE_DIR" ]; then
      echo "Directory $WP_CORE_DIR is deleted."
    else
		  echo "Directory $WP_CORE_DIR does not delete."
		fi
	fi

	# Delete the $WP_TESTS_DIR directory and all its contents, including any subdirectories and files
	if [ -d "$WP_TESTS_DIR" ]; then
    rm -rf "$WP_TESTS_DIR"
    if [ ! -d "$WP_TESTS_DIR" ]; then
      echo "Directory $WP_TESTS_DIR is deleted."
    else
		  echo "Directory $WP_TESTS_DIR does not delete."
		fi
	fi
}

delete_symlink(){
	# Check if file exists and is a symbolic link (same as -h command)
	if [ -L "${CURRENT_PLUGIN}" ]; then
		unlink "${CURRENT_PLUGIN}"
		echo "deleted symlink ${CURRENT_PLUGIN}"
	fi
}

wp_cli_cache_clear(){
	# Clears the internal cache
	wp cli cache clear
}

install_wp_cli
download_wp_core
config
install
install_test_suite
install_plugins
install_themes

# The below functions are usable only for clean local env when running tests
#delete_db
#delete_symlink
#delete_the_installed_directories
#wp_cli_cache_clear
