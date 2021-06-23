<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor utils.
 *
 * Elementor utils handler class is responsible for different utility methods
 * used by Elementor.
 *
 * @since 1.0.0
 */
class Utils {

	const DEPRECATION_RANGE = 0.4;

	/**
	 * A list of safe tage for `validate_html_tag` method.
	 */
	const ALLOWED_HTML_WRAPPER_TAGS = [
		'a',
		'article',
		'aside',
		'div',
		'footer',
		'h1',
		'h2',
		'h3',
		'h4',
		'h5',
		'h6',
		'header',
		'main',
		'nav',
		'p',
		'section',
		'span',
	];

	/**
	 * Is ajax.
	 *
	 * Whether the current request is a WordPress ajax request.
	 *
	 * @since 1.0.0
	 * @deprecated 2.6.0 Use `wp_doing_ajax()` instead.
	 * @access public
	 * @static
	 *
	 * @return bool True if it's a WordPress ajax request, false otherwise.
	 */
	public static function is_ajax() {
		 _deprecated_function( __METHOD__, '2.6.0', 'wp_doing_ajax()' );

		return wp_doing_ajax();
	}

	/**
	 * Is WP CLI.
	 *
	 * @return bool
	 */
	public static function is_wp_cli() {
		return defined( 'WP_CLI' ) && WP_CLI;
	}

	/**
	 * Is script debug.
	 *
	 * Whether script debug is enabled or not.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return bool True if it's a script debug is active, false otherwise.
	 */
	public static function is_script_debug() {
		return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
	}

	/**
	 * Get pro link.
	 *
	 * Retrieve the link to Elementor Pro.
	 *
	 * @since 1.7.0
	 * @access public
	 * @static
	 *
	 * @param string $link URL to Elementor pro.
	 *
	 * @return string Elementor pro link.
	 */
	public static function get_pro_link( $link ) {
		static $theme_name = false;

		if ( ! $theme_name ) {
			$theme_obj = wp_get_theme();
			if ( $theme_obj->parent() ) {
				$theme_name = $theme_obj->parent()->get( 'Name' );
			} else {
				$theme_name = $theme_obj->get( 'Name' );
			}

			$theme_name = sanitize_key( $theme_name );
		}

		$link = add_query_arg( 'utm_term', $theme_name, $link );

		return $link;
	}

	/**
	 * Replace URLs.
	 *
	 * Replace old URLs to new URLs. This method also updates all the Elementor data.
	 *
	 * @since 2.1.0
	 * @static
	 * @access public
	 *
	 * @param $from
	 * @param $to
	 *
	 * @return string
	 * @throws \Exception
	 */
	public static function replace_urls( $from, $to ) {
		$from = trim( $from );
		$to = trim( $to );

		if ( $from === $to ) {
			throw new \Exception( __( 'The `from` and `to` URL\'s must be different', 'elementor' ) );
		}

		$is_valid_urls = ( filter_var( $from, FILTER_VALIDATE_URL ) && filter_var( $to, FILTER_VALIDATE_URL ) );
		if ( ! $is_valid_urls ) {
			throw new \Exception( __( 'The `from` and `to` URL\'s must be valid URL\'s', 'elementor' ) );
		}

		global $wpdb;

		// @codingStandardsIgnoreStart cannot use `$wpdb->prepare` because it remove's the backslashes
		$rows_affected = $wpdb->query(
			"UPDATE {$wpdb->postmeta} " .
			"SET `meta_value` = REPLACE(`meta_value`, '" . str_replace( '/', '\\\/', $from ) . "', '" . str_replace( '/', '\\\/', $to ) . "') " .
			"WHERE `meta_key` = '_elementor_data' AND `meta_value` LIKE '[%' ;" ); // meta_value LIKE '[%' are json formatted
		// @codingStandardsIgnoreEnd

		if ( false === $rows_affected ) {
			throw new \Exception( __( 'An error occurred', 'elementor' ) );
		}

		// Allow externals to replace-urls, when they have to.
		$rows_affected += (int) apply_filters( 'elementor/tools/replace-urls', 0, $from, $to );

		Plugin::$instance->files_manager->clear_cache();

		return sprintf(
			/* translators: %d: Number of rows */
			_n( '%d row affected.', '%d rows affected.', $rows_affected, 'elementor' ),
			$rows_affected
		);
	}

	/**
	 * Is post supports Elementor.
	 *
	 * Whether the post supports editing with Elementor.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @param int $post_id Optional. Post ID. Default is `0`.
	 *
	 * @return string True if post supports editing with Elementor, false otherwise.
	 */
	public static function is_post_support( $post_id = 0 ) {
		$post_type = get_post_type( $post_id );

		$is_supported = self::is_post_type_support( $post_type );

		/**
		 * Is post type support.
		 *
		 * Filters whether the post type supports editing with Elementor.
		 *
		 * @since 1.0.0
		 * @deprecated 2.2.0 Use `elementor/utils/is_post_support` Instead
		 *
		 * @param bool $is_supported Whether the post type supports editing with Elementor.
		 * @param int $post_id Post ID.
		 * @param string $post_type Post type.
		 */
		$is_supported = apply_filters( 'elementor/utils/is_post_type_support', $is_supported, $post_id, $post_type );

		/**
		 * Is post support.
		 *
		 * Filters whether the post supports editing with Elementor.
		 *
		 * @since 2.2.0
		 *
		 * @param bool $is_supported Whether the post type supports editing with Elementor.
		 * @param int $post_id Post ID.
		 * @param string $post_type Post type.
		 */
		$is_supported = apply_filters( 'elementor/utils/is_post_support', $is_supported, $post_id, $post_type );

		return $is_supported;
	}


	/**
	 * Is post type supports Elementor.
	 *
	 * Whether the post type supports editing with Elementor.
	 *
	 * @since 2.2.0
	 * @access public
	 * @static
	 *
	 * @param string $post_type Post Type.
	 *
	 * @return string True if post type supports editing with Elementor, false otherwise.
	 */
	public static function is_post_type_support( $post_type ) {
		if ( ! post_type_exists( $post_type ) ) {
			return false;
		}

		if ( ! post_type_supports( $post_type, 'elementor' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get placeholder image source.
	 *
	 * Retrieve the source of the placeholder image.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return string The source of the default placeholder image used by Elementor.
	 */
	public static function get_placeholder_image_src() {
		$placeholder_image = ELEMENTOR_ASSETS_URL . 'images/placeholder.png';

		/**
		 * Get placeholder image source.
		 *
		 * Filters the source of the default placeholder image used by Elementor.
		 *
		 * @since 1.0.0
		 *
		 * @param string $placeholder_image The source of the default placeholder image.
		 */
		$placeholder_image = apply_filters( 'elementor/utils/get_placeholder_image_src', $placeholder_image );

		return $placeholder_image;
	}

	/**
	 * Generate random string.
	 *
	 * Returns a string containing a hexadecimal representation of random number.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return string Random string.
	 */
	public static function generate_random_string() {
		return dechex( rand() );
	}

	/**
	 * Do not cache.
	 *
	 * Tell WordPress cache plugins not to cache this request.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function do_not_cache() {
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}

		if ( ! defined( 'DONOTCACHEDB' ) ) {
			define( 'DONOTCACHEDB', true );
		}

		if ( ! defined( 'DONOTMINIFY' ) ) {
			define( 'DONOTMINIFY', true );
		}

		if ( ! defined( 'DONOTCDN' ) ) {
			define( 'DONOTCDN', true );
		}

		if ( ! defined( 'DONOTCACHCEOBJECT' ) ) {
			define( 'DONOTCACHCEOBJECT', true );
		}

		// Set the headers to prevent caching for the different browsers.
		nocache_headers();
	}

	/**
	 * Get timezone string.
	 *
	 * Retrieve timezone string from the WordPress database.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return string Timezone string.
	 */
	public static function get_timezone_string() {
		$current_offset = (float) get_option( 'gmt_offset' );
		$timezone_string = get_option( 'timezone_string' );

		// Create a UTC+- zone if no timezone string exists.
		if ( empty( $timezone_string ) ) {
			if ( $current_offset < 0 ) {
				$timezone_string = 'UTC' . $current_offset;
			} else {
				$timezone_string = 'UTC+' . $current_offset;
			}
		}

		return $timezone_string;
	}

	/**
	 * Get create new post URL.
	 *
	 * Retrieve a custom URL for creating a new post/page using Elementor.
	 *
	 * @since 1.9.0
	 * @access public
	 * @static
	 *
	 * @param string $post_type Optional. Post type slug. Default is 'page'.
	 * @param string|null $template_type Optional. Query arg 'template_type'. Default is null.
	 *
	 * @return string A URL for creating new post using Elementor.
	 */
	public static function get_create_new_post_url( $post_type = 'page', $template_type = null ) {
		Plugin::$instance->modules_manager->get_modules( 'dev-tools' )->deprecation->deprecated_function( __FUNCTION__, '3.3.0', 'Plugin::$instance->documents->get_create_new_post_url()' );

		return Plugin::$instance->documents->get_create_new_post_url( $post_type, $template_type );
	}

	/**
	 * Get post autosave.
	 *
	 * Retrieve an autosave for any given post.
	 *
	 * @since 1.9.2
	 * @access public
	 * @static
	 *
	 * @param int $post_id Post ID.
	 * @param int $user_id Optional. User ID. Default is `0`.
	 *
	 * @return \WP_Post|false Post autosave or false.
	 */
	public static function get_post_autosave( $post_id, $user_id = 0 ) {
		global $wpdb;

		$post = get_post( $post_id );

		$where = $wpdb->prepare( 'post_parent = %d AND post_name LIKE %s AND post_modified_gmt > %s', [ $post_id, "{$post_id}-autosave%", $post->post_modified_gmt ] );

		if ( $user_id ) {
			$where .= $wpdb->prepare( ' AND post_author = %d', $user_id );
		}

		$revision = $wpdb->get_row( "SELECT * FROM $wpdb->posts WHERE $where AND post_type = 'revision'" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( $revision ) {
			$revision = new \WP_Post( $revision );
		} else {
			$revision = false;
		}

		return $revision;
	}

	/**
	 * Is CPT supports custom templates.
	 *
	 * Whether the Custom Post Type supports templates.
	 *
	 * @since 2.0.0
	 * @access public
	 * @static
	 *
	 * @return bool True is templates are supported, False otherwise.
	 */
	public static function is_cpt_custom_templates_supported() {
		require_once ABSPATH . '/wp-admin/includes/theme.php';

		return method_exists( wp_get_theme(), 'get_post_templates' );
	}

	/**
	 * @since 2.1.2
	 * @access public
	 * @static
	 */
	public static function array_inject( $array, $key, $insert ) {
		$length = array_search( $key, array_keys( $array ), true ) + 1;

		return array_slice( $array, 0, $length, true ) +
			$insert +
			array_slice( $array, $length, null, true );
	}

	/**
	 * Render html attributes
	 *
	 * @access public
	 * @static
	 * @param array $attributes
	 *
	 * @return string
	 */
	public static function render_html_attributes( array $attributes ) {
		$rendered_attributes = [];

		foreach ( $attributes as $attribute_key => $attribute_values ) {
			if ( is_array( $attribute_values ) ) {
				$attribute_values = implode( ' ', $attribute_values );
			}

			$rendered_attributes[] = sprintf( '%1$s="%2$s"', $attribute_key, esc_attr( $attribute_values ) );
		}

		return implode( ' ', $rendered_attributes );
	}

	/**
	 * Safe print html attributes
	 *
	 * @access public
	 * @static
	 * @param array $attributes
	 */
	public static function print_html_attributes( array $attributes ) {
		// PHPCS - the method render_html_attributes is safe.
		echo self::render_html_attributes( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public static function get_meta_viewport( $context = '' ) {
		$meta_tag = '<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />';
		/**
		 * Viewport meta tag.
		 *
		 * Filters the Elementor preview URL.
		 *
		 * @since 2.5.0
		 *
		 * @param string $meta_tag Viewport meta tag.
		 */
		return apply_filters( 'elementor/template/viewport_tag', $meta_tag, $context );
	}

	/**
	 * Add Elementor Config js vars to the relevant script handle,
	 * WP will wrap it with <script> tag.
	 * To make sure this script runs thru the `script_loader_tag` hook, use a known handle value.
	 * @param string $handle
	 * @param string $js_var
	 * @param mixed $config
	 */
	public static function print_js_config( $handle, $js_var, $config ) {
		$config = wp_json_encode( $config );

		if ( get_option( 'elementor_editor_break_lines' ) ) {
			// Add new lines to avoid memory limits in some hosting servers that handles the buffer output according to new line characters
			$config = str_replace( '}},"', '}},' . PHP_EOL . '"', $config );
		}

		$script_data = 'var ' . $js_var . ' = ' . $config . ';';

		wp_add_inline_script( $handle, $script_data, 'before' );
	}

	public static function handle_deprecation( $item, $version, $replacement = null ) {
		preg_match( '/^[0-9]+\.[0-9]+/', ELEMENTOR_VERSION, $current_version );

		$current_version_as_float = (float) $current_version[0];

		preg_match( '/^[0-9]+\.[0-9]+/', $version, $alias_version );

		$alias_version_as_float = (float) $alias_version[0];

		if ( round( $current_version_as_float - $alias_version_as_float, 1 ) >= self::DEPRECATION_RANGE ) {
			_deprecated_file( $item, $version, $replacement );
		}
	}

	/**
	 * Checks a control value for being empty, including a string of '0' not covered by PHP's empty().
	 *
	 * @param mixed $source
	 * @param bool|string $key
	 *
	 * @return bool
	 */
	public static function is_empty( $source, $key = false ) {
		if ( is_array( $source ) ) {
			if ( ! isset( $source[ $key ] ) ) {
				return true;
			}

			$source = $source[ $key ];
		}

		return '0' !== $source && empty( $source );
	}

	public static function has_pro() {
		return defined( 'ELEMENTOR_PRO_VERSION' );
	}

	/**
	 * Convert HTMLEntities to UTF-8 characters
	 *
	 * @param $string
	 * @return string
	 */
	public static function urlencode_html_entities( $string ) {
		$entities_dictionary = [
			'&#145;' => "'", // Opening single quote
			'&#146;' => "'", // Closing single quote
			'&#147;' => '"', // Closing double quote
			'&#148;' => '"', // Opening double quote
			'&#8216;' => "'", // Closing single quote
			'&#8217;' => "'", // Opening single quote
			'&#8218;' => "'", // Single low quote
			'&#8220;' => '"', // Closing double quote
			'&#8221;' => '"', // Opening double quote
			'&#8222;' => '"', // Double low quote
		];

		// Decode decimal entities
		$string = str_replace( array_keys( $entities_dictionary ), array_values( $entities_dictionary ), $string );

		return rawurlencode( html_entity_decode( $string, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
	}

	/**
	 * Parse attributes that come as a string of comma-delimited key|value pairs.
	 * Removes Javascript events and unescaped `href` attributes.
	 *
	 * @param string $attributes_string
	 *
	 * @param string $delimiter Default comma `,`.
	 *
	 * @return array
	 */
	public static function parse_custom_attributes( $attributes_string, $delimiter = ',' ) {
		$attributes = explode( $delimiter, $attributes_string );
		$result = [];

		foreach ( $attributes as $attribute ) {
			$attr_key_value = explode( '|', $attribute );

			$attr_key = mb_strtolower( $attr_key_value[0] );

			// Remove any not allowed characters.
			preg_match( '/[-_a-z0-9]+/', $attr_key, $attr_key_matches );

			if ( empty( $attr_key_matches[0] ) ) {
				continue;
			}

			$attr_key = $attr_key_matches[0];

			// Avoid Javascript events and unescaped href.
			if ( 'href' === $attr_key || 'on' === substr( $attr_key, 0, 2 ) ) {
				continue;
			}

			if ( isset( $attr_key_value[1] ) ) {
				$attr_value = trim( $attr_key_value[1] );
			} else {
				$attr_value = '';
			}

			$result[ $attr_key ] = $attr_value;
		}

		return $result;
	}

	public static function find_element_recursive( $elements, $id ) {
		foreach ( $elements as $element ) {
			if ( $id === $element['id'] ) {
				return $element;
			}

			if ( ! empty( $element['elements'] ) ) {
				$element = self::find_element_recursive( $element['elements'], $id );

				if ( $element ) {
					return $element;
				}
			}
		}

		return false;
	}

	/**
	 * Change Submenu First Item Label
	 *
	 * Overwrite the label of the first submenu item of an admin menu item.
	 *
	 * Fired by `admin_menu` action.
	 *
	 * @since 3.1.0
	 *
	 * @param $menu_slug
	 * @param $new_label
	 * @access public
	 */
	public static function change_submenu_first_item_label( $menu_slug, $new_label ) {
		global $submenu;

		if ( isset( $submenu[ $menu_slug ] ) ) {
			// @codingStandardsIgnoreStart
			$submenu[ $menu_slug ][0][0] = $new_label;
			// @codingStandardsIgnoreEnd
		}
	}

	/**
	 * Validate an HTML tag against a safe allowed list.
	 *
	 * @param string $tag
	 *
	 * @return string
	 */
	public static function validate_html_tag( $tag ) {
		return in_array( strtolower( $tag ), self::ALLOWED_HTML_WRAPPER_TAGS ) ? $tag : 'div';
	}

	/**
	 * Safe print a validated HTML tag.
	 *
	 * @param string $tag
	 */
	public static function print_validated_html_tag( $tag ) {
		// PHPCS - the method validate_html_tag is safe.
		echo self::validate_html_tag( $tag ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Print internal content (not user input) without escaping.
	 */
	public static function print_unescaped_internal_string( $string ) {
		echo $string; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get recently edited posts query.
	 *
	 * Returns `WP_Query` of the recent edited posts.
	 * By default max posts ( $args['posts_per_page'] ) is 3.
	 *
	 * @param array $args
	 *
	 * @return \WP_Query
	 */
	public static function get_recently_edited_posts_query( $args = [] ) {
		$args = wp_parse_args( $args, [
			'post_type' => 'any',
			'post_status' => [ 'publish', 'draft' ],
			'posts_per_page' => '3',
			'meta_key' => '_elementor_edit_mode',
			'meta_value' => 'builder',
			'orderby' => 'modified',
		] );

		return new \WP_Query( $args );
	}
}
